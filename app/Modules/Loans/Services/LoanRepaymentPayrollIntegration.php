<?php

namespace App\Modules\Loans\Services;

use App\Modules\Loans\Models\Loan;
use App\Modules\Loans\Models\LoanRepayment;
use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Payroll\Models\PayrollRunAdjustment;
use Illuminate\Support\Facades\Log;

/**
 * Integrates loan repayments with the Payroll module.
 *
 * When a payroll run covers a period in which loan repayments are due,
 * this service creates PayrollRunAdjustment records so the deductions
 * appear on payslips. When the run is finalized, the linked repayments
 * are marked as paid.
 */
class LoanRepaymentPayrollIntegration
{
    /**
     * Create PayrollRunAdjustment records for all loan repayments due
     * within the payroll run's period.
     *
     * Called before payroll processing begins.
     *
     * @param PayrollRun $run
     * @return int Number of adjustments created
     */
    public function createAdjustmentsForRun(PayrollRun $run): int
    {
        $periodStart = $run->period_start->toDateString();
        $periodEnd = $run->period_end->toDateString();

        // Find pending repayments due within this pay period
        $repayments = LoanRepayment::pending()
            ->whereBetween('due_date', [$periodStart, $periodEnd])
            ->whereHas('loan', function ($query) {
                $query->where('status', 'active');
            })
            ->with('loan.employee')
            ->get();

        $count = 0;

        foreach ($repayments as $repayment) {
            $loan = $repayment->loan;
            $employee = $loan->employee;

            if (!$employee) {
                continue;
            }

            // Check if an adjustment already exists for this repayment in this run
            $existing = PayrollRunAdjustment::where('payroll_run_id', $run->id)
                ->where('source_type', 'loan_repayment')
                ->where('source_id', $repayment->id)
                ->exists();

            if ($existing) {
                continue;
            }

            // Create the adjustment
            PayrollRunAdjustment::create([
                'company_id' => $loan->company_id,
                'payroll_run_id' => $run->id,
                'employee_id' => $employee->id,
                'type' => 'deduction',
                'label' => 'Loan Repayment - ' . $loan->loan_number,
                'amount' => $repayment->amount,
                'note' => "Installment for loan {$loan->loan_number}",
                'source_type' => 'loan_repayment',
                'source_id' => $repayment->id,
            ]);

            $count++;

            Log::info('Loan repayment adjustment created for payroll run', [
                'payroll_run_id' => $run->id,
                'loan_repayment_id' => $repayment->id,
                'loan_number' => $loan->loan_number,
                'employee_id' => $employee->id,
                'amount' => $repayment->amount,
            ]);
        }

        return $count;
    }

    /**
     * Mark loan repayments as paid when the payroll run is finalized.
     *
     * Called after payroll run finalization.
     *
     * @param PayrollRun $run
     * @return int Number of repayments marked as paid
     */
    public function finalizeRepaymentsForRun(PayrollRun $run): int
    {
        // Find all loan repayment adjustments in this run
        $adjustments = PayrollRunAdjustment::where('payroll_run_id', $run->id)
            ->where('source_type', 'loan_repayment')
            ->get();

        $count = 0;
        $repaymentService = app(LoanRepaymentService::class);

        foreach ($adjustments as $adjustment) {
            $repayment = LoanRepayment::find($adjustment->source_id);

            if (!$repayment) {
                Log::warning('Loan repayment not found for adjustment', [
                    'adjustment_id' => $adjustment->id,
                    'source_id' => $adjustment->source_id,
                ]);
                continue;
            }

            // Skip if already paid
            if ($repayment->status === 'paid') {
                continue;
            }

            // Mark as paid via payroll deduction
            $repaymentService->markAsPaid(
                repayment: $repayment,
                paymentMethod: 'payroll_deduction',
                reference: 'Payroll Run #' . $run->id,
                payrollRunId: $run->id,
            );

            $count++;

            Log::info('Loan repayment marked as paid via payroll', [
                'payroll_run_id' => $run->id,
                'loan_repayment_id' => $repayment->id,
                'loan_number' => $repayment->loan->loan_number ?? 'N/A',
            ]);
        }

        return $count;
    }

    /**
     * Remove loan repayment adjustments for a payroll run (e.g., if reprocessing).
     *
     * @param PayrollRun $run
     * @return int Number of adjustments removed
     */
    public function removeAdjustmentsForRun(PayrollRun $run): int
    {
        return PayrollRunAdjustment::where('payroll_run_id', $run->id)
            ->where('source_type', 'loan_repayment')
            ->delete();
    }
}
