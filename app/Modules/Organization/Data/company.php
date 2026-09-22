<?php

/**
 * Data Configuration: Organization Company
 *
 * Drives the DataTable, DataTableForm, and DataTableDetail components
 * for the Company entity in the Organization module.
 *
 * Config Key: organization.company
 */

return [
    'model' => \App\Modules\Organization\Models\Company::class,

    'label' => 'Company',
    'label_plural' => 'Companies',

    'fieldDefinitions' => [
        'name' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Company Name',
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
        'email' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Email',
            'validation' => 'nullable|email|max:255',
            'filterable' => true,
            'searchable' => true,
        ],
        'phone' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Phone',
            'validation' => 'nullable|string|max:50',
            'filterable' => true,
        ],
        'website' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Website',
            'validation' => 'nullable|url|max:255',
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
        'state' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'State',
            'validation' => 'nullable|string|max:100',
        ],
        'country' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Country',
            'validation' => 'nullable|string|max:100',
            'filterable' => true,
            'searchable' => true,
        ],
        'postal_code' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Postal Code',
            'validation' => 'nullable|string|max:20',
        ],
        'tax_id' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Tax ID',
            'validation' => 'nullable|string|max:100',
        ],
        'registration_number' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Registration Number',
            'validation' => 'nullable|string|max:100',
        ],
        'currency_code' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'select',
            'label' => 'Currency',
            'validation' => 'nullable|string|max:3',
            'options' => ['USD' => 'USD', 'EUR' => 'EUR', 'GBP' => 'GBP', 'NGN' => 'NGN'],
        ],
        'timezone' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Timezone',
            'validation' => 'nullable|string|max:50',
        ],
        'date_format' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Date Format',
            'validation' => 'nullable|string|max:20',
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
            'title' => 'Company Details',
            'icon' => 'fas fa-building',
            'fields' => ['name', 'code', 'email', 'phone', 'website', 'is_active'],
        ],
        'location' => [
            'title' => 'Location',
            'icon' => 'fas fa-map-marker-alt',
            'fields' => ['address', 'city', 'state', 'country', 'postal_code'],
        ],
        'settings' => [
            'title' => 'Settings',
            'icon' => 'fas fa-cog',
            'fields' => ['currency_code', 'timezone', 'date_format', 'tax_id', 'registration_number'],
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
        'email',
        'city',
        'country',
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
