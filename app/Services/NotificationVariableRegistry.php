<?php

namespace App\Services;

use QuickerFaster\UILibrary\Contracts\Notifications\TemplateVariableRegistry;

class NotificationVariableRegistry implements TemplateVariableRegistry
{
    /**
     * Default variable definitions for all notification types used by the
     * consuming application, including library built-in types.
     *
     * @var array<string, array<string, string>>
     */
    protected array $defaults = [
        // ─── Library built-in types (carried over from DefaultTemplateVariableRegistry) ───
        'workflow_approval' => [
            'workflow_id' => 'Workflow ID',
            'definition_key' => 'Definition Key',
            'step_name' => 'Step Name',
            'requester_name' => 'Requester Name',
        ],
        'workflow_approved' => [
            'workflow_id' => 'Workflow ID',
            'definition_key' => 'Definition Key',
            'approver_name' => 'Approver Name',
        ],
        'workflow_denied' => [
            'workflow_id' => 'Workflow ID',
            'definition_key' => 'Definition Key',
            'denier_name' => 'Denier Name',
            'reason' => 'Reason',
        ],
        'workflow_cancelled' => [
            'workflow_id' => 'Workflow ID',
            'definition_key' => 'Definition Key',
        ],
        'user_welcome' => [
            'user_name' => 'User Name',
            'user_email' => 'User Email',
        ],
        'user_password_reset' => [
            'user_name' => 'User Name',
            'reset_link' => 'Reset Link',
        ],

        // ─── Library seeder types ─────────────────────────────────────────────────────
        'document_generated' => [
            'name' => 'Document Name',
        ],
        'report_ready' => [
            'report_name' => 'Report Name',
        ],
        'workflow_stage_changed' => [
            'workflow_name' => 'Workflow Name',
            'stage_name' => 'Stage Name',
        ],

        // ─── Consuming-app workflow notification types ─────────────────────────────────
        'workflow_submitted' => [
            'workflow_name' => 'Workflow Name',
            'entity_name' => 'Entity Name',
            'submitted_by' => 'Submitted By',
            'submitted_at' => 'Submitted At',
            'review_url' => 'Review URL',
        ],
        'workflow_rejected' => [
            'workflow_name' => 'Workflow Name',
            'entity_name' => 'Entity Name',
            'rejected_by' => 'Rejected By',
            'rejected_at' => 'Rejected At',
            'reason' => 'Reason',
        ],
        'workflow_recalled' => [
            'workflow_name' => 'Workflow Name',
            'entity_name' => 'Entity Name',
            'recalled_by' => 'Recalled By',
            'recalled_at' => 'Recalled At',
        ],

        // ─── Consuming-app ESS notification types ──────────────────────────────────────
        'payslip_ready' => [
            'employee_name' => 'Employee Name',
            'period' => 'Period',
            'payslip_url' => 'Payslip URL',
        ],
        'leave_approved' => [
            'employee_name' => 'Employee Name',
            'leave_type' => 'Leave Type',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'days' => 'Days',
            'approver_name' => 'Approver Name',
        ],
        'leave_denied' => [
            'employee_name' => 'Employee Name',
            'leave_type' => 'Leave Type',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'days' => 'Days',
            'approver_name' => 'Approver Name',
            'reason' => 'Reason',
        ],
        'leave_submitted' => [
            'employee_name' => 'Employee Name',
            'leave_type' => 'Leave Type',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'days' => 'Days',
        ],
        'upcoming_holiday' => [
            'holiday_name' => 'Holiday Name',
            'holiday_date' => 'Holiday Date',
        ],
        'clock_out_reminder' => [
            'employee_name' => 'Employee Name',
            'clock_in_time' => 'Clock-In Time',
            'clock_out_url' => 'Clock-Out URL',
        ],
    ];

    /**
     * @inheritDoc
     */
    public function variables(string $type): array
    {
        // Check config overrides first, then fall back to built-in defaults.
        $configOverrides = config("ui-library.notifications.template_variables.{$type}", []);

        if (! empty($configOverrides)) {
            return $configOverrides;
        }

        return $this->defaults[$type] ?? [];
    }
}
