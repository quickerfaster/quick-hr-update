<?php

return [
    'context_groups' => [
        'requests' => [
            'label' => 'Requests',
            'icon' => 'fas fa-calendar-alt',
            'order' => 999,
            'roles' => ['*'],
            'route' => NULL,
            'url' => 'leave/dashboard-requests-overview',
        ],
        'configuration' => [
            'label' => 'Configuration',
            'icon' => 'fas fa-cogs',
            'order' => 1000,
            'roles' => ['*'],
            'route' => NULL,
            'url' => 'leave/dashboard-configuration-overview',
        ],
    ],
    'contexts' => [
        'requests' => [
            [
                'key' => 'requests_overview',
                'label' => 'Overview',
                'icon' => 'fas fa-chart-bar',
                'route' => '/leave/dashboard-requests-overview',
                'permission' => 'view_requests_overview',
                'order' => 1,
                'page_title' => NULL,
            ],
            [
                'key' => 'leave_request',
                'label' => 'Leave Requests',
                'icon' => 'fas fa-calendar-alt',
                'route' => '/leave/leave-hub?tab=all-requests',
                'permission' => 'view_leave_request',
                'order' => 2,
                'page_title' => NULL,
            ],
            [
                'key' => 'leave_approvals',
                'label' => 'Approvals',
                'icon' => 'fas fa-clipboard-check',
                'route' => '/leave/approvals',
                'permission' => 'view_leave_request',
                'order' => 3,
                'page_title' => NULL,
                'modelName' => 'approvals',
            ],
            [
                'key' => 'leave_balance',
                'label' => 'Leave Balances',
                'icon' => 'fas fa-balance-scale',
                'route' => '/leave/leave-balances',
                'permission' => 'view_leave_balance',
                'order' => 4,
                'page_title' => NULL,
            ],
        ],
        'configuration' => [
            [
                'key' => 'configuration_overview',
                'label' => 'Overview',
                'icon' => 'fas fa-chart-bar',
                'route' => '/leave/dashboard-configuration-overview',
                'permission' => 'view_configuration_overview',
                'order' => 1,
                'page_title' => NULL,
            ],
            [
                'key' => 'leave_type',
                'label' => 'Leave Types',
                'icon' => 'fas fa-tags',
                'route' => '/leave/leave-types',
                'permission' => 'view_leave_type',
                'order' => 2,
                'page_title' => NULL,
            ],
        ],
    ],
    'layout' => [
        'top_bar' => [
            'enabled' => true,
        ],
        'context_menu' => [
            'type' => 'sidebar',
            'position' => 'left',
            'allow_switch' => true,
            'default_type' => 'sidebar',
        ],
        'sidebar' => [
            'initial_state' => 'full',
        ],
        'bottom_bar' => [
            'enabled' => true,
        ],
        'breadcrumb' => [
            'enabled' => true,
        ],
        'title' => [
            'enabled' => true,
        ],
    ],
    'shared_items' => [
        'header' => [],
        'footer' => [],
    ],
    'shared_top_items' => [
        'left' => [],
        'right' => [],
    ],
];
