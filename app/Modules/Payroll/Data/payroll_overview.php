<?php

/**
 * Payroll Overview — Placeholder Stub
 *
 * This data config is a stub and is not actively used in the current
 * Payroll module. It exists as a placeholder for a future payroll
 * overview / summary view. The model `PayrollOverview` referenced
 * here may not yet exist or be fully implemented.
 *
 * Do not rely on this config for production data tables or forms.
 */

return [
  'model' => 'App\Modules\Payroll\Models\PayrollOverview',
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
    'dummy' => [
      'display' => 'inline',
      'fillable' => true,
      'field_type' => 'string',
      'label' => 'Dummy',
      'validation' => 'nullable|string|max:255',
    ],
  ],
  'detailComponent' => '',
  'hiddenFields' => [
    'onTable' => [],
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
  'controls' => [],
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
  'switchViews' => [],
  'relations' => [],
  'report' => [],
];
