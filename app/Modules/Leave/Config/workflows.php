<?php

return [
    'leave_request' => [
        'label' => 'Leave Request Approval',
        'name' => 'Leave Request Approval',
        'entity_type' => 'LeaveRequest',
        'initiators' => ['employee', 'hr_manager', 'super_admin'],
        'steps' => [
            [
                'name' => 'Manager Review',
                'step_type' => 'approval',
                'approval_mode' => 'any',
                'roles' => ['line_manager', 'hr_manager'],
            ],
            [
                'name' => 'HR Authorization',
                'step_type' => 'approval',
                'approval_mode' => 'any',
                'roles' => ['hr_manager', 'super_admin'],
            ],
        ],
        'notifications' => [
            'enabled' => true,
            'types' => [
                'submitted' => 'workflow_submitted',
                'approved' => 'workflow_approved',
                'rejected' => 'workflow_rejected',
                'recalled' => 'workflow_recalled',
            ],
        ],
    ],
];