<?php

/**
 * Data Configuration: Policies — Company Policy
 *
 * Drives the DataTable, DataTableForm, and DataTableDetail components
 * for the Company Policy entity in the Policies module.
 *
 * Config Key: policies.company_policy
 */

return [
    'model' => \App\Modules\Policies\Models\CompanyPolicy::class,

    'label' => 'Company Policy',
    'label_plural' => 'Company Policies',

    'fieldDefinitions' => [
        'title' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Title',
            'validation' => 'required|string|max:255',
            'filterable' => true,
            'searchable' => true,
        ],
        'category' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'select',
            'label' => 'Category',
            'validation' => 'required|string',
            'options' => [
                'handbook' => 'Handbook',
                'code_of_conduct' => 'Code of Conduct',
                'leave_policy' => 'Leave Policy',
                'expense_policy' => 'Expense Policy',
                'it_policy' => 'IT Policy',
                'health_safety' => 'Health & Safety',
                'other' => 'Other',
            ],
            'filterable' => true,
        ],
        'version' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Version',
            'validation' => 'required|string|max:50',
            'filterable' => true,
        ],
        'status' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'select',
            'label' => 'Status',
            'validation' => 'required|string',
            'options' => [
                'draft' => 'Draft',
                'published' => 'Published',
                'archived' => 'Archived',
            ],
            'filterable' => true,
        ],
        'effective_date' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'datepicker',
            'label' => 'Effective Date',
            'validation' => 'nullable|date',
            'filterable' => true,
        ],
        'requires_acknowledgment' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'checkbox',
            'label' => 'Requires Acknowledgment',
            'validation' => 'boolean',
            'filterable' => true,
        ],
        'priority' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'select',
            'label' => 'Priority',
            'validation' => 'required|string',
            'options' => [
                'low' => 'Low',
                'medium' => 'Medium',
                'high' => 'High',
                'critical' => 'Critical',
            ],
            'filterable' => true,
        ],
        'description' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'textarea',
            'label' => 'Description',
            'validation' => 'nullable|string',
        ],
        'content' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'richtext',
            'label' => 'Policy Content',
            'validation' => 'nullable|string',
        ],
        'review_date' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'datepicker',
            'label' => 'Review Date',
            'validation' => 'nullable|date',
        ],
        'acknowledgment_required_by' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'datepicker',
            'label' => 'Acknowledgment Deadline',
            'validation' => 'nullable|date',
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
            'titleFields' => ['title'],
            'subtitleFields' => ['category', 'version'],
            'badgeField' => 'status',
            'badgeColors' => [
                'draft' => 'secondary',
                'published' => 'success',
                'archived' => 'dark',
            ],
        ],
    ],

    'fieldGroups' => [
        'policy_details' => [
            'title' => 'Policy Details',
            'groupType' => 'hr',
            'icon' => 'fas fa-file-contract',
            'fields' => [
                '0' => 'title',
                '1' => 'category',
                '2' => 'description',
                '3' => 'content',
            ],
        ],
        'policy_metadata' => [
            'title' => 'Version & Status',
            'groupType' => 'hr',
            'icon' => 'fas fa-info-circle',
            'fields' => [
                '0' => 'version',
                '1' => 'status',
                '2' => 'priority',
                '3' => 'effective_date',
                '4' => 'review_date',
            ],
        ],
        'policy_acknowledgment' => [
            'title' => 'Acknowledgment Settings',
            'groupType' => 'hr',
            'icon' => 'fas fa-clipboard-check',
            'fields' => [
                '0' => 'requires_acknowledgment',
                '1' => 'acknowledgment_required_by',
            ],
        ],
    ],

    'moreActions' => [
        '0' => [
            'title' => 'Publish',
            'icon' => 'fas fa-check-circle',
            'event' => 'publishPolicy',
            'permission' => 'edit_company_policy',
            'condition' => ['status' => 'draft'],
        ],
        '1' => [
            'title' => 'Archive',
            'icon' => 'fas fa-archive',
            'event' => 'archivePolicy',
            'permission' => 'edit_company_policy',
            'condition' => ['status' => 'published'],
        ],
        '2' => [
            'title' => 'Restore',
            'icon' => 'fas fa-trash-restore',
            'action' => 'restore',
            'confirm' => 'Restore this policy?',
            'requiredPermission' => 'restore_company_policy',
            'condition' => ['trashed' => [true]],
        ],
        '3' => [
            'title' => 'Permanently Delete',
            'icon' => 'fas fa-skull-crossbones',
            'action' => 'forceDelete',
            'confirm' => 'This action cannot be undone. Permanently delete this policy?',
            'requiredPermission' => 'force_delete_company_policy',
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
            '3' => 'created_by',
            '4' => 'content',
            '5' => 'description',
        ],
        'onNewForm' => [
            '0' => 'created_at',
            '1' => 'updated_at',
            '2' => 'deleted_at',
            '3' => 'company_id',
            '4' => 'created_by',
        ],
        'onEditForm' => [
            '0' => 'updated_at',
            '1' => 'deleted_at',
            '2' => 'company_id',
            '3' => 'created_by',
        ],
        'onQuery' => [
            '0' => 'deleted_at',
        ],
    ],

    'isTransaction' => false,
    'crudType' => 'pages',
    'includeControllers' => false,

    'tableDefaultFields' => [
        '0' => 'title',
        '1' => 'category',
        '2' => 'version',
        '3' => 'status',
        '4' => 'effective_date',
        '5' => 'requires_acknowledgment',
        '6' => 'priority',
    ],
];
