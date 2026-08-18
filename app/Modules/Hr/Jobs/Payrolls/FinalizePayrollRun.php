<?php

namespace App\Modules\Hr\Jobs\Payrolls;

use App\Modules\Hr\Models\PayrollRun;
use App\Modules\Hr\Models\PayrollRunProgress;
use App\Modules\Hr\Services\Payroll\PayrollCalculator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FinalizePayrollRun implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 3;

    protected int $payrollRunId;

    public function __construct(int $payrollRunId)
    {
        $this->payrollRunId = $payrollRunId;
    }

    public function handle(PayrollCalculator $calculator): void
    {
        $run = PayrollRun::withoutCompanyScope()->find($this->payrollRunId);
        if (!$run) {
            Log::error("Finalize job: Payroll run #{$this->payrollRunId} not found.");
            return;
        }

        // Idempotency guard: skip if already finalized
        if ($run->finalized_at !== null) {
            Log::info("Finalize job: Payroll run #{$this->payrollRunId} already finalized. Skipping.");
            return;
        }

        // Check if all employees are processed
$progress = PayrollRunProgress::withoutCompanyScope()
    ->where('payroll_run_id', $this->payrollRunId)
    ->first();

        if (!$progress || $progress->processed_employees < $progress->total_employees) {
            Log::warning("Finalize job: Not all employees processed for run #{$this->payrollRunId}. Processed: {$progress->processed_employees}, Total: {$progress->total_employees}");
            // Re-dispatch with a new delay if not complete? Or mark as failed?
            // For safety, we'll wait a bit longer.
            if ($progress && $progress->processed_employees > 0) {
                // If some progress, re-dispatch in 60 seconds.
                self::dispatch($this->payrollRunId)->delay(now()->addSeconds(60));
                return;
            }
            // If no progress, mark as failed.
            $run->update([
                'calculation_status' => 'failed',
                'failure_reason' => 'Finalization called but no employees processed.',
            ]);
            return;
        }

        // All employees processed – update totals and aggregations.
        // calculation_status is now set by ProcessEmployeeBatch when the last batch finishes,
        // so payslips appear immediately without waiting for this delayed job.
        $calculator->setRun($run);
        $calculator->updateRunTotals();

        // Mark the run as finalized
        $run->update([
            'finalized_at' => now(),
            'total_employees' => $progress->total_employees,
            'processed_employees' => $progress->processed_employees,
        ]);

        // Update progress status
        PayrollRunProgress::withoutCompanyScope()
            ->where('payroll_run_id', $this->payrollRunId)
            ->update(['status' => 'finalized']);

        Log::info("Payroll run #{$this->payrollRunId} finalized successfully.");
    }
}
