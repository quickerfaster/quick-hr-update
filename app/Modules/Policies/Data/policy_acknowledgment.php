<?php

/**
 * Data Configuration: Policies — Policy Acknowledgment Report
 *
 * Drives the DataTable component for the admin acknowledgment tracking
 * report. Shows which employees have/haven't acknowledged each policy.
 *
 * Config Key: policies.policy_acknowledgment
 */

return [
    'model' => \App\Modules\Policies\Models\PolicyAcknowledgment::class,

    'label' => 'Policy Acknowledgment',
    'label_plural' => 'Policy Acknowledgments',

    'fieldDefinitions' => [
        'company_policy_id' => [
            'display' => 'inline',
            'fillable' => false,
            'field_type' => 'select',
            'label' => 'Policy',
            'validation' => 'required|exists:company_policies,id',
            'filterable' => true,
            'relationship' => [
                'model' => \App\Modules\Policies\Models\CompanyPolicy::class,
                'type' => 'belongsTo',
                'display_field' => 'title',
                'dynamic_property' => 'companyPolicy',
                'foreign_key' => 'company_policy_id',
                'inlineAdd' => false,
            ],
            'options' => [
                'model' => \App\Modules\Policies\Models\CompanyPolicy::class,
                'column' => 'title',
                'hintField' => 'version',
            ],
        ],
        'employee_id' => [
            'display' => 'inline',
            'fillable' => false,
            'field_type' => 'livewire-searchable-select',
            'label' => 'Employee',
            'validation' => 'required|exists:employees,id',
            'filterable' => true,
            'searchable' => true,
            'relationship' => [
                'model' => \App\Modules\Hr\Models\Employee::class,
                'type' => 'belongsTo',
                'display_field' => 'employee_number',
                'dynamic_property' => 'employee',
                'foreign_key' => 'employee_id',
                'inlineAdd' => false,
            ],
            'options' => [
                'model' => \App\Modules\Hr\Models\Employee::class,
                'column' => 'employee_number',
                'hintField' => 'first_name,last_name',
            ],
        ],
        'status' => [
            'display' => 'inline',
            'fillable' => false,
            'field_type' => 'select',
            'label' => 'Status',
            'validation' => 'required|string',
            'options' => [
                'pending' => 'Pending',
                'acknowledged' => 'Acknowledged',
                'declined' => 'Declined',
            ],
            'filterable' => true,
        ],
        'acknowledged_at' => [
            'display' => 'inline',
            'fillable' => false,
            'field_type' => 'datetime',
            'label' => 'Acknowledged At',
            'filterable' => true,
        ],
        'signature' => [
            'display' => 'inline',
            'fillable' => false,
            'field_type' => 'string',
            'label' => 'Signature',
        ],
        'ip_address' => [
            'display' => 'detail',
            'fillable' => false,
            'field_type' => 'string',
            'label' => 'IP Address',
        ],
        'user_agent' => [
            'display' => 'detail',
            'fillable' => false,
            'field_type' => 'text',
            'label' => 'User Agent',
        ],
    ],

    'defaultSort' => ['column' => 'created_at', 'direction' => 'desc'],

    'fieldGroups' => [
        'acknowledgment_details' => [
            'title' => 'Acknowledgment Details',
            'groupType' => 'hr',
            'icon' => 'fas fa-clipboard-check',
            'fields' => [
                '0' => 'company_policy_id',
                '1' => 'employee_id',
                '2' => 'status',
                '3' => 'acknowledged_at',
                '4' => 'signature',
            ],
        ],
        'audit_information' => [
            'title' => 'Audit Information',
            'groupType' => 'hr',
            'icon' => 'fas fa-shield-alt',
            'fields' => [
                '0' => 'ip_address',
                '1' => 'user_agent',
            ],
        ],
    ],

    // Read-only report — no create/edit/delete
    'controls' => [
        'addButton' => false,
        'editable' => false,
        'deletable' => false,
        'files' => [
            'export' => ['csv', 'pdf'],
            'print' => true,
        ],
        'perPage' => [10, 25, 50, 100],
        'search' => true,
        'showHideColumns' => true,
        'filterColumns' => true,
    ],

    'hiddenFields' => [
        'onTable' => [
            '0' => 'created_at',
            '1' => 'updated_at',
            '2' => 'user_agent',
        ],
        'onNewForm' => [],
        'onEditForm' => [],
        'onQuery' => [],
    ],

    'isTransaction' => false,
    'crudType' => 'drawers',
    'includeControllers' => false,

    'tableDefaultFields' => [
        '0' => 'company_policy_id',
        '1' => 'employee_id',
        '2' => 'status',
        '3' => 'acknowledged_at',
        '4' => 'signature',
    ],
];
