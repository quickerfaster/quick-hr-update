<?php

/**
 * Leave Module — Quick Actions Configuration
 *
 * Registers leave management actions for the command palette.
 * These actions appear when the user presses Cmd+K / Ctrl+K.
 */
return [
    'quick_actions' => [
        [
            'id'          => 'leave.view_dashboard',
            'label'       => 'View Leave Dashboard',
            'description' => 'Go to the leave management dashboard overview',
            'icon'        => 'fas fa-tachometer-alt',
            'action'      => 'navigate',
            'module'      => 'leave',
            'keywords'    => ['dashboard', 'home', 'overview', 'leave', 'time off', 'vacation'],
            'category'    => 'Dashboard',
            'route'       => '/leave/dashboard-requests-overview',
            'roles'       => ['super_admin', 'company_admin'],
        ],
        [
            'id'          => 'leave.request_leave',
            'label'       => 'Request Leave',
            'description' => 'Submit a new leave request',
            'icon'        => 'fas fa-calendar-plus',
            'action'      => 'navigate',
            'module'      => 'leave',
            'keywords'    => ['leave', 'request', 'apply', 'new', 'time off', 'vacation', 'absence'],
            'category'    => 'Requests',
            'route'       => '/leave/leave-requests',
            'permission'  => 'view_leave_request',
            'roles'       => ['super_admin', 'company_admin'],
        ],
        [
            'id'          => 'leave.add_leave_type',
            'label'       => 'Add Leave Type',
            'description' => 'Create a new leave type for the organization',
            'icon'        => 'fas fa-tags',
            'action'      => 'navigate',
            'module'      => 'leave',
            'keywords'    => ['leave', 'type', 'add', 'create', 'new', 'category', 'annual', 'sick'],
            'category'    => 'Configuration',
            'route'       => '/leave/leave-types',
            'permission'  => 'view_leave_type',
            'roles'       => ['super_admin', 'company_admin'],
        ],
        [
            'id'          => 'leave.policies',
            'label'       => 'Leave Policies',
            'description' => 'View and manage leave policies and rules',
            'icon'        => 'fas fa-gavel',
            'action'      => 'navigate',
            'module'      => 'leave',
            'keywords'    => ['policies', 'leave', 'rules', 'policy', 'entitlement', 'accrual'],
            'category'    => 'Configuration',
            'route'       => '/leave/dashboard-configuration-overview',
            'permission'  => 'view_configuration_overview',
            'roles'       => ['super_admin', 'company_admin'],
        ],
    ],
];
