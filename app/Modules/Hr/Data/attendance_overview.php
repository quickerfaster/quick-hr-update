<?php

return [
  'model' => 'App\Modules\Hr\Models\AttendanceOverview',
  'fieldDefinitions' => [
    'dummy' => [
      'display' => 'inline',
      'fillable' => true,
      'field_type' => 'string',
      'label' => 'Dummy',
      'validation' => 'nullable|string|max:255',
      'editable' => false,
    ],
    'company_id' => [
      'display' => 'inline',
      'fillable' => true,
      'field_type' => 'select',
      'label' => 'Company',
      'validation' => 'required|integer|exists:companies,id',
      'filterable' => true,
      'searchable' => true,
      'editable' => false,
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
  ],
  'detailComponent' => '',
  'hiddenFields' => [
    'onTable' => [
      '0' => 'company_id',
    ],
    'onNewForm' => [
      '0' => 'company_id',
    ],
    'onEditForm' => [
      '0' => 'company_id',
    ],
    'onQuery' => [],
  ],
  'simpleActions' => [],
  'isTransaction' => false,
  'crudType' => 'modals',
  'includeControllers' => false,
  'tableDefaultFields' => [
    '0' => 'company_id',
  ],
  'addRoutes' => false,
  'dispatchEvents' => false,
  'controls' => [
    'editable' => false,
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
  ],
  'moreActions' => [],
  'switchViews' => [
    'default' => 'table',
    'table' => [
      'enabled' => true,
    ],
    'list' => [
      'enabled' => true,
      'titleFields' => [
        '0' => 'company.name',
      ],
      'subtitleFields' => [
        '0' => 'dummy',
      ],
      'contentFields' => [],
      'badgeField' => 'dummy',
      'badgeColors' => [],
    ],
    'card' => [
      'enabled' => true,
      'titleFields' => [
        '0' => 'company.name',
      ],
      'subtitleFields' => [
        '0' => 'dummy',
      ],
      'contentFields' => [],
      'badgeField' => 'dummy',
      'badgeColors' => [],
    ],
  ],
  'relations' => [],
  'report' => [],
];
