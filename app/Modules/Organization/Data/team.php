<?php

/**
 * Data Configuration: Organization Team
 *
 * Config Key: organization.team
 */

return [
    'model' => \App\Modules\Organization\Models\Team::class,

    'label' => 'Team',
    'label_plural' => 'Teams',

    /*
    |--------------------------------------------------------------------------
    | Field Definitions
    |--------------------------------------------------------------------------
    */
    'fieldDefinitions' => [
        'company_id' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'select',
            'label' => 'Company',
            'validation' => 'required|exists:companies,id',
            'filterable' => true,
        ],
        'department_id' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'select',
            'label' => 'Department',
            'validation' => 'nullable|exists:departments,id',
            'filterable' => true,
        ],
        'name' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Team Name',
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
        'description' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'textarea',
            'label' => 'Description',
            'validation' => 'nullable|string',
        ],
        'type' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'select',
            'label' => 'Type',
            'validation' => 'nullable|string|max:50',
            'options' => ['permanent' => 'Permanent', 'project' => 'Project', 'virtual' => 'Virtual'],
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

    'columns' => [
        'id' => [
            'label' => 'ID',
            'field' => 'id',
            'sortable' => true,
            'type' => 'text',
            'visible' => true,
        ],
        'name' => [
            'label' => 'Team Name',
            'field' => 'name',
            'sortable' => true,
            'searchable' => true,
            'type' => 'text',
            'visible' => true,
        ],
        'code' => [
            'label' => 'Code',
            'field' => 'code',
            'sortable' => true,
            'searchable' => true,
            'type' => 'text',
            'visible' => true,
        ],
        'type' => [
            'label' => 'Type',
            'field' => 'type',
            'sortable' => true,
            'type' => 'text',
            'visible' => false,
        ],
        'company_id' => [
            'label' => 'Company',
            'field' => 'company_id',
            'sortable' => true,
            'type' => 'text',
            'visible' => true,
        ],
        'department_id' => [
            'label' => 'Department',
            'field' => 'department_id',
            'sortable' => true,
            'type' => 'text',
            'visible' => false,
        ],
        'is_active' => [
            'label' => 'Active',
            'field' => 'is_active',
            'sortable' => true,
            'type' => 'boolean',
            'visible' => true,
        ],
        'created_at' => [
            'label' => 'Created',
            'field' => 'created_at',
            'sortable' => true,
            'type' => 'date',
            'visible' => true,
        ],
    ],

    'fields' => [
        'company_id' => [
            'label' => 'Company',
            'type' => 'select',
            'validation' => ['required', 'exists:companies,id'],
        ],
        'department_id' => [
            'label' => 'Department',
            'type' => 'select',
            'validation' => ['nullable', 'exists:departments,id'],
        ],
        'name' => [
            'label' => 'Team Name',
            'type' => 'text',
            'validation' => ['required', 'string', 'max:255'],
        ],
        'code' => [
            'label' => 'Code',
            'type' => 'text',
            'validation' => ['nullable', 'string', 'max:50'],
        ],
        'description' => [
            'label' => 'Description',
            'type' => 'textarea',
            'validation' => ['nullable', 'string'],
        ],
        'type' => [
            'label' => 'Type',
            'type' => 'select',
            'validation' => ['nullable', 'string', 'max:50'],
            'options' => ['permanent' => 'Permanent', 'project' => 'Project', 'virtual' => 'Virtual'],
        ],
        'is_active' => [
            'label' => 'Active',
            'field_type' => 'checkbox',
            'validation' => ['nullable', 'boolean'],
        ],
    ],

    'filters' => [
        'company_id' => [
            'label' => 'Company',
            'type' => 'select',
        ],
        'department_id' => [
            'label' => 'Department',
            'type' => 'select',
        ],
        'type' => [
            'label' => 'Type',
            'type' => 'select',
            'options' => ['' => 'All', 'permanent' => 'Permanent', 'project' => 'Project', 'virtual' => 'Virtual'],
        ],
        'is_active' => [
            'label' => 'Status',
            'type' => 'select',
            'options' => ['' => 'All', '1' => 'Active', '0' => 'Inactive'],
        ],
    ],

    'controls' => [
        'create' => true,
        'edit' => true,
        'delete' => true,
        'view' => true,
        'export' => true,
        'import' => true,
        'print' => true,
        'softDelete' => true,
    ],

    'detail' => [
        'fields' => ['id', 'company_id', 'department_id', 'name', 'code', 'description', 'type', 'is_active', 'created_at', 'updated_at'],
    ],

    'default_sort' => [
        'field' => 'name',
        'direction' => 'asc',
    ],

    'per_page_options' => [10, 25, 50, 100],
    'default_per_page' => 25,

    'switchViews' => [
        'default' => 'list',
        'table' => ['enabled' => true],
        'list' => [
            'enabled' => true,
            'titleFields' => ['name'],
            'subtitleFields' => ['code', 'type'],
            'badgeField' => 'is_active',
            'badgeColors' => ['1' => 'success', '0' => 'secondary'],
        ],
    ],
];
