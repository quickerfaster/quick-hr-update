<?php

/**
 * Data Configuration: Loans — Loan Type
 *
 * Drives the DataTable, DataTableForm, and DataTableDetail components
 * for the Loan Type entity in the Loans module.
 *
 * Config Key: loans.loan_type
 */

return [
    'model' => \App\Modules\Loans\Models\LoanType::class,

    'label' => 'Loan Type',
    'label_plural' => 'Loan Types',

    'fieldDefinitions' => [
        'name' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Name',
            'validation' => 'required|string|max:255',
            'filterable' => true,
            'searchable' => true,
        ],
        'description' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'textarea',
            'label' => 'Description',
            'validation' => 'nullable|string',
        ],
        'min_amount' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'currency',
            'label' => 'Min Amount',
            'validation' => 'nullable|numeric|min:0',
            'filterable' => true,
        ],
        'max_amount' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'currency',
            'label' => 'Max Amount',
            'validation' => 'nullable|numeric|min:0',
            'filterable' => true,
        ],
        'default_interest_rate' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Default Interest Rate (% per annum)',
            'validation' => 'nullable|numeric|min:0|max:100',
            'filterable' => true,
        ],
        'max_repayment_months' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Max Repayment Months',
            'validation' => 'required|integer|min:1|max:120',
            'filterable' => true,
        ],
        'requires_approval' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'checkbox',
            'label' => 'Requires Approval',
            'validation' => 'boolean',
            'filterable' => true,
        ],
        'is_active' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'checkbox',
            'label' => 'Active',
            'validation' => 'boolean',
            'filterable' => true,
        ],
    ],

    'defaultSort' => ['column' => 'name', 'direction' => 'asc'],

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
            'titleFields' => ['name'],
            'subtitleFields' => ['description'],
            'badgeField' => 'is_active',
            'badgeColors' => [
                '1' => 'success',
                '0' => 'secondary',
            ],
        ],
    ],

    'fieldGroups' => [
        'loan_type_details' => [
            'title' => 'Loan Type Details',
            'groupType' => 'hr',
            'icon' => 'fas fa-tags',
            'fields' => [
                '0' => 'name',
                '1' => 'description',
                '2' => 'is_active',
            ],
        ],
        'loan_type_limits' => [
            'title' => 'Amount & Term Limits',
            'groupType' => 'hr',
            'icon' => 'fas fa-balance-scale',
            'fields' => [
                '0' => 'min_amount',
                '1' => 'max_amount',
                '2' => 'default_interest_rate',
                '3' => 'max_repayment_months',
                '4' => 'requires_approval',
            ],
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
            '3' => 'eligibility_rules',
        ],
        'onNewForm' => [
            '0' => 'created_at',
            '1' => 'updated_at',
            '2' => 'deleted_at',
            '3' => 'company_id',
            '4' => 'eligibility_rules',
        ],
        'onEditForm' => [
            '0' => 'updated_at',
            '1' => 'deleted_at',
            '2' => 'company_id',
            '3' => 'eligibility_rules',
        ],
        'onQuery' => [
            '0' => 'deleted_at',
        ],
    ],

    'isTransaction' => false,
    'crudType' => 'pages',
    'includeControllers' => false,

    'tableDefaultFields' => [
        '0' => 'name',
        '1' => 'min_amount',
        '2' => 'max_amount',
        '3' => 'default_interest_rate',
        '4' => 'max_repayment_months',
        '5' => 'requires_approval',
        '6' => 'is_active',
    ],
];
