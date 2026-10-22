<?php

namespace App\Modules\Loans\Listeners;

use App\Modules\Loans\Models\Loan;
use QuickerFaster\UILibrary\Events\Workflows\WorkflowApproved;
use QuickerFaster\UILibrary\Events\Workflows\WorkflowRejected;
use QuickerFaster\UILibrary\Events\Workflows\WorkflowRecalled;

/**
 * Syncs Loan status with workflow events.
 *
 * Matches the Payroll (SyncPayrollRunStatus) and Leave (SyncLeaveRequestStatus)
 * patterns. Key behaviour: when a workflow is recalled, the loan returns to
 * 'draft' status so the initiator can edit and resubmit.
 */
class SyncLoanStatus
{
    protected static array $statusMap = [
        'approved'  => 'approved',
        'rejected'  => 'rejected',
        'cancelled' => 'cancelled',
        // 'recalled' intentionally omitted — we handle it manually to set 'draft'
    ];

    /**
     * Handle workflow fully approved.
     */
    public function handleWorkflowApproved(WorkflowApproved $event): void
    {
        if (! $event->completed) {
            return;
        }

        $loan = Loan::withoutCompanyScope()->find($event->workflow->workflowable_id);

        if ($loan && $event->workflow->workflowable_type === Loan::class) {
            $loan->syncStatusFromWorkflow($event->workflow, static::$statusMap);
            $loan->update([
                'approval_date' => now()->toDateString(),
                'approved_by'   => $event->approvingUserId,
            ]);
        }
    }

    /**
     * Handle workflow rejected.
     */
    public function handleWorkflowRejected(WorkflowRejected $event): void
    {
        $loan = Loan::withoutCompanyScope()->find($event->workflow->workflowable_id);

        if ($loan && $event->workflow->workflowable_type === Loan::class) {
            $loan->syncStatusFromWorkflow($event->workflow, static::$statusMap);
        }
    }

    /**
     * Handle workflow recalled — return to draft so the initiator can
     * edit and resubmit. Matches the Payroll module pattern.
     */
    public function handleWorkflowRecalled(WorkflowRecalled $event): void
    {
        $loan = Loan::withoutCompanyScope()->find($event->workflow->workflowable_id);

        if ($loan && $event->workflow->workflowable_type === Loan::class) {
            // Set status back to 'draft' so the user can edit and resubmit
            $loan->update(['status' => 'draft']);
        }
    }
}
