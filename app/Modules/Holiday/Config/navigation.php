<?php

return [
    'context_groups' => [
        'holidays' => [
            'label' => 'Holidays',
            'icon' => 'fas fa-calendar-alt',
            'order' => 10000,
            'roles' => ['*'],
            'route' => NULL,
            'url' => '/holiday/dashboard-holidays-overview',
        ],
    ],
    'contexts' => [
        'holidays' => [
            [
                'key' => 'holiday_overview',
                'label' => 'Overview',
                'icon' => 'fas fa-chart-bar',
                'route' => '/holiday/dashboard-holidays-overview',
                'permission' => 'view_holiday_overview',
                'order' => 1,
                'page_title' => NULL,
            ],
            [
                'key' => 'holiday_calendar',
                'label' => 'Holiday Calendars',
                'icon' => 'fas fa-calendar',
                'route' => '/holiday/holiday-calendars',
                'permission' => 'view_holiday_calendar',
                'order' => 10,
                'page_title' => NULL,
            ],
            [
                'key' => 'holiday',
                'label' => 'Holidays',
                'icon' => 'fas fa-umbrella-beach',
                'route' => '/holiday/holidays',
                'permission' => 'view_holiday',
                'order' => 20,
                'page_title' => NULL,
            ],
            [
                'key' => 'holiday_batch_creation',
                'label' => 'Batch Create',
                'icon' => 'fas fa-magic',
                'route' => '/holiday/holiday-batch-creation',
                'permission' => 'create_holiday',
                'order' => 30,
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
