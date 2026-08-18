<?php

return [
  'model' => 'App\Modules\Hr\Models\Tag',
  'fieldDefinitions' => [
    'company_id' => [
      'display' => 'inline',
      'fillable' => true,
      'field_type' => 'select',
      'label' => 'Company',
      'validation' => 'required|integer|exists:companies,id',
      'filterable' => true,
      'searchable' => true,
      'relationship' => [
        'model' => 'App\Modules\Hr\Models\Company',
        'type' => 'belongsTo',
        'display_field' => 'name',
        'dynamic_property' => 'company',
        'foreign_key' => 'company_id',
        'inlineAdd' => false,
      ],
      'options' => [
        'model' => 'App\Modules\Hr\Models\Company',
        'column' => 'name',
        'hintField' => '',
      ],
    ],
    'name' => [
      'display' => 'inline',
      'fillable' => true,
      'field_type' => 'string',
      'label' => 'Tag Name',
      'validation' => 'required|string|max:255|unique:tags,name',
      'filterable' => true,
      'searchable' => true,
    ],
    'slug' => [
      'display' => 'inline',
      'fillable' => true,
      'field_type' => 'string',
      'label' => 'Slug',
      'validation' => 'nullable|string|max:255|unique:tags,slug',
      'autoGenerate' => true,
      'searchable' => true,
    ],
    'color' => [
      'display' => 'inline',
      'fillable' => true,
      'field_type' => 'select',
      'label' => 'Tag Color',
      'validation' => 'required|in:primary,success,danger,warning,info,secondary,dark',
      'options' => [
        'primary' => 'Default (Blue)',
        'success' => 'Green',
        'danger' => 'Red',
        'warning' => 'Orange',
        'info' => 'Cyan',
        'secondary' => 'Gray',
        'dark' => 'Black',
      ],
      'filterable' => true,
    ],
    'description' => [
      'display' => 'inline',
      'fillable' => true,
      'field_type' => 'textarea',
      'label' => 'Description',
      'validation' => 'nullable|string|max:500',
      'searchable' => true,
    ],
    'is_active' => [
      'display' => 'inline',
      'fillable' => true,
      'field_type' => 'checkbox',
      'label' => 'Active',
      'validation' => 'nullable|boolean',
      'filterable' => true,
    ],
  ],
  'detailComponent' => '',
  'hiddenFields' => [
    'onTable' => [
      '0' => 'slug',
      '1' => 'created_at',
      '2' => 'updated_at',
      '3' => 'company_id',
    ],
    'onNewForm' => [
      '0' => 'slug',
      '1' => 'company_id',
    ],
    'onEditForm' => [
      '0' => 'slug',
      '1' => 'company_id',
    ],
    'onQuery' => [],
  ],
  'simpleActions' => [
    '0' => 'show',
    '1' => 'edit',
    '2' => 'delete',
  ],
  'isTransaction' => false,
  'crudType' => 'drawers',
  'includeControllers' => false,
  'tableDefaultFields' => [
    '0' => 'company_id',
    '1' => 'name',
    '2' => 'color',
    '3' => 'is_active',
  ],
  'addRoutes' => false,
  'dispatchEvents' => false,
  'controls' => [
    'addButton' => [
      '0' => [
        'label' => 'New Tag',
        'type' => 'quick_add',
        'icon' => 'fas fa-plus',
        'primary' => true,
      ],
    ],
    'files' => [
      'export' => [
        '0' => 'xls',
        '1' => 'csv',
        '2' => 'pdf',
      ],
      'print' => true,
    ],
    'perPage' => [
      '0' => 10,
      '1' => 25,
      '2' => 50,
      '3' => 100,
    ],
    'search' => true,
    'showHideColumns' => true,
    'filterColumns' => true,
    'editable' => true,
    'bulkActions' => [
      'export' => [
        '0' => 'xls',
        '1' => 'csv',
        '2' => 'pdf',
      ],
      'activate' => [
        'label' => 'Activate Selected',
        'icon' => 'fas fa-toggle-on',
        'updateModelField' => 'is_active',
        'fieldValue' => true,
        'confirm' => 'Activate selected tags?',
      ],
      'deactivate' => [
        'label' => 'Deactivate Selected',
        'icon' => 'fas fa-toggle-off',
        'updateModelField' => 'is_active',
        'fieldValue' => false,
        'confirm' => 'Deactivate selected tags?',
      ],
      'delete' => true,
    ],
  ],
  'fieldGroups' => [
    'company' => [
      'title' => 'Company',
      'groupType' => 'hr',
      'icon' => 'fas fa-building',
      'fields' => [
        '0' => 'company_id',
      ],
    ],
    'basic_info' => [
      'title' => 'Tag Information',
      'groupType' => 'hr',
      'icon' => 'fas fa-info-circle',
      'fields' => [
        '0' => 'name',
        '1' => 'color',
        '2' => 'description',
        '3' => 'is_active',
      ],
    ],
  ],
  'moreActions' => [],
  'switchViews' => [
    'default' => 'list',
    'table' => [
      'enabled' => true,
    ],
    'list' => [
      'enabled' => true,
      'titleFields' => [
        '0' => 'name',
      ],
      'subtitleFields' => [
        '0' => 'color',
      ],
      'contentFields' => [
        '0' => 'description',
      ],
      'badgeField' => 'is_active',
      'badgeColors' => [
        'true' => 'success',
        'false' => 'secondary',
      ],
    ],
    'card' => [
      'enabled' => true,
      'titleFields' => [
        '0' => 'name',
      ],
      'subtitleFields' => [
        '0' => 'color',
      ],
      'contentFields' => [
        '0' => 'description',
      ],
      'badgeField' => 'is_active',
      'badgeColors' => [
        'true' => 'success',
        'false' => 'secondary',
      ],
      'defaultIconClass' => 'fas fa-tag',
    ],
  ],
  'relations' => [],
  'report' => [],
];
