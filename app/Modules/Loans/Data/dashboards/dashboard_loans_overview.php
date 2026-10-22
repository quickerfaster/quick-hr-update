<?php

/**
 * Dashboard Widget: Loans Overview (Admin)
 *
 * Displays loan portfolio summary on the admin loans dashboard.
 * Uses the library's standard declarative widget schema (stat, list,
 * trend, chart, progress) — matching the HR People Overview dashboard.
 *
 * Config Key: loans_dashboard_loans_overview
 */

return [
    'title' => 'Loans Overview',
    'description' => 'Loan portfolio health, pending approvals, and recent activity at a glance',
    'widgets' => [
        [
            'type' => 'stat',
            'title' => 'Active Loans',
            'size' => 'col-12',
            'model' => \App\Modules\Loans\Models\Loan::class,
            'icon' => 'fas fa-play-circle',
            'color' => 'success',
            'aggregate' => 'count',
            'conditions' => [
                ['status', '=', 'active'],
            ],
            'width' => 3,
        ],
        [
            'type' => 'stat',
            'title' => 'Pending Approval',
            'size' => 'col-12',
            'model' => \App\Modules\Loans\Models\Loan::class,
            'icon' => 'fas fa-clock',
            'color' => 'warning',
            'aggregate' => 'count',
            'conditions' => [
                ['status', '=', 'pending_approval'],
            ],
            'width' => 3,
        ],
        [
            'type' => 'stat',
            'title' => 'Total Outstanding',
            'size' => 'col-12',
            'model' => \App\Modules\Loans\Models\Loan::class,
            'icon' => 'fas fa-money-bill-wave',
            'color' => 'danger',
            'aggregate' => 'sum',
            'field' => 'balance',
            'conditions' => [
                ['status', '=', 'active'],
            ],
            'format' => 'currency',
            'width' => 3,
        ],
        [
            'type' => 'stat',
            'title' => 'Overdue Repayments',
            'size' => 'col-12',
            'model' => \App\Modules\Loans\Models\LoanRepayment::class,
            'icon' => 'fas fa-exclamation-triangle',
            'color' => 'danger',
            'aggregate' => 'count',
            'conditions' => [
                ['status', '=', 'overdue'],
            ],
            'width' => 3,
        ],
        [
            'type' => 'list',
            'title' => 'Recent Loan Requests',
            'size' => 'col-12',
            'model' => \App\Modules\Loans\Models\Loan::class,
            'icon' => 'fas fa-list-alt',
            'description' => 'Latest 5 loan requests',
            'limit' => 5,
            'sort' => ['created_at', 'desc'],
            'columns' => [
                [
                    'label' => 'Loan Number',
                    'field' => 'loan_number',
                    'format' => 'text',
                ],
                [
                    'label' => 'Employee',
                    'field' => 'employee.full_name',
                    'format' => 'text',
                ],
                [
                    'label' => 'Amount',
                    'field' => 'amount',
                    'format' => 'currency',
                ],
                [
                    'label' => 'Status',
                    'field' => 'status',
                    'format' => 'text',
                ],
                [
                    'label' => 'Requested',
                    'field' => 'request_date',
                    'format' => 'date',
                ],
            ],
            'width' => 6,
            'show_view_all' => true,
            'view_all_link' => '/hr/loans',
        ],
        [
            'type' => 'trend',
            'title' => 'Loan Requests Trend (Last 6 Months)',
            'size' => 'col-12',
            'model' => \App\Modules\Loans\Models\Loan::class,
            'group_by' => 'month',
            'icon' => 'fas fa-chart-line',
            'description' => 'Monthly loan request count',
            'aggregate' => 'count',
            'date_field' => 'request_date',
            'period' => 6,
            'width' => 6,
        ],
        [
            'type' => 'chart',
            'title' => 'Loans by Type',
            'size' => 'col-12',
            'model' => \App\Modules\Loans\Models\Loan::class,
            'group_by' => 'loan_type_id',
            'chart_type' => 'bar',
            'description' => 'Current loan distribution by type',
            'aggregate' => 'count',
            'width' => 6,
        ],
        [
            'type' => 'progress',
            'title' => 'Repayment Progress',
            'size' => 'col-12',
            'model' => \App\Modules\Loans\Models\Loan::class,
            'icon' => 'fas fa-percentage',
            'description' => 'Total repaid vs total repayable',
            'aggregate' => 'sum',
            'field' => 'paid_amount',
            'target_model' => \App\Modules\Loans\Models\Loan::class,
            'target_aggregate' => 'sum',
            'target_field' => 'total_repayable',
            'width' => 4,
        ],
    ],
];
