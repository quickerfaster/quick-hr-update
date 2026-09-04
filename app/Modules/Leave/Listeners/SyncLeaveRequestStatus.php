<?php

namespace App\Modules\Leave\Listeners;

use App\Modules\Leave\Models\LeaveRequest;
use QuickerFaster\UILibrary\Events\Workflows\WorkflowApproved;
use QuickerFaster\UILibrary\Events\Workflows\WorkflowRejected;
use QuickerFaster\UILibrary\Events\Workflows\WorkflowRecalled;

class SyncLeaveRequestStatus
{
    /**
     * Status mapping: workflow status → leave_requests.status
     *
     * Note: LeaveRequest uses PascalCase status values (Pending, Approved, Denied, Cancelled)
     * while the Workflow system uses lowercase (pending, approved, rejected, cancelled).
     * Also, LeaveRequest uses 'Denied' while Workflow uses 'rejected'.
     */
    protected static array $statusMap = [
        'approved'  => 'Approved',
        'rejected'  => 'Denied',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Handle workflow approved event.
     * Only sync when the entire workflow is completed (all steps approved).
     */
    public function handleWorkflowApproved(WorkflowApproved $event): void
    {
        if (! $event->completed) {
            return; // Individual step approved, but workflow still in progress
        }

        $workflowable = LeaveRequest::withoutCompanyScope()
            ->find($event->workflow->workflowable_id);

        if ($workflowable && $event->workflow->workflowable_type === LeaveRequest::class) {
            $workflowable->syncStatusFromWorkflow($event->workflow, static::$statusMap);
        }
    }

    /**
     * Handle workflow rejected event.
     */
    public function handleWorkflowRejected(WorkflowRejected $event): void
    {
        $workflowable = LeaveRequest::withoutCompanyScope()
            ->find($event->workflow->workflowable_id);

        if ($workflowable && $event->workflow->workflowable_type === LeaveRequest::class) {
            $workflowable->syncStatusFromWorkflow($event->workflow, static::$statusMap);
        }
    }

    /**
     * Handle workflow recalled event.
     */
    public function handleWorkflowRecalled(WorkflowRecalled $event): void
    {
        $workflowable = LeaveRequest::withoutCompanyScope()
            ->find($event->workflow->workflowable_id);

        if ($workflowable && $event->workflow->workflowable_type === LeaveRequest::class) {
            $workflowable->syncStatusFromWorkflow($event->workflow, static::$statusMap);
        }
    }
}