<?php

namespace App\Modules\Hr\Jobs\Payrolls;

use App\Modules\Hr\Models\PayrollRun;
use App\Modules\Hr\Models\EmployeePosition;
use App\Modules\Hr\Models\PayrollRunProgress;
use App\Modules\Hr\Services\Payroll\PayrollCalculator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessEmployeeBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout;
    public $tries = 3;

    protected int $payrollRunId;
    protected array $employeeIds;

    public function __construct(int $payrollRunId, array $employeeIds)
    {
        $this->payrollRunId = $payrollRunId;
        $this->employeeIds = $employeeIds;
        $this->timeout = config('quick_hr_payroll.batch_timeout', 60);
    }

public function handle(PayrollCalculator $calculator): void
{
    $run = PayrollRun::withoutCompanyScope()->find($this->payrollRunId);
    if (!$run) {
        Log::error("Payroll run #{$this->payrollRunId} not found in batch job.");
        return;
    }

    // Get employee positions for these employee IDs (with their relationships)
    $positions = EmployeePosition::withoutCompanyScope()
        ->whereIn('employee_id', $this->employeeIds)
        ->where('employment_status', 'Active')
        ->with([
            'employee' => function ($q) {
                $q->withoutCompanyScope();
            },
            'location',
            'employee.employeeProfile',
            'employee.user',
        ])
        ->get();

    if ($positions->isEmpty()) {
        Log::info("Batch job: No active positions found for employee IDs: " . implode(',', $this->employeeIds));
        return;
    }

    // Process each employee inside its own transaction to keep it atomic
    foreach ($positions as $position) {
        DB::transaction(function () use ($calculator, $position, $run) {
            $calculator->setRun($run);
            $calculator->calculateForEmployee($position);
            // The calculator already sets company_id on the payslip
        });

        // Increment progress immediately after each employee
        PayrollRunProgress::withoutCompanyScope()
            ->where('payroll_run_id', $this->payrollRunId)
            ->increment('processed_employees');

        // ALSO increment payroll_runs table
        \App\Modules\Hr\Models\PayrollRun::withoutCompanyScope()
            ->where('id', $this->payrollRunId)
            ->increment('processed_employees');
    }

    // After processing the entire batch, check if all employees are done
    $progress = PayrollRunProgress::withoutCompanyScope()
        ->where('payroll_run_id', $this->payrollRunId)
        ->first();

    if ($progress && $progress->processed_employees >= $progress->total_employees) {
        $run = PayrollRun::withoutCompanyScope()->find($this->payrollRunId);
        if ($run && $run->calculation_status !== 'completed') {
            $run->update(['calculation_status' => 'completed']);
        }
        // Dispatch finalization immediately from the last batch
        \App\Modules\Hr\Jobs\Payrolls\FinalizePayrollRun::dispatch($this->payrollRunId);
    }

    Log::info("Batch job processed {$positions->count()} employees for run #{$this->payrollRunId}");
}

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessEmployeeBatch failed', [
            'run_id' => $this->payrollRunId,
            'error' => $exception->getMessage(),
        ]);

        $run = \App\Modules\Hr\Models\PayrollRun::find($this->payrollRunId);
        if ($run) {
            $run->update(['calculation_status' => 'failed']);
        }
    }
}
