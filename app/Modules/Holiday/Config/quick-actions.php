<?php

/**
 * Holiday Module — Quick Actions Configuration
 *
 * Registers holiday management actions for the command palette.
 * These actions appear when the user presses Cmd+K / Ctrl+K.
 */
return [
    'quick_actions' => [
        [
            'id'          => 'holiday.view_dashboard',
            'label'       => 'View Holiday Dashboard',
            'description' => 'Go to the holiday management dashboard overview',
            'icon'        => 'fas fa-tachometer-alt',
            'action'      => 'navigate',
            'module'      => 'holiday',
            'keywords'    => ['dashboard', 'home', 'overview', 'holiday', 'calendar'],
            'category'    => 'Dashboard',
            'route'       => '/holiday/dashboard-holidays-overview',
            'roles'       => ['super_admin', 'company_admin'],
        ],
        [
            'id'          => 'holiday.add_holiday',
            'label'       => 'Add Holiday',
            'description' => 'Add a new holiday to the calendar',
            'icon'        => 'fas fa-umbrella-beach',
            'action'      => 'navigate',
            'module'      => 'holiday',
            'keywords'    => ['holiday', 'add', 'create', 'new', 'public', 'event', 'day off'],
            'category'    => 'Holidays',
            'route'       => '/holiday/holidays',
            'permission'  => 'view_holiday',
            'roles'       => ['super_admin', 'company_admin'],
        ],
        [
            'id'          => 'holiday.add_calendar',
            'label'       => 'Add Calendar',
            'description' => 'Create a new holiday calendar',
            'icon'        => 'fas fa-calendar-plus',
            'action'      => 'navigate',
            'module'      => 'holiday',
            'keywords'    => ['calendar', 'add', 'create', 'new', 'holiday', 'schedule'],
            'category'    => 'Holidays',
            'route'       => '/holiday/holiday-calendars',
            'permission'  => 'view_holiday_calendar',
            'roles'       => ['super_admin', 'company_admin'],
        ],
        [
            'id'          => 'holiday.calendars',
            'label'       => 'Holiday Calendars',
            'description' => 'View and manage all holiday calendars',
            'icon'        => 'fas fa-calendar-alt',
            'action'      => 'navigate',
            'module'      => 'holiday',
            'keywords'    => ['calendars', 'holiday', 'manage', 'view', 'all', 'list'],
            'category'    => 'Holidays',
            'route'       => '/holiday/holiday-calendars',
            'permission'  => 'view_holiday_calendar',
            'roles'       => ['super_admin', 'company_admin'],
        ],
    ],
];