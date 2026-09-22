<?php

/**
 * Data Configuration: Organization Branch
 *
 * Config Key: organization.branch
 */

return [
    'model' => \App\Modules\Organization\Models\Branch::class,

    'label' => 'Branch',
    'label_plural' => 'Branches',

    'fieldDefinitions' => [
        'company_id' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'select',
            'label' => 'Company',
            'validation' => 'required|exists:companies,id',
            'filterable' => true,
            'searchable' => true,
            'relationship' => [
                'model' => 'App\Modules\Organization\Models\Company',
                'type' => 'belongsTo',
                'display_field' => 'name',
                'searchable_fields' => ['name', 'code'],
                'dynamic_property' => 'company',
                'foreign_key' => 'company_id',
                'inlineAdd' => false,
            ],
            'options' => [
                'model' => 'App\Modules\Organization\Models\Company',
                'column' => 'name',
                'hintField' => 'code',
            ],
        ],
        'name' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Branch Name',
            'validation' => 'required|string|max:255',
            'filterable' => true,
            'searchable' => true,
        ],
        'code' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Code',
            'validation' => 'nullable|string|max:50',
            'filterable' => true,
            'searchable' => true,
        ],
        'address' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'textarea',
            'label' => 'Address',
            'validation' => 'nullable|string',
        ],
        'city' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'City',
            'validation' => 'nullable|string|max:100',
            'filterable' => true,
            'searchable' => true,
        ],
        'state_code' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'State',
            'validation' => 'nullable|string|max:100',
        ],
        'country_code' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Country',
            'validation' => 'nullable|string|max:100',
            'filterable' => true,
        ],
        'postal_code' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Postal Code',
            'validation' => 'nullable|string|max:20',
        ],
        'phone' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Phone',
            'validation' => 'nullable|string|max:50',
        ],
        'email' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Email',
            'validation' => 'nullable|email|max:255',
        ],
        'is_headquarters' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'select',
            'label' => 'Is Headquarters',
            'validation' => 'boolean',
            'options' => ['1' => 'Yes', '0' => 'No'],
            'filterable' => true,
        ],
        'is_active' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'select',
            'label' => 'Active',
            'validation' => 'boolean',
            'options' => ['1' => 'Active', '0' => 'Inactive'],
            'filterable' => true,
        ],
        'created_at' => [
            'display' => 'inline',
            'fillable' => false,
            'field_type' => 'datetimepicker',
            'label' => 'Created',
            'validation' => 'nullable|date',
        ],
        'updated_at' => [
            'display' => 'inline',
            'fillable' => false,
            'field_type' => 'datetimepicker',
            'label' => 'Updated',
            'validation' => 'nullable|date',
        ],
    ],

    'fieldGroups' => [
        'details' => [
            'title' => 'Branch Details',
            'icon' => 'fas fa-code-branch',
            'fields' => ['company_id', 'name', 'code', 'is_headquarters', 'is_active'],
        ],
        'address' => [
            'title' => 'Address',
            'icon' => 'fas fa-map',
            'fields' => ['address', 'city', 'state_code', 'country_code', 'postal_code'],
        ],
        'contact' => [
            'title' => 'Contact',
            'icon' => 'fas fa-phone',
            'fields' => ['phone', 'email'],
        ],
    ],

    'hiddenFields' => [
        'onTable' => ['created_at', 'updated_at'],
        'onNewForm' => ['created_at', 'updated_at'],
        'onEditForm' => ['updated_at'],
        'onQuery' => [],
    ],

    'simpleActions' => ['show', 'edit', 'delete'],

    'tableDefaultFields' => [
        'name',
        'code',
        'company_id',
        'city',
        'country_code',
        'is_active',
    ],

    'isTransaction' => false,
    'crudType' => 'drawers',
    'includeControllers' => false,

    'controls' => [
        'addButton' => true,
        'files' => [
            'export' => ['xls', 'csv', 'pdf'],
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
            'export' => ['xls', 'csv', 'pdf'],
            'delete' => true,
            'restore' => true,
            'forceDelete' => true,
        ],
    ],

    'switchViews' => [
        'default' => 'list',
        'table' => ['enabled' => true],
        'list' => [
            'enabled' => true,
            'titleFields' => ['name'],
            'subtitleFields' => ['code'],
            'badgeField' => 'is_active',
            'badgeColors' => ['1' => 'success', '0' => 'secondary'],
        ],
    ],
];
