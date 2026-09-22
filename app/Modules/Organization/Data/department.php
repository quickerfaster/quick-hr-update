<?php

/**
 * Data Configuration: Organization Department
 *
 * Config Key: organization.department
 */

return [
    'model' => \App\Modules\Organization\Models\Department::class,

    'label' => 'Department',
    'label_plural' => 'Departments',

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
        'branch_id' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'select',
            'label' => 'Branch',
            'validation' => 'nullable|exists:branches,id',
            'filterable' => true,
            'searchable' => true,
            'relationship' => [
                'model' => 'App\Modules\Organization\Models\Branch',
                'type' => 'belongsTo',
                'display_field' => 'name',
                'searchable_fields' => ['name', 'code'],
                'dynamic_property' => 'branch',
                'foreign_key' => 'branch_id',
                'inlineAdd' => false,
            ],
            'options' => [
                'model' => 'App\Modules\Organization\Models\Branch',
                'column' => 'name',
                'hintField' => 'code',
            ],
        ],
        'parent_department_id' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'select',
            'label' => 'Parent Department',
            'validation' => 'nullable|exists:departments,id',
            'searchable' => true,
            'relationship' => [
                'model' => 'App\Modules\Organization\Models\Department',
                'type' => 'belongsTo',
                'display_field' => 'name',
                'searchable_fields' => ['name', 'code'],
                'dynamic_property' => 'parentDepartment',
                'foreign_key' => 'parent_department_id',
                'inlineAdd' => false,
            ],
            'options' => [
                'model' => 'App\Modules\Organization\Models\Department',
                'column' => 'name',
                'hintField' => 'code',
            ],
        ],
        'name' => [
            'display' => 'inline',
            'fillable' => true,
            'field_type' => 'string',
            'label' => 'Department Name',
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
            'title' => 'Department Details',
            'icon' => 'fas fa-sitemap',
            'fields' => ['company_id', 'branch_id', 'parent_department_id', 'name', 'code', 'is_active'],
        ],
        'description' => [
            'title' => 'Description',
            'icon' => 'fas fa-align-left',
            'fields' => ['description'],
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
        'branch_id',
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
