<?php

return [
  'model' => 'App\Modules\Hr\Models\LeaveType',
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
      'label' => 'Leave Type Name',
      'validation' => 'required|string|max:255',
      'filterable' => true,
      'searchable' => true,
    ],
    'code' => [
      'display' => 'inline',
      'fillable' => true,
      'field_type' => 'string',
      'label' => 'Short Code',
      'validation' => 'required|string|max:10|unique:leave_types,code',
      'filterable' => true,
      'searchable' => true,
    ],
    'description' => [
      'display' => 'inline',
      'fillable' => true,
      'field_type' => 'textarea',
      'label' => 'Description',
      'validation' => 'nullable|string',
      'searchable' => true,
    ],
    'deducts_from_balance' => [
      'display' => 'inline',
      'fillable' => true,
      'field_type' => 'radio',
      'label' => 'Deducts from Balance',
      'validation' => 'required',
      'options' => [
        '0' => 'Yes',
        '1' => 'No',
      ],
      'filterable' => true,
    ],
    'requires_approval' => [
      'display' => 'inline',
      'fillable' => true,
      'field_type' => 'radio',
      'label' => 'Requires Approval',
      'validation' => 'required',
      'options' => [
        '0' => 'Yes',
        '1' => 'No',
      ],
      'filterable' => true,
    ],
    'max_days_per_request' => [
      'display' => 'inline',
      'fillable' => true,
      'field_type' => 'number',
      'label' => 'Max Days per Request',
      'validation' => 'nullable|integer|min:1',
    ],
    'is_active' => [
      'display' => 'inline',
      'fillable' => true,
      'field_type' => 'radio',
      'label' => 'Status',
      'validation' => 'required',
      'options' => [
        '0' => 'Active',
        '1' => 'Inactive',
      ],
      'filterable' => true,
    ],
  ],
  'detailComponent' => '',
  'hiddenFields' => [
    'onTable' => [
      '0' => 'description',
      '1' => 'max_days_per_request',
      '2' => 'created_at',
      '3' => 'updated_at',
      '4' => 'deleted_at',
      '5' => 'company_id',
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
    '3' => 'deducts_from_balance',
    '4' => 'requires_approval',
    '5' => 'is_active',
  ],
  'addRoutes' => false,
  'dispatchEvents' => false,
  'controls' => [
    'addButton' => true,
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
      ],
    ],
    'rules' => [
      'title' => 'Leave Rules',
      'groupType' => 'hr',
      'icon' => 'fas fa-gavel',
      'fields' => [
        '0' => 'deducts_from_balance',
        '1' => 'requires_approval',
        '2' => 'max_days_per_request',
        '3' => 'is_active',
      ],
    ],
  ],
  'moreActions' => [
    '0' => [
      'title' => 'Restore',
      'icon' => 'fas fa-trash-restore',
      'action' => 'restore',
      'confirm' => 'Restore this archived leave type?',
      'requiredPermission' => 'restore_leave_type',
      'condition' => ['trashed' => [true]],
    ],
    '1' => [
      'title' => 'Permanently Delete',
      'icon' => 'fas fa-skull-crossbones',
      'action' => 'forceDelete',
      'confirm' => 'This action cannot be undone. Permanently delete this leave type?',
      'requiredPermission' => 'force_delete_leave_type',
      'condition' => ['trashed' => [true]],
    ],
  ],
  'switchViews' => [
    'default' => 'list',
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
        '0' => 'deducts_from_balance',
        '1' => 'requires_approval',
      ],
      'badgeField' => 'is_active',
      'badgeColors' => [
        'true' => 'success',
        'false' => 'secondary',
      ],
    ],
  ],
  'relations' => [
    'leaveBalances' => [
      'type' => 'hasMany',
      'model' => 'App\Modules\Hr\Models\LeaveBalance',
      'foreignKey' => 'leave_type_id',
      'localKey' => '',
    ],
  ],
  'report' => [],
];
