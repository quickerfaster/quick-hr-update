<?php

/**
 * Attendance Module — Quick Actions Configuration
 *
 * Registers attendance management actions for the command palette.
 * These actions appear when the user presses Cmd+K / Ctrl+K.
 */
return [
    'quick_actions' => [
        [
            'id'          => 'attendance.view_dashboard',
            'label'       => 'View Attendance Dashboard',
            'description' => 'Go to the attendance dashboard overview',
            'icon'        => 'fas fa-tachometer-alt',
            'action'      => 'navigate',
            'module'      => 'attendance',
            'keywords'    => ['dashboard', 'home', 'overview', 'attendance', 'time'],
            'category'    => 'Dashboard',
            'route'       => '/attendance/dashboard-time-overview',
            'roles'       => ['super_admin', 'company_admin'],
        ],
        [
            'id'          => 'attendance.clock_in_out',
            'label'       => 'Clock In/Out',
            'description' => 'Record clock in or clock out event',
            'icon'        => 'fas fa-clock',
            'action'      => 'navigate',
            'module'      => 'attendance',
            'keywords'    => ['clock', 'in', 'out', 'punch', 'time', 'check', 'attendance'],
            'category'    => 'Time',
            'route'       => '/attendance/clock-events',
            'permission'  => 'view_clock_event',
            'roles'       => ['super_admin', 'company_admin'],
        ],
        [
            'id'          => 'attendance.create_shift',
            'label'       => 'Create Shift',
            'description' => 'Create a new work shift schedule',
            'icon'        => 'fas fa-calendar-day',
            'action'      => 'navigate',
            'module'      => 'attendance',
            'keywords'    => ['shift', 'create', 'add', 'new', 'schedule', 'roster', 'timetable'],
            'category'    => 'Scheduling',
            'route'       => '/attendance/shifts',
            'permission'  => 'view_shift',
            'roles'       => ['super_admin', 'company_admin'],
        ],
        [
            'id'          => 'attendance.add_work_pattern',
            'label'       => 'Add Work Pattern',
            'description' => 'Create a new work pattern for employees',
            'icon'        => 'fas fa-calendar-week',
            'action'      => 'navigate',
            'module'      => 'attendance',
            'keywords'    => ['work', 'pattern', 'add', 'create', 'new', 'schedule', 'weekly'],
            'category'    => 'Scheduling',
            'route'       => '/attendance/work-patterns',
            'permission'  => 'view_work_pattern',
            'roles'       => ['super_admin', 'company_admin'],
        ],
        [
            'id'          => 'attendance.policies',
            'label'       => 'Attendance Policies',
            'description' => 'View and manage attendance policies',
            'icon'        => 'fas fa-gavel',
            'action'      => 'navigate',
            'module'      => 'attendance',
            'keywords'    => ['policies', 'attendance', 'rules', 'policy', 'compliance', 'regulation'],
            'category'    => 'Policies',
            'route'       => '/attendance/attendance-policies',
            'permission'  => 'view_attendance_policy',
            'roles'       => ['super_admin', 'company_admin'],
        ],
    ],
];