<?php

return [
    'context_groups' => [
        'leave' => [
            'label' => 'Leave',
            'icon' => 'fas fa-user-check',
            'order' => 1000,
            'route' => NULL,
            'url' => 'leave/dashboard-leave-overview',
        ],
    ],
    'contexts' => [
        'leave' => [
            [
                'key' => 'leave_overview',
                'label' => 'Overview',
                'icon' => 'fas fa-chart-bar',
                'route' => '/leave/dashboard-leave-overview',
                'permission' => 'view_leave_overview',
                'order' => 1,
                'page_title' => NULL,
            ],
            [
                'key' => 'leave_request',
                'label' => 'Leave Requests',
                'icon' => 'fas fa-calendar-alt',
                'route' => '/leave/leave-requests',
                'permission' => 'view_leave_request',
                'order' => 2,
                'page_title' => NULL,
            ],
            [
                'key' => 'leave_type',
                'label' => 'Leave Types',
                'icon' => 'fas fa-tags',
                'route' => '/leave/leave-types',
                'permission' => 'view_leave_type',
                'order' => 3,
                'page_title' => NULL,
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
            [
                'key' => 'leave_approver',
                'label' => 'Approvers',
                'icon' => 'fas fa-user-check',
                'route' => '/leave/leave-approvers',
                'permission' => 'view_leave_approver',
                'order' => 5,
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