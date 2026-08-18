<?php

return [
    'context_groups' => [
        'time' => [
            'label' => 'Time',
            'icon' => 'fas fa-user-clock',
            'order' => 999,
            'route' => NULL,
            'url' => 'hr/dashboard-time-overview',
        ],
        'policies' => [
            'label' => 'Policies',
            'icon' => 'fas fa-gavel',
            'order' => 10000,
            'route' => NULL,
            'url' => 'hr/dashboard-policies-overview',
        ],
    ],
    'contexts' => [
        'time' => [
            [
                'key' => 'attendance_overview',
                'label' => 'Overview',
                'icon' => 'fas fa-chart-bar',
                'route' => '/hr/dashboard-time-overview',
                'permission' => 'view_attendance_overview',
                'order' => 1,
                'page_title' => NULL,
            ],
            [
                'key' => 'attendance',
                'label' => 'Attendance',
                'icon' => 'fas fa-user-clock',
                'route' => '/hr/attendances',
                'permission' => 'view_attendance',
                'order' => 2,
                'page_title' => NULL,
            ],
            [
                'key' => 'shift_schedule',
                'label' => 'Shift Schedules',
                'icon' => 'fas fa-calendar-check',
                'route' => '/hr/shift-schedules',
                'permission' => 'view_shift_schedule',
                'order' => 5,
                'page_title' => NULL,
            ],
            [
                'key' => 'shift',
                'label' => 'Shifts',
                'icon' => 'fas fa-calendar-day',
                'route' => '/hr/shifts',
                'permission' => 'view_shift',
                'order' => 6,
                'page_title' => NULL,
            ],
        ],
        'policies' => [
            [
                'key' => 'policy_overview',
                'label' => 'Overview',
                'icon' => 'fas fa-chart-bar',
                'route' => '/hr/dashboard-policies-overview',
                'permission' => 'view_policy_overview',
                'order' => 1,
                'page_title' => NULL,
            ],
            [
                'key' => 'attendance_policy',
                'label' => 'Attendance Policies',
                'icon' => 'fas fa-gavel',
                'route' => '/hr/attendance-policies',
                'permission' => 'view_attendance_policy',
                'order' => 2,
                'page_title' => NULL,
            ],
            [
                'key' => 'work_pattern',
                'label' => 'Work Patterns',
                'icon' => 'fas fa-calendar-week',
                'route' => '/hr/work-patterns',
                'permission' => 'view_work_pattern',
                'order' => 2,
                'page_title' => NULL,
            ],
            [
                'key' => 'policy_assignment',
                'label' => 'Policy Assignments',
                'icon' => 'fas fa-tasks',
                'route' => '/hr/policy-assignments',
                'permission' => 'view_policy_assignment',
                'order' => 999,
                'page_title' => NULL,
            ],
            [
                'key' => 'employee_work_pattern',
                'label' => 'Employee Work Patterns',
                'icon' => 'fas fa-user-tag',
                'route' => '/hr/employee-work-patterns',
                'permission' => 'view_employee_work_pattern',
                'order' => 999,
                'page_title' => NULL,
            ],
        ],
        'attendance_adjustment' => [
            [
                'key' => 'attendance_adjustment',
                'label' => 'Attendance Adjustments',
                'icon' => 'fas fa-edit',
                'route' => '/attendance-adjustments',
                'permission' => 'view_attendance_adjustment',
                'order' => 999,
                'page_title' => NULL,
            ],
        ],
        'clock_event' => [
            [
                'key' => 'clock_event',
                'label' => 'Clock Events',
                'icon' => 'fas fa-clock',
                'route' => '/clock-events',
                'permission' => 'view_clock_event',
                'order' => 999,
                'page_title' => NULL,
            ],
        ],
        'attendance_session' => [
            [
                'key' => 'attendance_session',
                'label' => 'Attendance Sessions',
                'icon' => 'fas fa-hourglass-half',
                'route' => '/attendance-sessions',
                'permission' => 'view_attendance_session',
                'order' => 999,
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