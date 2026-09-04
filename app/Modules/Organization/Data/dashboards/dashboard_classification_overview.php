<?php

return array (
  'title' => 'Classification Overview',
  'description' => 'Monitor organizational classifications, tags, and categorisation metrics',
  'widgets' =>
  array (
    0 =>
    array (
      'type' => 'stat',
      'title' => 'Total Companies',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Company',
      'icon' => 'fas fa-building',
      'aggregate' => 'count',
      'width' => 3,
    ),
    1 =>
    array (
      'type' => 'stat',
      'title' => 'Total Branches',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Branch',
      'icon' => 'fas fa-code-branch',
      'aggregate' => 'count',
      'width' => 3,
    ),
    2 =>
    array (
      'type' => 'stat',
      'title' => 'Business Units',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\BusinessUnit',
      'icon' => 'fas fa-briefcase',
      'aggregate' => 'count',
      'width' => 3,
    ),
    3 =>
    array (
      'type' => 'chart',
      'title' => 'Entities by Type',
      'size' => 'col-12',
      'chart_type' => 'bar',
      'description' => 'Companies, branches, and business units',
      'width' => 4,
      'static_data' =>
      array (
        'labels' =>
        array (
          0 => 'Companies',
          1 => 'Branches',
          2 => 'Business Units',
        ),
        'datasets' =>
        array (
          0 =>
          array (
            'label' => 'Count',
            'data' =>
            array (
              0 => '{{ App\\Modules\\Organization\\Models\\Company::count() }}',
              1 => '{{ App\\Modules\\Organization\\Models\\Branch::count() }}',
              2 => '{{ App\\Modules\\Organization\\Models\\BusinessUnit::count() }}',
            ),
          ),
        ),
      ),
    ),
    4 =>
    array (
      'type' => 'list',
      'title' => 'Companies (A–Z)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Company',
      'icon' => 'fas fa-building',
      'description' => 'All companies alphabetically',
      'limit' => 5,
      'sort' =>
      array (
        0 => 'name',
        1 => 'asc',
      ),
      'columns' =>
      array (
        0 =>
        array (
          'label' => 'Name',
          'field' => 'name',
        ),
        1 =>
        array (
          'label' => 'Code',
          'field' => 'code',
        ),
        2 =>
        array (
          'label' => 'Status',
          'field' => 'is_active',
          'format' => 'boolean',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/organization/companies',
    ),
    5 =>
    array (
      'type' => 'list',
      'title' => 'Business Units',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\BusinessUnit',
      'icon' => 'fas fa-briefcase',
      'description' => 'Latest 5 business units',
      'limit' => 5,
      'sort' =>
      array (
        0 => 'created_at',
        1 => 'desc',
      ),
      'columns' =>
      array (
        0 =>
        array (
          'label' => 'Name',
          'field' => 'name',
        ),
        1 =>
        array (
          'label' => 'Code',
          'field' => 'code',
        ),
        2 =>
        array (
          'label' => 'Company',
          'field' => 'company.name',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/organization/business-units',
    ),
    6 =>
    array (
      'type' => 'action_card',
      'title' => 'Add Company',
      'size' => 'col-12',
      'icon' => 'fas fa-plus-circle',
      'description' => 'Register a new company in the organization',
      'actions' =>
      array (
        0 =>
        array (
          'label' => 'Create',
          'event' => 'openDrawer',
          'params' =>
          array (
            'component' => 'qf.data-table-form',
            'params' =>
            array (
              'configKey' => 'hr.company',
              'recordId' => null,
            ),
            'title' => 'Add Company',
          ),
          'style' => 'primary',
        ),
      ),
      'width' => 3,
    ),
    7 =>
   array (
     'type' => 'action_card',
     'title' => 'Add Business Unit',
     'size' => 'col-12',
     'icon' => 'fas fa-briefcase',
     'description' => 'Create a new business unit',
     'actions' =>
     array (
       0 =>
       array (
         'label' => 'Create',
         'event' => 'openDrawer',
         'params' =>
         array (
           'component' => 'qf.data-table-form',
           'params' =>
           array (
             'configKey' => 'organization.business_unit',
             'recordId' => null,
           ),
           'title' => 'Add Business Unit',
         ),
         'style' => 'secondary',
       ),
     ),
     'width' => 3,
   ),
  ),
  'roles' =>
  array (
    'admin' => 'full',
    'super_admin' => 'full',
    'organization_manager' => 'limited',
  ),
  'layout' =>
  array (
    'columns' => 12,
    'gutter' => 3,
  ),
);
