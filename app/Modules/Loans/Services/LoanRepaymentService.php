<?php

namespace App\Modules\Loans\Services;

use App\Modules\Loans\Models\Loan;
use App\Modules\Loans\Models\LoanRepayment;
use Illuminate\Support\Facades\Log;

class LoanRepaymentService
{
    protected LoanCalculator $calculator;

    public function __construct()
    {
        $this->calculator = app(LoanCalculator::class);
    }

    /**
     * Generate the repayment schedule for a loan and persist it.
     *
     * Called when a loan is activated (disbursed).
     *
     * @param Loan       $loan
     * @param string|null $startDate  First repayment date (defaults to next month)
     * @return array                 The generated schedule
     */
    public function generateSchedule(Loan $loan, ?string $startDate = null): array
    {
        $startDate = $startDate ?? now()->addMonth()->toDateString();

        $schedule = $this->calculator->generateSchedule(
            principal: (float) $loan->amount,
            annualRate: (float) $loan->interest_rate,
            months: (int) $loan->repayment_months,
            startDate: $startDate,
        );

        // Delete any existing repayments (regeneration scenario)
        $loan->repayments()->delete();

        // Persist each installment
        foreach ($schedule as $installment) {
            LoanRepayment::create([
                'loan_id' => $loan->id,
                'amount' => $installment['amount'],
                'principal' => $installment['principal_component'],
                'interest' => $installment['interest_component'],
                'due_date' => $installment['due_date'],
                'status' => 'pending',
            ]);
        }

        // Update the loan's cached schedule and dates
        $loan->update([
            'repayment_schedule' => $schedule,
            'start_date' => $startDate,
            'expected_end_date' => end($schedule)['due_date'] ?? null,
            'balance' => $loan->total_repayable,
        ]);

        Log::info('Loan repayment schedule generated', [
            'loan_id' => $loan->id,
            'loan_number' => $loan->loan_number,
            'installments' => count($schedule),
            'start_date' => $startDate,
        ]);

        return $schedule;
    }

    /**
     * Mark a repayment as paid.
     *
     * @param LoanRepayment $repayment
     * @param string|null   $paymentMethod  payroll_deduction, manual, bank_transfer
     * @param string|null   $reference      Transaction reference
     * @param int|null      $payrollRunId   Link to payroll run
     * @return LoanRepayment
     */
    public function markAsPaid(
        LoanRepayment $repayment,
        ?string $paymentMethod = 'manual',
        ?string $reference = null,
        ?int $payrollRunId = null
    ): LoanRepayment {
        $repayment->update([
            'status' => 'paid',
            'paid_date' => now()->toDateString(),
            'payment_method' => $paymentMethod,
            'reference' => $reference,
            'payroll_run_id' => $payrollRunId,
        ]);

        // Update the loan's paid_amount and balance
        $loan = $repayment->loan;
        $totalPaid = $loan->repayments()->where('status', 'paid')->sum('amount');
        $balance = max(0, $loan->total_repayable - $totalPaid);

        $loan->update([
            'paid_amount' => $totalPaid,
            'balance' => $balance,
        ]);

        // Auto-complete the loan if fully paid
        if ($balance <= 0 && $loan->status === 'active') {
            $loan->update(['status' => 'completed']);
            Log::info('Loan fully repaid', [
                'loan_id' => $loan->id,
                'loan_number' => $loan->loan_number,
            ]);
        }

        return $repayment;
    }

    /**
     * Waive a repayment (mark as waived, no payment required).
     */
    public function waive(LoanRepayment $repayment): LoanRepayment
    {
        $repayment->update(['status' => 'waived']);

        // Recalculate balance
        $loan = $repayment->loan;
        $totalPaid = $loan->repayments()->where('status', 'paid')->sum('amount');
        $balance = max(0, $loan->total_repayable - $totalPaid);

        $loan->update([
            'paid_amount' => $totalPaid,
            'balance' => $balance,
        ]);

        return $repayment;
    }

    /**
     * Process overdue repayments — mark pending repayments past their due date as overdue.
     *
     * Called by the scheduled command.
     *
     * @return int Number of repayments marked as overdue
     */
    public function processOverdueRepayments(): int
    {
        $count = 0;

        LoanRepayment::pending()
            ->where('due_date', '<', now()->toDateString())
            ->each(function (LoanRepayment $repayment) use (&$count) {
                $repayment->update(['status' => 'overdue']);
                $count++;

                Log::info('Loan repayment marked as overdue', [
                    'repayment_id' => $repayment->id,
                    'loan_id' => $repayment->loan_id,
                    'due_date' => $repayment->due_date->toDateString(),
                ]);
            });

        return $count;
    }

    /**
     * Get upcoming due repayments within the next N days.
     *
     * @param int $days Number of days to look ahead
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUpcomingDueRepayments(int $days = 7)
    {
        $targetDate = now()->addDays($days)->toDateString();

        return LoanRepayment::pending()
            ->where('due_date', '<=', $targetDate)
            ->where('due_date', '>=', now()->toDateString())
            ->with('loan.employee')
            ->get();
    }
}
