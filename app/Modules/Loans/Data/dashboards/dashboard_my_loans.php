<?php

/**
 * My Loans Overview Dashboard — Employee Self-Service
 *
 * The overview tab of the My Loans page. Provides a loan-focused
 * snapshot for the authenticated employee: active loans, outstanding
 * balance, next payment, and recent activity.
 *
 * Placeholders (resolved via Dashboard $parameters):
 *   {{ employee_id }} — the employee's ID (used in conditions)
 */

return [
    'title'       => 'My Loans Overview',
    'description' => 'Your active loans, outstanding balance, and recent activity at a glance.',
    'widgets'     => [

        /*
         * ─── Row 1: Stat Cards ───────────────────────────────────
         */
        [
            'type'         => 'stat',
            'title'        => 'Active Loans',
            'size'         => 'col-12',
            'model'        => 'App\\Modules\\Loans\\Models\\Loan',
            'icon'         => 'fas fa-play-circle',
            'aggregate'    => 'count',
            'conditions'   => [
                ['employee_id', '=', '{{ employee_id }}'],
                ['status', '=', 'active'],
            ],
            'width'        => 3,
        ],
        [
            'type'         => 'stat',
            'title'        => 'Pending Approval',
            'size'         => 'col-12',
            'model'        => 'App\\Modules\\Loans\\Models\\Loan',
            'icon'         => 'fas fa-hourglass-half',
            'aggregate'    => 'count',
            'conditions'   => [
                ['employee_id', '=', '{{ employee_id }}'],
                ['status', '=', 'pending_approval'],
            ],
            'width'        => 3,
        ],
        [
            'type'         => 'stat',
            'title'        => 'Outstanding Balance',
            'size'         => 'col-12',
            'model'        => 'App\\Modules\\Loans\\Models\\Loan',
            'icon'         => 'fas fa-money-bill-wave',
            'aggregate'    => 'sum',
            'field'        => 'balance',
            'conditions'   => [
                ['employee_id', '=', '{{ employee_id }}'],
                ['status', '=', 'active'],
            ],
            'format'       => 'currency',
            'width'        => 3,
        ],
        [
            'type'         => 'stat',
            'title'        => 'Completed Loans',
            'size'         => 'col-12',
            'model'        => 'App\\Modules\\Loans\\Models\\Loan',
            'icon'         => 'fas fa-check-circle',
            'aggregate'    => 'count',
            'conditions'   => [
                ['employee_id', '=', '{{ employee_id }}'],
                ['status', '=', 'completed'],
            ],
            'width'        => 3,
        ],

        /*
         * ─── Row 2: Recent Loan Requests ────────────────────────
         */
        [
            'type'        => 'list',
            'title'       => 'Recent Loan Requests',
            'size'        => 'col-12',
            'model'       => 'App\\Modules\\Loans\\Models\\Loan',
            'icon'        => 'fas fa-list-alt',
            'description' => 'Last 5 requests',
            'limit'       => 5,
            'sort'        => [
                'created_at',
                'desc',
            ],
            'conditions'  => [
                ['employee_id', '=', '{{ employee_id }}'],
            ],
            'columns'     => [
                [
                    'label'  => 'Loan #',
                    'field'  => 'loan_number',
                ],
                [
                    'label'  => 'Amount',
                    'field'  => 'amount',
                    'format' => 'currency',
                ],
                [
                    'label'  => 'Status',
                    'field'  => 'status',
                ],
                [
                    'label'  => 'Date',
                    'field'  => 'created_at',
                    'format' => 'date',
                ],
            ],
            'width'            => 6,
            'show_view_all'    => true,
            'view_all_link'    => '/hr/my-loans?tab=my-loans',
            'view_all_link_target' => '_self',
        ],

        /*
         * ─── Row 2: Active Loan Details ─────────────────────────
         */
        [
            'type'        => 'list',
            'title'       => 'Active Loans',
            'size'        => 'col-12',
            'model'       => 'App\\Modules\\Loans\\Models\\Loan',
            'icon'        => 'fas fa-hand-holding-usd',
            'description' => 'Currently active loans',
            'limit'       => 5,
            'sort'        => [
                'start_date',
                'desc',
            ],
            'conditions'  => [
                ['employee_id', '=', '{{ employee_id }}'],
                ['status', '=', 'active'],
            ],
            'columns'     => [
                [
                    'label'  => 'Loan #',
                    'field'  => 'loan_number',
                ],
                [
                    'label'  => 'Monthly EMI',
                    'field'  => 'monthly_installment',
                    'format' => 'currency',
                ],
                [
                    'label'  => 'Balance',
                    'field'  => 'balance',
                    'format' => 'currency',
                ],
                [
                    'label'  => 'End Date',
                    'field'  => 'expected_end_date',
                    'format' => 'date',
                ],
            ],
            'width'            => 6,
            'show_view_all'    => true,
            'view_all_link'    => '/hr/my-loans?tab=my-loans',
            'view_all_link_target' => '_self',
        ],

        /*
         * ─── Row 3: Quick Action ─────────────────────────────────
         */
        [
            'type'        => 'action_card',
            'title'       => 'Request a Loan',
            'icon'        => 'fas fa-hand-holding-usd',
            'color'       => 'primary',
            'description' => 'Submit a new loan request for approval',
            'actions'     => [
                [
                    'label'  => 'Request Now',
                    'event'  => 'openDrawer',
                    'params' => [
                        'component' => 'qf.loans.request-form',
                        'params'    => [
                            'employeeId'        => '{{ employee_id }}',
                            'availableLoanTypes' => [],
                        ],
                        'title'     => 'New Loan Request',
                    ],
                    'style'  => 'primary',
                ],
            ],
            'width'       => 12,
        ],

    ],
];
