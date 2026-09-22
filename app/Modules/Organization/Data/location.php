<?php

/**
 * Data Configuration: Organization Location
 *
 * Config Key: organization.location
 */

return [
    'model' => \App\Modules\Organization\Models\Location::class,

    'label' => 'Location',
    'label_plural' => 'Locations',

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
            'label' => 'Location Name',
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
        'type' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'select',
            'label' => 'Type',
            'validation' => 'nullable|string|max:50',
            'options' => ['office' => 'Office', 'warehouse' => 'Warehouse', 'remote' => 'Remote', 'retail' => 'Retail'],
            'filterable' => true,
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
        'timezone' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Timezone',
            'validation' => 'nullable|string|max:50',
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
            'title' => 'Location Details',
            'icon' => 'fas fa-map-marker-alt',
            'fields' => ['company_id', 'name', 'code', 'type', 'is_headquarters', 'is_active'],
        ],
        'address' => [
            'title' => 'Address',
            'icon' => 'fas fa-map',
            'fields' => ['address', 'city', 'state_code', 'country_code', 'postal_code'],
        ],
        'contact' => [
            'title' => 'Contact',
            'icon' => 'fas fa-phone',
            'fields' => ['phone', 'email', 'timezone'],
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
        'type',
        'company_id',
        'city',
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
