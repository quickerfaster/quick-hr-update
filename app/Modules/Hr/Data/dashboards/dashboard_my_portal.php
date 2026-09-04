<?php

/**
 * My Portal Dashboard — Employee Self-Service
 *
 * The landing page for employees and managers. Aggregates personal
 * stats, quick actions, recent activity, and key HR information
 * scoped to the authenticated user's employee record.
 *
 * Phase 1: Foundation — profile header, stat cards, action cards,
 * activity feed, and quick actions widget.
 * Phase 2+: Real data sources, interactive clock in/out, team widget.
 */

return [
    'title'       => 'My Portal',
    'description' => 'Your personal HR dashboard — leave, attendance, payslips, and quick actions at a glance.',
    'widgets'     => [
        /*
         * ─── Row 1: Profile Header ───────────────────────────────
         * Shows the employee's photo, full name, department, and
         * employee ID. The ProfileHeaderWidgetProcessor expects
         * photo_url, full_name, record_number, and optional fields.
         *
         * TODO Phase 2: Resolve employee from auth()->user() and
         * populate photo_url, full_name, record_number, and fields
         * dynamically via the Dashboard component's $parameters.
         */
        [
            'type'          => 'profile_header',
            'title'         => 'Welcome back',
            'photo_url'     => '{{ employee_photo_url }}',
            'full_name'     => '{{ employee_full_name }}',
            'record_number' => '{{ employee_number }}',
            'color'         => 'primary',
            'fields'        => [
                [
                    'label' => 'Department',
                    'value' => '{{ employee_department }}',
                    'icon'  => 'fas fa-sitemap',
                ],
                [
                    'label' => 'Position',
                    'value' => '{{ employee_position }}',
                    'icon'  => 'fas fa-briefcase',
                ],
                [
                    'label' => 'Manager',
                    'value' => '{{ employee_manager }}',
                    'icon'  => 'fas fa-user-tie',
                ],
                [
                    'label' => 'Hire Date',
                    'value' => '{{ employee_hire_date }}',
                    'icon'  => 'fas fa-calendar-check',
                ],
            ],
            'width'         => 12,
        ],

        /*
         * ─── Row 2: Stat Cards ───────────────────────────────────
         * Four key metrics for the employee. Phase 1 uses placeholder
         * values or simple queries. Phase 2 will wire up real data.
         */
        [
            'type'         => 'stat',
            'title'        => 'Leave Balance',
            'size'         => 'col-12',
            'model'        => 'App\\Modules\\Hr\\Models\\LeaveBalance',
            'icon'         => 'fas fa-umbrella-beach',
            'aggregate'    => 'sum',
            'field'        => 'balance',
            'conditions'   => [
                ['employee_id', '=', '{{ employee_number }}'],
            ],
            'width'        => 3,
        ],
        [
            'type'         => 'stat',
            'title'        => 'Hours This Week',
            'size'         => 'col-12',
            'model'        => 'App\\Modules\\Hr\\Models\\Attendance',
            'icon'         => 'fas fa-clock',
            'aggregate'    => 'sum',
            'field'        => 'net_hours',
            'conditions'   => [
                ['employee.employee_number', '=', '{{ employee_number }}'],
                ['date', '>=', 'monday this week'],
                ['date', '<=', 'sunday this week'],
            ],
            'width'        => 3,
        ],
        [
            'type'         => 'stat',
            'title'        => 'Pending Approvals',
            'size'         => 'col-12',
            'model'        => 'App\\Modules\\Hr\\Models\\LeaveRequest',
            'icon'         => 'fas fa-hourglass-half',
            'aggregate'    => 'count',
            'conditions'   => [
                ['employee_id', '=', '{{ employee_number }}'],
                ['status', '=', 'Pending'],
            ],
            'width'        => 3,
        ],
        [
            // TODO Phase 2: Replace with real holiday query from Holiday module
            'type'         => 'stat',
            'title'        => 'Upcoming Holidays',
            'size'         => 'col-12',
            'icon'         => 'fas fa-calendar-star',
            'custom_value' => '—',
            'width'        => 3,
        ],

        /*
         * ─── Row 3: Quick-Action Cards ───────────────────────────
         * Four prominent action cards for the most common employee
         * self-service tasks.
         */
        [
            'type'        => 'action_card',
            'title'       => 'Request Leave',
            'icon'        => 'fas fa-calendar-plus',
            'color'       => 'primary',
            'description' => 'Submit a new leave request',
            'actions'     => [
                [
                    'label'  => 'Request',
                    'event'  => 'openDrawer',
                    'params' => [
                        'component' => 'qf.wizard',
                        'params'    => [
                            'configKey'  => 'leave.wizards.employee_self_service',
                            'presetData' => [
                                'employee_id' => '{{ employee_id }}',
                            ],
                        ],
                        'title'     => 'Request Leave',
                    ],
                    'style'  => 'primary',
                ],
            ],
            'width'       => 3,
        ],
        // Clock In/Out is now handled by the qf.clock-in-out Livewire
        // component rendered inline in the my-portal.blade.php view.
        // This placeholder keeps the grid layout consistent.
        [
            'type'        => 'action_card',
            'title'       => 'Clock In / Out',
            'icon'        => 'fas fa-user-clock',
            'color'       => 'success',
            'description' => 'Record your attendance',
            'actions'     => [
                [
                    'label'  => 'Open Clock In/Out',
                    'event'  => 'openDrawer',
                    'params' => [
                        'component' => 'qf.clock-in-out',
                        'params'    => [
                            'employee-id' => '{{ employee_number }}',
                        ],
                        'title'     => 'Clock In / Out',
                    ],
                    'style'  => 'success',
                ],
            ],
            'width'       => 3,
        ],
        [
            'type'        => 'action_card',
            'title'       => 'View Payslip',
            'icon'        => 'fas fa-receipt',
            'color'       => 'info',
            'description' => 'See your latest payslip',
            'actions'     => [
                [
                    'label'  => 'View',
                    'event'  => 'navigate',
                    'params' => [
                        'url' => '/payroll/my-payslips',
                    ],
                    'style'  => 'info',
                ],
            ],
            'width'       => 3,
        ],
        [
            'type'        => 'action_card',
            'title'       => 'Update My Info',
            'icon'        => 'fas fa-user-edit',
            'color'       => 'warning',
            'description' => 'Edit your personal details',
            'actions'     => [
                [
                    'label'  => 'Edit',
                    'event'  => 'navigate',
                    'params' => [
                        'url' => '/hr/my-account',
                    ],
                    'style'  => 'warning',
                ],
            ],
            'width'       => 3,
        ],

        /*
         * ─── Row 4: Quick Actions Widget ─────────────────────────
         * Top employee actions from the Cmd+K palette, rendered as
         * a dashboard widget. Actions are role-filtered by
         * ActionRegistry::authorizedFor() — employees only see
         * ESS actions (My Portal, Request Leave, Clock In/Out,
         * View Payslip, Update My Info, My Schedule, My Documents).
         *
         * NOTE: The activity_log widget was removed because it
         * showed unfiltered system-wide activity logs and linked
         * to /admin/activity-logs (admin-only). An ESS-scoped
         * "My Recent Activity" widget could be added in a future
         * phase using causer_id filtering.
         */
        [
            'type'  => 'quick_actions',
            'title' => 'Quick Actions',
            'icon'  => 'fas fa-bolt',
            'color' => 'warning',
            'limit' => 5,
            'width' => 12,
        ],

        /*
         * ─── Row 5: Team Who's Out ────────────────────────────────
         * Shows team members on leave today. Queries approved
         * LeaveRequests scoped to the current company (via
         * HasCompanyScope) and filters to those whose leave
         * spans today (start_date <= today AND end_date >= today).
         */
        [
            'type'               => 'team_whos_out',
            'title'              => 'Team Who\'s Out',
            'icon'               => 'fas fa-users-slash',
            'color'              => 'warning',
            'date_range'         => 'today',
            'limit'              => 10,
            'width'              => 12,
            'model'              => 'App\\Modules\\Leave\\Models\\LeaveRequest',
            'conditions'         => [
                ['status', '=', 'Approved'],
            ],
            'date_field'         => 'start_date',
            'end_date_field'     => 'end_date',
            'status_field'       => 'status',
            'approved_status'    => 'Approved',
            'employee_relation'  => 'employee',
            'leave_type_relation'=> 'leaveType',
        ],
    ],
    'roles'       => ['employee', 'manager'],
    'layout'      => [
        'columns' => 12,
        'gutter'  => 3,
    ],
];
