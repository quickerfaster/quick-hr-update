<?php

return [
    'widgets' => [
        0 => [
            'type' => 'stat',
            'title' => 'Pending Invitations',
            'size' => 'col-12',
            'model' => 'QuickerFaster\\UILibrary\\Models\\Invitation',
            'icon' => 'fas fa-envelope-open-text',
            'aggregate' => 'count',
            'conditions' => [
                0 => [
                    0 => 'status',
                    1 => '=',
                    2 => 'pending',
                ],
            ],
            'width' => 3,
        ],
        1 => [
            'type' => 'stat',
            'title' => 'Recent Hires This Month',
            'size' => 'col-12',
            'model' => 'App\\Modules\\Hr\\Models\\Employee',
            'icon' => 'fas fa-user-plus',
            'aggregate' => 'count',
            'width' => 3,
        ],
        // --- Onboarding Gap Tracking ---
        2 => [
            'type' => 'stat',
            'title' => 'Missing Position',
            'size' => 'col-12',
            'model' => 'App\\Modules\\Hr\\Models\\Employee',
            'icon' => 'fas fa-user-tag',
            'aggregate' => 'count',
            'conditions' => [
                ['onboarding_status', '=', 'position_pending'],
            ],
            'width' => 3,
            'color' => 'warning',
        ],
        3 => [
            'type' => 'stat',
            'title' => 'Missing Company',
            'size' => 'col-12',
            'model' => 'App\\Modules\\Hr\\Models\\Employee',
            'icon' => 'fas fa-building',
            'aggregate' => 'count',
            'conditions' => [
                ['onboarding_status', '=', 'company_pending'],
            ],
            'width' => 3,
            'color' => 'danger',
        ],
        4 => [
            'type' => 'stat',
            'title' => 'Incomplete Onboarding',
            'size' => 'col-12',
            'model' => 'App\\Modules\\Hr\\Models\\Employee',
            'icon' => 'fas fa-exclamation-triangle',
            'aggregate' => 'count',
            'conditions' => [
                ['onboarding_status', '!=', 'complete'],
                ['onboarding_status', '!=', null],
            ],
            'width' => 3,
            'color' => 'warning',
        ],
        // --- End Onboarding Gap Tracking ---
        5 => [
            'type' => 'list',
            'title' => 'Incomplete Onboarding',
            'size' => 'col-12',
            'model' => 'App\\Modules\\Hr\\Models\\Employee',
            'icon' => 'fas fa-clipboard-list',
            'description' => 'Employees needing position or company assignment',
            'limit' => 10,
            'sort' => ['created_at', 'asc'],
            'conditions' => [
                ['onboarding_status', '!=', 'complete'],
                ['onboarding_status', '!=', null],
            ],
            'columns' => [
                ['label' => 'Name', 'field' => 'first_name'],
                ['label' => 'Email', 'field' => 'email'],
                ['label' => 'Status', 'field' => 'onboarding_status', 'format' => 'badge'],
            ],
            'width' => 6,
            'show_view_all' => false,
            'id_field' => 'user_id',
            'row_actions' => [
                [
                    'label'     => 'Assign Company',
                    'icon'      => 'fas fa-building',
                    'event'     => 'openDrawer',
                    'params'    => [
                        'component' => 'qf.user-company-assignment',
                        'params'    => ['user' => '{{ user_id }}'],
                        'title'     => 'Assign Company',
                    ],
                    'style'     => 'danger',
                ],
                [
                    'label'     => 'Add Job Info',
                    'icon'      => 'fas fa-briefcase',
                    'event'     => 'openDrawer',
                    'params'    => [
                        'component' => 'qf.data-table-form',
                        'params'    => [
                            'configKey'     => 'hr.employee_position',
                            'recordId'      => '{{ employeePosition.id }}',
                            'prefilledData' => ['employee_id' => '{{ id }}'],
                            'inline'        => true,
                        ],
                        'title'     => 'Add Job Details',
                    ],
                    'style'     => 'warning',
                ],
            ],
        ],
        6 => [
            'type' => 'list',
            'title' => 'Recent Invitations',
            'size' => 'col-12',
            'model' => 'QuickerFaster\\UILibrary\\Models\\Invitation',
            'icon' => 'fas fa-envelope',
            'description' => 'Latest 5 invitations sent',
            'limit' => 5,
            'sort' => [
                0 => 'created_at',
                1 => 'desc',
            ],
            'columns' => [
                0 => [
                    'label' => 'Email',
                    'field' => 'email',
                ],
                1 => [
                    'label' => 'Status',
                    'field' => 'status',
                ],
                2 => [
                    'label' => 'Role',
                    'field' => 'role',
                ],
                7 => [
                    'label' => 'Sent',
                    'field' => 'created_at',
                    'format' => 'date',
                ],
            ],
            'width' => 6,
            'show_view_all' => true,
            'view_all_link' => '/hr/invitations',
        ],
        7 => [
            'type' => 'action_card',
            'title' => 'Send Invitation',
            'size' => 'col-12',
            'icon' => 'fas fa-paper-plane',
            'description' => 'Invite a new user to the platform',
            'actions' => [
                0 => [
                    'label' => 'Send',
                    'event' => 'openDrawer',
                    'params' => [
                        'component' => 'qf.data-table-form',
                        'params' => [
                            'configKey' => 'admin.invitation',
                            'recordId' => null,
                        ],
                        'title' => 'Send Invitation',
                    ],
                    'style' => 'primary',
                ],
            ],
            'width' => 3,
        ],
        8 => [
            'type' => 'action_card',
            'title' => 'Assign Companies',
            'size' => 'col-12',
            'icon' => 'fas fa-building',
            'color' => 'danger',
            'description' => 'Bulk-assign companies to employees missing assignment',
            'actions' => [
                0 => [
                    'label' => 'Assign',
                    'event' => 'openDrawer',
                    'params' => [
                        'component' => 'qf.user-company-assignment',
                        'params' => [],
                        'title' => 'Assign Companies to Employees',
                    ],
                    'style' => 'danger',
                ],
            ],
            'width' => 3,
        ],
        9 => [
            'type' => 'action_card',
            'title' => 'Onboard New Hire',
            'size' => 'col-12',
            'icon' => 'fas fa-magic',
            'description' => 'Walk through the employee onboarding wizard',
            'actions' => [
                0 => [
                    'label' => 'Start',
                    'event' => 'navigate',
                    'params' => ['url' => '/hr/employee-onboarding'],
                    'style' => 'primary',
                ],
            ],
            'width' => 3,
        ],
    ],
    'roles' => [
        'admin' => 'full',
        'manager' => 'limited',
        'user' => 'basic',
    ],
    'layout' => [
        'columns' => 12,
        'gutter' => 3,
    ],
];
