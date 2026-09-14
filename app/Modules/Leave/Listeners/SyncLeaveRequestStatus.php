<?php

namespace App\Modules\Leave\Listeners;

use App\Modules\Leave\Models\LeaveBalance;
use App\Modules\Leave\Models\LeaveRequest;
use QuickerFaster\UILibrary\Events\Workflows\WorkflowApproved;
use QuickerFaster\UILibrary\Events\Workflows\WorkflowCancelled;
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

        // After syncing status to 'Approved', deduct from leave balance
        $leaveRequest = LeaveRequest::find($event->workflow->workflowable_id);
        if ($leaveRequest && $leaveRequest->status === 'Approved') {
            $leaveType = $leaveRequest->leaveType;
            if ($leaveType && $leaveType->deducts_from_balance) {
                $year = \Carbon\Carbon::parse($leaveRequest->start_date)->year;
                $balance = LeaveBalance::where('employee_id', $leaveRequest->employee_id)
                    ->where('leave_type_id', $leaveRequest->leave_type_id)
                    ->where('year', $year)
                    ->first();

                if ($balance) {
                    $daysToDeduct = $leaveRequest->workdays_count ?? $this->calculateWorkdays($leaveRequest);
                    $balance->decrement('balance', $daysToDeduct);
                }
            }
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
     * Calculate the number of workdays (excluding weekends) between start and end date.
     */
    private function calculateWorkdays($leaveRequest): int
    {
        $start = \Carbon\Carbon::parse($leaveRequest->start_date);
        $end = \Carbon\Carbon::parse($leaveRequest->end_date);
        $days = 0;
        while ($start->lte($end)) {
            if (! $start->isWeekend()) {
                $days++;
            }
            $start->addDay();
        }
        return $days;
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

        // Restore balance if it was deducted (safety net)
        $leaveRequest = LeaveRequest::find($event->workflow->workflowable_id);
        if ($leaveRequest) {
            $leaveType = $leaveRequest->leaveType;
            if ($leaveType && $leaveType->deducts_from_balance) {
                $year = \Carbon\Carbon::parse($leaveRequest->start_date)->year;
                $balance = LeaveBalance::where('employee_id', $leaveRequest->employee_id)
                    ->where('leave_type_id', $leaveRequest->leave_type_id)
                    ->where('year', $year)
                    ->first();

                if ($balance && $balance->last_accrual_date) {
                    // Balance was already deducted — restore it
                    $daysToRestore = $leaveRequest->workdays_count ?? 0;
                    if ($daysToRestore > 0) {
                        $balance->increment('balance', $daysToRestore);
                    }
                }
            }
        }
    }

    /**
     * Handle workflow cancelled event.
     */
    public function handleWorkflowCancelled($event): void
    {
        $leaveRequest = LeaveRequest::find($event->workflow->workflowable_id);
        if (!$leaveRequest) return;

        $leaveRequest->update(['status' => 'Cancelled']);

        $leaveType = $leaveRequest->leaveType;
        if ($leaveType && $leaveType->deducts_from_balance) {
            $year = \Carbon\Carbon::parse($leaveRequest->start_date)->year;
            $balance = LeaveBalance::where('employee_id', $leaveRequest->employee_id)
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->where('year', $year)
                ->first();

            if ($balance) {
                $daysToRestore = $leaveRequest->workdays_count ?? 0;
                if ($daysToRestore > 0) {
                    $balance->increment('balance', $daysToRestore);
                }
            }
        }
    }
}
