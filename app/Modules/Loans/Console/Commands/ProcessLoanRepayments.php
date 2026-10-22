<?php

namespace App\Modules\Loans\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Loans\Services\LoanRepaymentService;
use QuickerFaster\UILibrary\Services\Notifications\NotificationService;
use App\Modules\Loans\Models\LoanRepayment;

class ProcessLoanRepayments extends Command
{
    protected $signature = 'loans:process-repayments';
    protected $description = 'Mark overdue loan repayments and send reminders for upcoming due dates.';

    /**
     * Execute the console command.
     */
    public function handle(LoanRepaymentService $repaymentService, NotificationService $notificationService): int
    {
        $this->info('Processing loan repayments...');

        // 1. Mark overdue repayments
        $overdueCount = $repaymentService->processOverdueRepayments();
        $this->info("Marked {$overdueCount} repayment(s) as overdue.");

        // 2. Send reminders for repayments due within 3 days
        $upcomingRepayments = $repaymentService->getUpcomingDueRepayments(3);

        $reminderCount = 0;
        foreach ($upcomingRepayments as $repayment) {
            $loan = $repayment->loan;
            $employee = $loan?->employee;

            if (!$employee || !$employee->user_id) {
                continue;
            }

            $notificationService->dispatchAsync(
                notifiable: $employee->user,
                type: 'repayment_due',
                data: [
                    'employee_name' => trim($employee->first_name . ' ' . $employee->last_name),
                    'loan_number' => $loan->loan_number,
                    'installment_amount' => number_format($repayment->amount, 2),
                    'due_date' => $repayment->due_date->format('M d, Y'),
                    'balance' => number_format($loan->balance, 2),
                    'url' => url('/hr/my-loans'),
                ],
            );

            $reminderCount++;
        }

        $this->info("Sent {$reminderCount} repayment reminder(s).");

        // 3. Send overdue notifications
        $overdueRepayments = LoanRepayment::overdue()
            ->where('due_date', '>=', now()->subDays(1)->toDateString())
            ->with('loan.employee')
            ->get();

        $overdueNotifyCount = 0;
        foreach ($overdueRepayments as $repayment) {
            $loan = $repayment->loan;
            $employee = $loan?->employee;

            if (!$employee || !$employee->user_id) {
                continue;
            }

            $notificationService->dispatchAsync(
                notifiable: $employee->user,
                type: 'repayment_overdue',
                data: [
                    'employee_name' => trim($employee->first_name . ' ' . $employee->last_name),
                    'loan_number' => $loan->loan_number,
                    'installment_amount' => number_format($repayment->amount, 2),
                    'due_date' => $repayment->due_date->format('M d, Y'),
                    'balance' => number_format($loan->balance, 2),
                    'url' => url('/hr/my-loans'),
                ],
            );

            $overdueNotifyCount++;
        }

        $this->info("Sent {$overdueNotifyCount} overdue notification(s).");

        return self::SUCCESS;
    }
}
