<?php

return [
    'title' => 'Onboarding Overview',
    'description' => 'Track invitations, recent hires, and manage the onboarding pipeline',
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
        2 => [
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
                3 => [
                    'label' => 'Sent',
                    'field' => 'created_at',
                    'format' => 'date',
                ],
            ],
            'width' => 6,
            'show_view_all' => true,
            'view_all_link' => '/hr/invitations',
        ],
        3 => [
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
        4 => [
            'type' => 'action_card',
            'title' => 'Onboard New Hire',
            'size' => 'col-12',
            'icon' => 'fas fa-magic',
            'description' => 'Walk through the employee onboarding wizard',
            'actions' => [
                0 => [
                    'label' => 'Start',
                    'url' => '/hr/employee-onboarding',
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
