<?php

return [
    'payroll_run' => [
        'label' => 'Payroll Run Approval',
        'name' => 'Payroll Run Approval',
        'entity_type' => 'PayrollRun',
        'initiators' => ['payroll_officer', 'hr_manager', 'super_admin'],
        'steps' => [
            [
                'name' => 'Payroll Officer Review',
                'step_type' => 'approval',
                'approval_mode' => 'any',
                'roles' => ['payroll_officer'],
            ],
            [
                'name' => 'HR Manager Authorization',
                'step_type' => 'approval',
                'approval_mode' => 'any',
                'roles' => ['hr_manager'],
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
