<?php

namespace App\Modules\Loans\Listeners;

use Illuminate\Support\Facades\Log;
use QuickerFaster\UILibrary\Events\DataTableRecordSaved;
use QuickerFaster\UILibrary\Services\Notifications\NotificationService;
use App\Modules\Loans\Models\Loan;
use App\Modules\Hr\Models\Employee;

class LoanEventListener
{
    /**
     * Handle DataTable record save events for Loan.
     */
    public function handle(DataTableRecordSaved $event): void
    {
        if ($event->model !== Loan::class) {
            return;
        }

        $recordId = $event->component?->recordId
            ?? $event->newRecord['id']
            ?? $event->oldRecord['id']
            ?? null;

        if (!$recordId) {
            return;
        }

        $loan = Loan::find($recordId);
        if (!$loan) {
            return;
        }

        if ($event->action === DataTableRecordSaved::ACTION_CREATED) {
            $this->handleCreated($loan);
        } elseif ($event->action === DataTableRecordSaved::ACTION_UPDATED) {
            $this->handleUpdated($loan, $event);
        }
    }

    /**
     * New loan created — notify if submitted for approval.
     */
    protected function handleCreated(Loan $loan): void
    {
        if ($loan->status !== 'pending_approval') {
            return;
        }

        $this->notifyEmployee($loan, 'loan_submitted');
    }

    /**
     * Existing loan updated — notify on status changes.
     */
    protected function handleUpdated(Loan $loan, DataTableRecordSaved $event): void
    {
        $newRecord = $event->newRecord ?? [];

        // Check if status changed
        if (!array_key_exists('status', $newRecord)) {
            return;
        }

        $newStatus = $newRecord['status'];

        Log::info('LoanEventListener: Status change detected', [
            'loan_id' => $loan->id,
            'loan_number' => $loan->loan_number,
            'new_status' => $newStatus,
        ]);

        match ($newStatus) {
            'approved' => $this->notifyEmployee($loan, 'loan_approved'),
            'rejected' => $this->notifyEmployee($loan, 'loan_rejected'),
            'completed' => $this->notifyEmployee($loan, 'loan_completed'),
            default => null,
        };
    }

    /**
     * Send notification to the loan's employee.
     */
    protected function notifyEmployee(Loan $loan, string $type): void
    {
        $employee = $loan->employee;
        if (!$employee || !$employee->user_id) {
            return;
        }

        $notificationService = app(NotificationService::class);

        $variables = [
            'employee_name' => trim($employee->first_name . ' ' . $employee->last_name),
            'loan_number' => $loan->loan_number,
            'amount' => number_format($loan->amount, 2),
            'installment_amount' => number_format($loan->monthly_installment, 2),
            'start_date' => $loan->start_date?->format('M d, Y') ?? 'N/A',
            'due_date' => $loan->start_date?->format('M d, Y') ?? 'N/A',
            'balance' => number_format($loan->balance, 2),
            'url' => url('/hr/my-loans'),
        ];

        $notificationService->dispatchAsync(
            notifiable: $employee->user,
            type: $type,
            data: $variables,
        );
    }
}
