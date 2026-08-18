<?php

return [
  'model' => 'App\Modules\Hr\Models\Team',
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
      'label' => 'Team Name',
      'validation' => 'required|string|max:255|unique:teams,name',
      'filterable' => true,
      'searchable' => true,
    ],
    'code' => [
      'display' => 'inline',
      'fillable' => true,
      'field_type' => 'string',
      'label' => 'Team Code',
      'validation' => 'required|string|max:50|unique:teams,code',
      'autoGenerate' => true,
      'filterable' => true,
      'searchable' => true,
    ],
    'description' => [
      'display' => 'inline',
      'fillable' => true,
      'field_type' => 'textarea',
      'label' => 'Description',
      'validation' => 'nullable|string|max:1000',
      'searchable' => true,
    ],
    'team_lead_id' => [
      'display' => 'inline',
      'fillable' => true,
      'field_type' => 'select',
      'label' => 'Team Lead',
      'validation' => 'nullable|exists:employees,id',
      'filterable' => true,
      'searchable' => true,
      'relationship' => [
        'model' => 'App\Modules\Hr\Models\Employee',
        'type' => 'belongsTo',
        'display_field' => 'employee_number',
        'dynamic_property' => 'teamLead',
        'foreign_key' => 'team_lead_id',
        'inlineAdd' => false,
      ],
      'options' => [
        'model' => 'App\Modules\Hr\Models\Employee',
        'column' => 'employee_number',
        'hintField' => 'first_name,last_name',
      ],
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
      '0' => 'created_at',
      '1' => 'updated_at',
      '2' => 'deleted_at',
      '3' => 'company_id',
    ],
    'onNewForm' => [
      '0' => 'created_at',
      '1' => 'updated_at',
      '2' => 'deleted_at',
      '3' => 'company_id',
    ],
    'onEditForm' => [
      '0' => 'updated_at',
      '1' => 'deleted_at',
      '2' => 'company_id',
    ],
    'onQuery' => [
      '0' => 'deleted_at',
    ],
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
    '2' => 'code',
    '3' => 'team_lead_id',
    '4' => 'is_active',
  ],
  'addRoutes' => false,
  'dispatchEvents' => false,
  'controls' => [
    'addButton' => [
      '0' => [
        'label' => 'New Team',
        'type' => 'quick_add',
        'icon' => 'fas fa-plus-circle',
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
    'softDelete' => true,
    'restore' => true,
    'forceDelete' => true,
    'trashView' => true,
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
        'confirm' => 'Activate selected teams?',
      ],
      'deactivate' => [
        'label' => 'Deactivate Selected',
        'icon' => 'fas fa-toggle-off',
        'updateModelField' => 'is_active',
        'fieldValue' => false,
        'confirm' => 'Deactivate selected teams?',
      ],
      'delete' => true,
      'restore' => true,
      'forceDelete' => true,
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
      'title' => 'Basic Information',
      'groupType' => 'hr',
      'icon' => 'fas fa-info-circle',
      'fields' => [
        '0' => 'name',
        '1' => 'code',
        '2' => 'description',
        '3' => 'is_active',
      ],
    ],
    'leadership' => [
      'title' => 'Leadership',
      'groupType' => 'hr',
      'icon' => 'fas fa-user-friends',
      'fields' => [
        '0' => 'team_lead_id',
      ],
    ],
  ],
  'moreActions' => [
    '0' => [
      'title' => 'Restore',
      'icon' => 'fas fa-trash-restore',
      'action' => 'restore',
      'confirm' => 'Restore this archived team?',
      'requiredPermission' => 'restore_team',
      'condition' => ['trashed' => [true]],
    ],
    '1' => [
      'title' => 'Permanently Delete',
      'icon' => 'fas fa-skull-crossbones',
      'action' => 'forceDelete',
      'confirm' => 'This action cannot be undone. Permanently delete this team?',
      'requiredPermission' => 'force_delete_team',
      'condition' => ['trashed' => [true]],
    ],
  ],
  'switchViews' => [
    'default' => 'table',
    'table' => [
      'enabled' => true,
    ],
    'list' => [
      'enabled' => true,
      'titleFields' => [
        '0' => 'name',
      ],
      'subtitleFields' => [
        '0' => 'code',
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
        '0' => 'code',
      ],
      'contentFields' => [
        '0' => 'description',
      ],
      'badgeField' => 'is_active',
      'badgeColors' => [
        'true' => 'success',
        'false' => 'secondary',
      ],
      'defaultIconClass' => 'fas fa-users',
    ],
  ],
  'relations' => [
    'employees' => [
      'type' => 'belongsToMany',
      'model' => 'App\Modules\Hr\Models\Employee',
      'foreignKey' => '',
      'localKey' => '',
    ],
    'teamLead' => [
      'type' => 'belongsTo',
      'model' => 'App\Modules\Hr\Models\Employee',
      'foreignKey' => 'team_lead_id',
      'localKey' => '',
    ],
  ],
  'report' => [],
];
