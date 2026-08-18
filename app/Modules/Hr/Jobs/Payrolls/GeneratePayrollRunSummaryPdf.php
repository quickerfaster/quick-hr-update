<?php

namespace App\Modules\Hr\Jobs\Payrolls;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use QuickerFaster\UILibrary\Models\Export;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Modules\Hr\Models\PayrollRun;

/**
 * Generate a PDF summary for a payroll run.
 *
 * For multi-company runs, reads per_company_summaries from the
 * payroll_runs JSON column to produce a consolidated report
 * without loading all payslips into memory.
 *
 * Designed for shared cPanel hosting:
 *  - Atomic status check with lockForUpdate prevents double-generation
 *  - Chunked payslip loading prevents memory exhaustion
 *  - Timeout set appropriately for large datasets
 */
class GeneratePayrollRunSummaryPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum execution time for PDF generation.
     * 1 hour should suffice even for multi-company runs with
     * thousands of employees (DomPDF is the bottleneck).
     */
    public $timeout = 3600;

    /**
     * No retries — if PDF generation fails, the user can re-trigger.
     */
    public $tries = 1;

    /**
     * Mark as failed on timeout.
     */
    public $failOnTimeout = true;

    /**
     * Maximum unhandled exceptions.
     */
    public $maxExceptions = 1;

    protected int $exportId;

    /**
     * @param int $exportId  The Export model ID
     */
    public function __construct(int $exportId)
    {
        $this->exportId = $exportId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // -----------------------------------------------------------------
        // 1. Atomic status check — prevent double-generation
        // -----------------------------------------------------------------
        $export = DB::transaction(function () {
            $export = Export::where('id', $this->exportId)
                ->lockForUpdate()
                ->first();

            if (!$export) {
                Log::warning("GeneratePayrollRunSummaryPdf: Export #{$this->exportId} not found.");
                return null;
            }

            if ($export->status !== 'pending') {
                Log::info("GeneratePayrollRunSummaryPdf: Export #{$this->exportId} already processed (status: {$export->status}). Skipping.");
                return null;
            }

            $export->update(['status' => 'processing']);

            return $export;
        });

        if (!$export) {
            return; // Guard prevented execution
        }

        try {
            // -----------------------------------------------------------------
            // 2. Load the payroll run
            // -----------------------------------------------------------------
            $runId = $export->options['run_id'] ?? null;
            if (!$runId) {
                throw new \RuntimeException('Export missing run_id in options.');
            }

            $run = PayrollRun::with(['paySchedule'])->find($runId);
            if (!$run) {
                throw new \RuntimeException("Payroll run #{$runId} not found.");
            }

            $currencySymbol = $export->options['currency_symbol'] ?? '$';
            $companyName = $export->options['company_name'] ?? config('app.name', 'Quick HR');

            // -----------------------------------------------------------------
            // 3. Determine data source: multi-company vs single-company
            // -----------------------------------------------------------------
            $isMultiCompany = !empty($run->is_multi_company);
            $perCompanySummaries = null;

            if ($isMultiCompany) {
                // Read from the JSON column — avoids loading all payslips
                $perCompanySummaries = $run->per_company_summaries;

                if (is_string($perCompanySummaries)) {
                    $perCompanySummaries = json_decode($perCompanySummaries, true);
                }

                Log::info("GeneratePayrollRunSummaryPdf: Multi-company run #{$runId}", [
                    'companies_count' => count($perCompanySummaries['companies'] ?? []),
                    'failed_count' => count($perCompanySummaries['failed_companies'] ?? []),
                ]);
            }

            // -----------------------------------------------------------------
            // 4. Load payslips — chunked for memory efficiency
            //    For multi-company runs, we still load payslips for the detail
            //    table, but the summary section uses the JSON column.
            // -----------------------------------------------------------------
            $payslips = $run->payslips()
                ->with('employee')
                ->cursor();

            // -----------------------------------------------------------------
            // 5. Generate PDF
            // -----------------------------------------------------------------
            $pdf = Pdf::loadView('hr::livewire.payroll.exports.payroll_run_summary_pdf', [
                'run' => $run,
                'payslips' => $payslips,
                'currencySymbol' => $currencySymbol,
                'companyName' => $companyName,
                'isMultiCompany' => $isMultiCompany,
                'perCompanySummaries' => $perCompanySummaries,
            ]);

            // -----------------------------------------------------------------
            // 6. Store PDF
            // -----------------------------------------------------------------
            $relativePath = 'exports/payroll-summary-' . $runId . '-' . uniqid() . '.pdf';
            Storage::disk('local')->put($relativePath, $pdf->output());

            $fileSize = Storage::disk('local')->size($relativePath);

            // -----------------------------------------------------------------
            // 7. Update export record
            // -----------------------------------------------------------------
            $export->update([
                'status' => 'completed',
                'file_path' => $relativePath,
                'file_size' => $fileSize,
                'download_token' => \Str::random(64),
                'expires_at' => now()->addHour(),
                'completed_at' => now(),
            ]);

            Log::info("GeneratePayrollRunSummaryPdf: Export #{$this->exportId} completed.", [
                'run_id' => $runId,
                'file_size' => $fileSize,
                'is_multi_company' => $isMultiCompany,
            ]);

        } catch (\Exception $e) {
            // -----------------------------------------------------------------
            // 8. Mark export as failed
            // -----------------------------------------------------------------
            $export->update([
                'status' => 'failed',
                'error_message' => substr($e->getMessage(), 0, 1000),
                'completed_at' => now(),
            ]);

            Log::error("GeneratePayrollRunSummaryPdf: Export #{$this->exportId} failed.", [
                'error' => $e->getMessage(),
                'run_id' => $runId ?? 'unknown',
            ]);

            throw $e; // Re-throw so queue marks job as failed
        }
    }

    /**
     * Handle a job failure (called when tries are exhausted).
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical("GeneratePayrollRunSummaryPdf: Job permanently failed for export #{$this->exportId}", [
            'error' => $exception->getMessage(),
        ]);

        // Ensure export is marked as failed
        Export::where('id', $this->exportId)
            ->where('status', '!=', 'failed')
            ->update([
                'status' => 'failed',
                'error_message' => 'Job permanently failed: ' . substr($exception->getMessage(), 0, 800),
                'completed_at' => now(),
            ]);
    }
}
