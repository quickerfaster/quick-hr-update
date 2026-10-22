<?php

/**
 * Data Configuration: Loans — Loan
 *
 * Drives the DataTable, DataTableForm, and DataTableDetail components
 * for the Loan entity in the Loans module.
 *
 * Config Key: loans.loan
 */

return [
    'model' => \App\Modules\Loans\Models\Loan::class,

    'label' => 'Loan',
    'label_plural' => 'Loans',

    'fieldDefinitions' => [
        'loan_number' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Loan Number',
            'validation' => 'required|string|max:50',
            'filterable' => true,
            'searchable' => true,
        ],
        'employee_id' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'livewire-searchable-select',
            'label' => 'Employee',
            'validation' => 'required|exists:employees,id',
            'filterable' => true,
            'searchable' => true,
            'relationship' => [
                'model' => \App\Modules\Hr\Models\Employee::class,
                'type' => 'belongsTo',
                'display_field' => 'full_name',
                'dynamic_property' => 'employee',
                'foreign_key' => 'employee_id',
                'inlineAdd' => false,
            ],
            'options' => [
                'model' => \App\Modules\Hr\Models\Employee::class,
                'column' => 'full_name',
                'hintField' => 'employee_number',
            ],
        ],
        'loan_type_id' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'select',
            'label' => 'Loan Type',
            'validation' => 'nullable|exists:loan_types,id',
            'filterable' => true,
            'relationship' => [
                'model' => \App\Modules\Loans\Models\LoanType::class,
                'display' => 'name',
                'value' => 'id',
                'dynamic_property' => 'loanType',
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
        'interest_rate' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Interest Rate (%)',
            'validation' => 'nullable|numeric|min:0|max:100',
            'filterable' => true,
        ],
        'repayment_months' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Repayment Months',
            'validation' => 'required|integer|min:1|max:60',
            'filterable' => true,
        ],
        'monthly_installment' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'currency',
            'label' => 'Monthly Installment',
            'validation' => 'nullable|numeric|min:0',
        ],
        'total_repayable' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'currency',
            'label' => 'Total Repayable',
            'validation' => 'nullable|numeric|min:0',
        ],
        'paid_amount' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'currency',
            'label' => 'Paid Amount',
            'validation' => 'nullable|numeric|min:0',
        ],
        'balance' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'currency',
            'label' => 'Balance',
            'validation' => 'nullable|numeric|min:0',
        ],
        'status' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'select',
            'label' => 'Status',
            'validation' => 'required|string',
            'options' => [
                'draft' => 'Draft',
                'pending_approval' => 'Pending Approval',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'active' => 'Active',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ],
            'filterable' => true,
        ],
        'reason' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'textarea',
            'label' => 'Reason',
            'validation' => 'nullable|string|max:1000',
        ],
        'notes' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'textarea',
            'label' => 'Admin Notes',
            'validation' => 'nullable|string',
        ],
        'request_date' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'datepicker',
            'label' => 'Request Date',
            'validation' => 'nullable|date',
            'filterable' => true,
        ],
        'approval_date' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'datepicker',
            'label' => 'Approval Date',
            'validation' => 'nullable|date',
            'filterable' => true,
        ],
        'start_date' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'datepicker',
            'label' => 'Start Date',
            'validation' => 'nullable|date',
            'filterable' => true,
        ],
        'expected_end_date' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'datepicker',
            'label' => 'Expected End Date',
            'validation' => 'nullable|date',
            'filterable' => true,
        ],
        'approved_by' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'select',
            'label' => 'Approved By',
            'validation' => 'nullable|exists:users,id',
            'relationship' => [
                'model' => \App\Models\User::class,
                'display' => 'name',
                'value' => 'id',
                'dynamic_property' => 'approvedBy',
            ],
        ],
    ],

    'defaultSort' => ['column' => 'created_at', 'direction' => 'desc'],

    'controls' => [
        'addButton' => true,
        'files' => [
            'export' => ['csv', 'pdf'],
            'print' => true,
        ],
        'perPage' => [10, 25, 50, 100],
        'search' => true,
        'showHideColumns' => true,
        'filterColumns' => true,
        'softDelete' => true,
        'restore' => true,
        'forceDelete' => true,
        'trashView' => true,
        'bulkActions' => [
            'export' => ['csv', 'pdf'],
            'delete' => true,
        ],
    ],

    'switchViews' => [
        'default' => 'table',
        'table' => ['enabled' => true],
        'list' => [
            'enabled' => true,
            'titleFields' => ['loan_number'],
            'subtitleFields' => ['employee_id', 'amount'],
            'badgeField' => 'status',
            'badgeColors' => [
                'draft' => 'secondary',
                'pending_approval' => 'warning',
                'approved' => 'info',
                'rejected' => 'danger',
                'active' => 'success',
                'completed' => 'primary',
                'cancelled' => 'dark',
            ],
        ],
    ],

    'fieldGroups' => [
        'loan_details' => [
            'title' => 'Loan Details',
            'groupType' => 'hr',
            'icon' => 'fas fa-hand-holding-usd',
            'fields' => [
                '0' => 'employee_id',
                '1' => 'loan_type_id',
                '2' => 'amount',
                '3' => 'interest_rate',
                '4' => 'repayment_months',
                '5' => 'reason',
            ],
        ],
        'loan_financials' => [
            'title' => 'Financial Information',
            'groupType' => 'hr',
            'icon' => 'fas fa-calculator',
            'fields' => [
                '0' => 'monthly_installment',
                '1' => 'total_repayable',
                '2' => 'paid_amount',
                '3' => 'balance',
            ],
        ],
        'loan_status' => [
            'title' => 'Status & Dates',
            'groupType' => 'hr',
            'icon' => 'fas fa-info-circle',
            'fields' => [
                '0' => 'status',
                '1' => 'request_date',
                '2' => 'approval_date',
                '3' => 'start_date',
                '4' => 'expected_end_date',
                '5' => 'approved_by',
            ],
        ],
        'loan_notes' => [
            'title' => 'Notes',
            'groupType' => 'hr',
            'icon' => 'fas fa-sticky-note',
            'fields' => [
                '0' => 'notes',
            ],
        ],
    ],

    'moreActions' => [
        '0' => [
            'title' => 'Resume',
            'icon' => 'fas fa-pencil-alt',
            'action' => 'resume',
            'wizardUrl' => '/hr/my-loans?tab=request&loanId={id}',
            'condition' => ['status' => 'draft'],
        ],
        '1' => [
            'title' => 'Submit for Approval',
            'icon' => 'fas fa-paper-plane',
            'event' => 'submitForApproval',
            'permission' => 'edit',
            'condition' => ['status' => 'draft'],
        ],
        '1' => [
            'title' => 'Withdraw',
            'icon' => 'fas fa-undo-alt',
            'action' => 'recall',
            'confirm' => 'Withdraw this loan request? The approval process will be cancelled.',
            'condition' => ['status' => 'pending_approval'],
        ],
        '2' => [
            'title' => 'Mark as Active',
            'icon' => 'fas fa-play-circle',
            'event' => 'activateLoan',
            'permission' => 'edit',
            'condition' => ['status' => 'approved'],
        ],
        '3' => [
            'title' => 'Restore',
            'icon' => 'fas fa-trash-restore',
            'action' => 'restore',
            'confirm' => 'Restore this loan?',
            'requiredPermission' => 'restore_loan',
            'condition' => ['trashed' => [true]],
        ],
        '4' => [
            'title' => 'Permanently Delete',
            'icon' => 'fas fa-skull-crossbones',
            'action' => 'forceDelete',
            'confirm' => 'This action cannot be undone. Permanently delete this loan?',
            'requiredPermission' => 'force_delete_loan',
            'condition' => ['trashed' => [true]],
        ],
    ],

    'simpleActions' => [
        '0' => 'show',
        '1' => 'edit',
        '2' => 'delete',
    ],

    'hiddenFields' => [
        'onTable' => [
            '0' => 'created_at',
            '1' => 'updated_at',
            '2' => 'deleted_at',
            '3' => 'notes',
            '4' => 'repayment_schedule',
        ],
        'onNewForm' => [
            '0' => 'loan_number',
            '1' => 'created_at',
            '2' => 'updated_at',
            '3' => 'deleted_at',
            '4' => 'company_id',
            '5' => 'paid_amount',
            '6' => 'balance',
            '7' => 'monthly_installment',
            '8' => 'total_repayable',
            '9' => 'repayment_schedule',
            '10' => 'approval_date',
            '11' => 'approved_by',
        ],
        'onEditForm' => [
            '0' => 'loan_number',
            '1' => 'updated_at',
            '2' => 'deleted_at',
            '3' => 'company_id',
            '4' => 'repayment_schedule',
        ],
        'onQuery' => [
            '0' => 'deleted_at',
        ],
    ],

    'isTransaction' => false,
    'crudType' => 'drawers',
    'detailComponent' => 'qf.loans.detail',
    'includeControllers' => false,

    'tableDefaultFields' => [
        '0' => 'loan_number',
        '1' => 'employee_id',
        '2' => 'loan_type_id',
        '3' => 'amount',
        '4' => 'status',
        '5' => 'monthly_installment',
        '6' => 'balance',
        '7' => 'request_date',
    ],
];
