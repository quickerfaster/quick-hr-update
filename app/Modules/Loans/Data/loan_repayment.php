<?php

/**
 * Data Configuration: Loans — Loan Repayment
 *
 * Drives the DataTable for admin repayment management.
 *
 * Config Key: loans.loan_repayment
 */

return [
    'model' => \App\Modules\Loans\Models\LoanRepayment::class,

    'label' => 'Loan Repayment',
    'label_plural' => 'Loan Repayments',

    'fieldDefinitions' => [
        'loan_id' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'livewire-searchable-select',
            'label' => 'Loan',
            'validation' => 'required|exists:loans,id',
            'filterable' => true,
            'searchable' => true,
            'relationship' => [
                'model' => \App\Modules\Loans\Models\Loan::class,
                'type' => 'belongsTo',
                'display_field' => 'loan_number',
                'dynamic_property' => 'loan',
                'foreign_key' => 'loan_id',
                'inlineAdd' => false,
            ],
            'options' => [
                'model' => \App\Modules\Loans\Models\Loan::class,
                'column' => 'loan_number',
            ],
        ],
        'amount' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'currency',
            'label' => 'Amount',
            'validation' => 'required|numeric|min:0',
            'filterable' => true,
        ],
        'principal' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'currency',
            'label' => 'Principal',
            'validation' => 'nullable|numeric|min:0',
        ],
        'interest' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'currency',
            'label' => 'Interest',
            'validation' => 'nullable|numeric|min:0',
        ],
        'due_date' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'datepicker',
            'label' => 'Due Date',
            'validation' => 'required|date',
            'filterable' => true,
        ],
        'paid_date' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'datepicker',
            'label' => 'Paid Date',
            'validation' => 'nullable|date',
            'filterable' => true,
        ],
        'status' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'select',
            'label' => 'Status',
            'validation' => 'required|string',
            'options' => [
                'pending' => 'Pending',
                'paid' => 'Paid',
                'overdue' => 'Overdue',
                'waived' => 'Waived',
            ],
            'filterable' => true,
        ],
        'payment_method' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'select',
            'label' => 'Payment Method',
            'validation' => 'nullable|string',
            'options' => [
                'payroll_deduction' => 'Payroll Deduction',
                'manual' => 'Manual',
                'bank_transfer' => 'Bank Transfer',
            ],
            'filterable' => true,
        ],
        'reference' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Reference',
            'validation' => 'nullable|string|max:255',
        ],
        'payroll_run_id' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Payroll Run ID',
            'validation' => 'nullable|integer',
        ],
    ],

    'defaultSort' => ['column' => 'due_date', 'direction' => 'asc'],

    'controls' => [
        'addButton' => false,
        'files' => [
            'export' => ['csv', 'pdf'],
            'print' => true,
        ],
        'perPage' => [10, 25, 50, 100],
        'search' => true,
        'showHideColumns' => true,
        'filterColumns' => true,
        'softDelete' => false,
        'restore' => false,
        'forceDelete' => false,
        'trashView' => false,
        'bulkActions' => [
            'export' => ['csv', 'pdf'],
        ],
    ],

    'switchViews' => [
        'default' => 'table',
        'table' => ['enabled' => true],
        'list' => [
            'enabled' => true,
            'titleFields' => ['loan_id'],
            'subtitleFields' => ['amount', 'due_date'],
            'badgeField' => 'status',
            'badgeColors' => [
                'pending' => 'warning',
                'paid' => 'success',
                'overdue' => 'danger',
                'waived' => 'secondary',
            ],
        ],
    ],

    'fieldGroups' => [
        'repayment_details' => [
            'title' => 'Repayment Details',
            'groupType' => 'hr',
            'icon' => 'fas fa-calendar-check',
            'fields' => [
                '0' => 'loan_id',
                '1' => 'amount',
                '2' => 'principal',
                '3' => 'interest',
                '4' => 'due_date',
                '5' => 'status',
            ],
        ],
        'payment_info' => [
            'title' => 'Payment Information',
            'groupType' => 'hr',
            'icon' => 'fas fa-credit-card',
            'fields' => [
                '0' => 'paid_date',
                '1' => 'payment_method',
                '2' => 'reference',
                '3' => 'payroll_run_id',
            ],
        ],
    ],

    'moreActions' => [
        '0' => [
            'title' => 'Mark as Paid',
            'icon' => 'fas fa-check-circle',
            'event' => 'markRepaymentPaid',
            'permission' => 'edit_loan',
            'condition' => ['status' => 'pending'],
        ],
        '1' => [
            'title' => 'Mark as Paid',
            'icon' => 'fas fa-check-circle',
            'event' => 'markRepaymentPaid',
            'permission' => 'edit_loan',
            'condition' => ['status' => 'overdue'],
        ],
        '2' => [
            'title' => 'Waive',
            'icon' => 'fas fa-ban',
            'event' => 'waiveRepayment',
            'permission' => 'edit_loan',
            'condition' => ['status' => 'pending'],
        ],
    ],

    'simpleActions' => [
        '0' => 'show',
        '1' => 'edit',
    ],

    'hiddenFields' => [
        'onTable' => [
            '0' => 'created_at',
            '1' => 'updated_at',
        ],
        'onNewForm' => [
            '0' => 'created_at',
            '1' => 'updated_at',
        ],
        'onEditForm' => [
            '0' => 'updated_at',
        ],
        'onQuery' => [],
    ],

    'isTransaction' => false,
    'crudType' => 'pages',
    'dataTableComponent' => 'qf.loans.repayment-data-table',
    'includeControllers' => false,

    'tableDefaultFields' => [
        '0' => 'loan_id',
        '1' => 'amount',
        '2' => 'due_date',
        '3' => 'status',
        '4' => 'paid_date',
        '5' => 'payment_method',
    ],
];
