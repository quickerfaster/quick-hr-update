<?php

return [
    'loan_request' => [
        'label' => 'Loan Request Approval',
        'name' => 'Loan Request Approval',
        'entity_type' => 'Loan',
        'initiators' => ['employee', 'manager', 'super_admin'],
        'steps' => [
            [
                'name' => 'Manager Review',
                'step_type' => 'approval',
                'approval_mode' => 'any',
                // 'employee_manager' resolves to the submitting employee's
                // specific line manager (via HrsApproverResolver), falling
                // back to 'hr_manager' when no manager is assigned.
                // Mirrors the Leave module's leave_request workflow.
                'roles' => ['employee_manager', 'hr_manager'],
            ],
            [
                'name' => 'HR Review',
                'step_type' => 'approval',
                'approval_mode' => 'any',
                'roles' => ['hr_manager', 'hr_officer'],
            ],
            [
                'name' => 'Finance Approval',
                'step_type' => 'approval',
                'approval_mode' => 'any',
                'roles' => ['finance_manager', 'super_admin'],
            ],
        ],
        'notifications' => [
            'enabled' => true,
            'types' => [
                'submitted' => 'workflow_submitted',
                'approved' => 'workflow_approved',
                'rejected' => 'workflow_rejected',
                'recalled' => 'workflow_recalled',
                'cancelled' => 'workflow_cancelled',
                'submitted_initiator' => 'workflow_submitted_initiator',
                'stage_advanced' => 'workflow_stage_advanced',
                'workflow_completed' => 'workflow_completed',
            ],
        ],
    ],
];
