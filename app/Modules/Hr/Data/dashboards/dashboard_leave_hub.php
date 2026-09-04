<?php

/**
 * Leave Hub Overview Dashboard — Employee Self-Service
 *
 * The overview tab of the Leave Hub page. Provides a leave-focused
 * snapshot for the authenticated employee: balance summary, pending
 * approvals, upcoming time off, recent requests, and quick actions.
 *
 * Placeholders (resolved via Dashboard $parameters):
 *   {{ employee_number }} — the employee's employee_number (used in conditions)
 */

return [
    'title'       => 'Leave Overview',
    'description' => 'Your leave balance, upcoming time off, and recent requests at a glance.',
    'widgets'     => [

        /*
         * ─── Row 1: Stat Cards ───────────────────────────────────
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
            'type'         => 'stat',
            'title'        => 'Approved This Year',
            'size'         => 'col-12',
            'model'        => 'App\\Modules\\Hr\\Models\\LeaveRequest',
            'icon'         => 'fas fa-check-circle',
            'aggregate'    => 'count',
            'conditions'   => [
                ['employee_id', '=', '{{ employee_number }}'],
                ['status', '=', 'Approved'],
                ['start_date', '>=', 'first day of january this year'],
                ['start_date', '<=', 'last day of december this year'],
            ],
            'width'        => 3,
        ],
        [
            'type'         => 'stat',
            'title'        => 'Total Requests',
            'size'         => 'col-12',
            'model'        => 'App\\Modules\\Hr\\Models\\LeaveRequest',
            'icon'         => 'fas fa-calendar-alt',
            'aggregate'    => 'count',
            'conditions'   => [
                ['employee_id', '=', '{{ employee_number }}'],
            ],
            'width'        => 3,
        ],

        /*
         * ─── Row 2: Upcoming Time Off ────────────────────────────
         */
        [
            'type'        => 'list',
            'title'       => 'Upcoming Time Off',
            'size'        => 'col-12',
            'model'       => 'App\\Modules\\Hr\\Models\\LeaveRequest',
            'icon'        => 'fas fa-calendar-week',
            'description' => 'Future approved leave',
            'limit'       => 5,
            'sort'        => [
                'start_date',
                'asc',
            ],
            'conditions'  => [
                ['employee_id', '=', '{{ employee_number }}'],
                ['status', '=', 'Approved'],
                ['start_date', '>=', 'today'],
            ],
            'columns'     => [
                [
                    'label'  => 'Type',
                    'field'  => 'leaveType.name',
                ],
                [
                    'label'  => 'Start',
                    'field'  => 'start_date',
                    'format' => 'date',
                ],
                [
                    'label'  => 'End',
                    'field'  => 'end_date',
                    'format' => 'date',
                ],
                [
                    'label'  => 'Days',
                    'field'  => 'workdays_count',
                ],
            ],
            'width'            => 6,
            'show_view_all'    => true,
            'view_all_link'    => '/hr/my-leave-requests',
            'view_all_link_target' => '_self',
        ],

        /*
         * ─── Row 2: Recent Leave Requests ────────────────────────
         */
        [
            'type'        => 'list',
            'title'       => 'Recent Leave Requests',
            'size'        => 'col-12',
            'model'       => 'App\\Modules\\Hr\\Models\\LeaveRequest',
            'icon'        => 'fas fa-list-alt',
            'description' => 'Last 5 requests',
            'limit'       => 5,
            'sort'        => [
                'created_at',
                'desc',
            ],
            'conditions'  => [
                ['employee_id', '=', '{{ employee_number }}'],
            ],
            'columns'     => [
                [
                    'label'  => 'Type',
                    'field'  => 'leaveType.name',
                ],
                [
                    'label'  => 'Start',
                    'field'  => 'start_date',
                    'format' => 'date',
                ],
                [
                    'label'  => 'End',
                    'field'  => 'end_date',
                    'format' => 'date',
                ],
                [
                    'label'  => 'Status',
                    'field'  => 'status',
                ],
            ],
            'width'            => 6,
            'show_view_all'    => true,
            'view_all_link'    => '/hr/my-leave-requests',
            'view_all_link_target' => '_self',
        ],

        /*
         * ─── Row 3: Quick Action ─────────────────────────────────
         */
        [
            'type'        => 'action_card',
            'title'       => 'Apply for Leave',
            'icon'        => 'fas fa-calendar-plus',
            'color'       => 'primary',
            'description' => 'Submit a new leave request',
            'actions'     => [
                [
                    'label'  => 'Apply Now',
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
            'width'       => 12,
        ],

    ],
];