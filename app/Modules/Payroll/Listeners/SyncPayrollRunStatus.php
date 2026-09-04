<?php

namespace App\Modules\Payroll\Listeners;

use App\Modules\Payroll\Models\PayrollRun;
use QuickerFaster\UILibrary\Events\Workflows\WorkflowApproved;
use QuickerFaster\UILibrary\Events\Workflows\WorkflowRejected;
use QuickerFaster\UILibrary\Events\Workflows\WorkflowRecalled;

class SyncPayrollRunStatus
{
    /**
     * Status mapping: workflow status → payroll_runs.status
     */
    protected static array $statusMap = [
        'approved'  => 'approved',
        'rejected'  => 'cancelled',
        'cancelled' => 'cancelled',
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

        $workflowable = PayrollRun::withoutCompanyScope()
            ->find($event->workflow->workflowable_id);

        if ($workflowable && $event->workflow->workflowable_type === PayrollRun::class) {
            $workflowable->syncStatusFromWorkflow($event->workflow, static::$statusMap);
        }
    }

    /**
     * Handle workflow rejected event.
     */
    public function handleWorkflowRejected(WorkflowRejected $event): void
    {
        $workflowable = PayrollRun::withoutCompanyScope()
            ->find($event->workflow->workflowable_id);

        if ($workflowable && $event->workflow->workflowable_type === PayrollRun::class) {
            $workflowable->syncStatusFromWorkflow($event->workflow, static::$statusMap);
        }
    }

    /**
     * Handle workflow recalled event.
     */
    public function handleWorkflowRecalled(WorkflowRecalled $event): void
    {
        $workflowable = PayrollRun::withoutCompanyScope()
            ->find($event->workflow->workflowable_id);

        if ($workflowable && $event->workflow->workflowable_type === PayrollRun::class) {
            $workflowable->syncStatusFromWorkflow($event->workflow, static::$statusMap);
            // Reset wizard step so the user can edit before resubmitting.
            // Step 3 is the review step — user can navigate back to 1 or 2 from there.
            $workflowable->update(['current_step' => 3]);
        }
    }
}