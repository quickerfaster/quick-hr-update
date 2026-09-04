<?php

/**
 * Team Calendar Dashboard
 *
 * Shows approved leave requests for the team/company in a calendar-like
 * list view, grouped by date. Uses the library's existing dashboard +
 * list widget + team_whos_out widget patterns.
 *
 * Config key: hr.dashboards.dashboard_team_calendar
 * Route: /hr/team-calendar
 */

return [
    'title'       => 'Team Calendar',
    'description' => 'See who\'s out of office — approved leave across your team and company.',

    'widgets'     => [

        /*
         * ─── Row 1: Stat Cards ───────────────────────────────────
         * Quick at-a-glance metrics for team availability.
         */
        [
            'type'         => 'stat',
            'title'        => 'People Out Today',
            'size'         => 'col-12',
            'model'        => 'App\\Modules\\Leave\\Models\\LeaveRequest',
            'icon'         => 'fas fa-user-slash',
            'aggregate'    => 'count',
            'conditions'   => [
                ['status', '=', 'Approved'],
                ['start_date', '<=', 'today'],
                ['end_date', '>=', 'today'],
            ],
            'width'        => 3,
        ],
        [
            'type'         => 'stat',
            'title'        => 'People Out This Week',
            'size'         => 'col-12',
            'model'        => 'App\\Modules\\Leave\\Models\\LeaveRequest',
            'icon'         => 'fas fa-users-slash',
            'aggregate'    => 'count',
            'conditions'   => [
                ['status', '=', 'Approved'],
                ['start_date', '<=', 'sunday this week'],
                ['end_date', '>=', 'monday this week'],
            ],
            'width'        => 3,
        ],
        [
            'type'         => 'stat',
            'title'        => 'People Out This Month',
            'size'         => 'col-12',
            'model'        => 'App\\Modules\\Leave\\Models\\LeaveRequest',
            'icon'         => 'fas fa-calendar-times',
            'aggregate'    => 'count',
            'conditions'   => [
                ['status', '=', 'Approved'],
                ['start_date', '<=', 'last day of this month'],
                ['end_date', '>=', 'first day of this month'],
            ],
            'width'        => 3,
        ],
        [
            'type'         => 'stat',
            'title'        => 'Pending Requests',
            'size'         => 'col-12',
            'model'        => 'App\\Modules\\Leave\\Models\\LeaveRequest',
            'icon'         => 'fas fa-hourglass-half',
            'aggregate'    => 'count',
            'conditions'   => [
                ['status', '=', 'Pending'],
            ],
            'width'        => 3,
        ],

        /*
         * ─── Row 2: Team Who's Out Today ─────────────────────────
         * Reuses the existing team_whos_out widget with model-based
         * querying. Shows employee name, leave type, date range, and
         * return date for approved leaves spanning today.
         */
        [
            'type'               => 'team_whos_out',
            'title'              => 'Out of Office Today',
            'icon'               => 'fas fa-users-slash',
            'color'              => 'warning',
            'date_range'         => 'today',
            'limit'              => 20,
            'width'              => 6,
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

        /*
         * ─── Row 2 (right): Upcoming Leave (List) ────────────────
         * A list widget showing approved leave requests starting
         * this month, ordered by start date. Each row shows the
         * employee name, leave type, dates, and duration.
         */
        [
            'type'          => 'list',
            'title'         => 'Upcoming Leave This Month',
            'size'          => 'col-12',
            'model'         => 'App\\Modules\\Leave\\Models\\LeaveRequest',
            'icon'          => 'fas fa-calendar-week',
            'description'   => 'Approved leave starting this month',
            'limit'         => 10,
            'sort'          => [
                0 => 'start_date',
                1 => 'asc',
            ],
            'conditions'    => [
                ['status', '=', 'Approved'],
                ['start_date', '>=', 'first day of this month'],
                ['start_date', '<=', 'last day of this month'],
            ],
            'columns'       => [
                0 => [
                    'label' => 'Employee',
                    'field' => 'employee.first_name',
                ],
                1 => [
                    'label' => 'Leave Type',
                    'field' => 'leaveType.name',
                ],
                2 => [
                    'label' => 'Start',
                    'field' => 'start_date',
                    'format' => 'date',
                ],
                3 => [
                    'label' => 'End',
                    'field' => 'end_date',
                    'format' => 'date',
                ],
                4 => [
                    'label' => 'Days',
                    'field' => 'workdays_count',
                ],
            ],
            'width'         => 6,
            'show_view_all' => true,
            'view_all_link' => '/leave/leave-requests?filter[status]=Approved&filter[start_date][>=]=first day of this month&filter[start_date][<=]=last day of this month',
        ],

        /*
         * ─── Row 3: Currently Out (List) ─────────────────────────
         * Shows all employees currently on leave (start_date <= today
         * AND end_date >= today), ordered by return date.
         */
        [
            'type'          => 'list',
            'title'         => 'Currently Out of Office',
            'size'          => 'col-12',
            'model'         => 'App\\Modules\\Leave\\Models\\LeaveRequest',
            'icon'          => 'fas fa-sign-out-alt',
            'description'   => 'Employees currently on approved leave',
            'limit'         => 10,
            'sort'          => [
                0 => 'end_date',
                1 => 'asc',
            ],
            'conditions'    => [
                ['status', '=', 'Approved'],
                ['start_date', '<=', 'today'],
                ['end_date', '>=', 'today'],
            ],
            'columns'       => [
                0 => [
                    'label' => 'Employee',
                    'field' => 'employee.first_name',
                ],
                1 => [
                    'label' => 'Leave Type',
                    'field' => 'leaveType.name',
                ],
                2 => [
                    'label' => 'Since',
                    'field' => 'start_date',
                    'format' => 'date',
                ],
                3 => [
                    'label' => 'Returns',
                    'field' => 'end_date',
                    'format' => 'date',
                ],
                4 => [
                    'label' => 'Days Left',
                    'field' => 'workdays_count',
                ],
            ],
            'width'         => 12,
            'show_view_all' => true,
            'view_all_link' => '/leave/leave-requests?filter[status]=Approved&filter[start_date][<=]=today&filter[end_date][>=]=today',
        ],
    ],

    'roles'       => ['employee', 'manager', 'admin', 'hr_manager'],
    'layout'      => [
        'columns' => 12,
        'gutter'  => 3,
    ],
];