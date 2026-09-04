<?php

return array (
  'title' => 'Organization Dashboard',
  'description' => 'Monitor companies, branches, departments, divisions, locations, teams, and business units across your organization',
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
      'title' => 'Total Departments',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Department',
      'icon' => 'fas fa-sitemap',
      'aggregate' => 'count',
      'width' => 3,
    ),
    3 =>
    array (
      'type' => 'stat',
      'title' => 'Total Locations',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Location',
      'icon' => 'fas fa-map-marker-alt',
      'aggregate' => 'count',
      'width' => 3,
    ),
    4 =>
    array (
      'type' => 'stat',
      'title' => 'Total Divisions',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Division',
      'icon' => 'fas fa-diagram-project',
      'aggregate' => 'count',
      'width' => 3,
    ),
    5 =>
    array (
      'type' => 'stat',
      'title' => 'Total Teams',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Team',
      'icon' => 'fas fa-users',
      'aggregate' => 'count',
      'width' => 3,
    ),
    6 =>
    array (
      'type' => 'stat',
      'title' => 'Business Units',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\BusinessUnit',
      'icon' => 'fas fa-briefcase',
      'aggregate' => 'count',
      'width' => 3,
    ),
    7 =>
    array (
      'type' => 'stat',
      'title' => 'Active Companies',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Company',
      'icon' => 'fas fa-check-circle',
      'aggregate' => 'count',
      'conditions' =>
      array (
        0 =>
        array (
          0 => 'is_active',
          1 => '=',
          2 => true,
        ),
      ),
      'width' => 3,
    ),
    8 =>
    array (
      'type' => 'stat',
      'title' => 'Active Branches',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Branch',
      'icon' => 'fas fa-check-double',
      'aggregate' => 'count',
      'conditions' =>
      array (
        0 =>
        array (
          0 => 'is_active',
          1 => '=',
          2 => true,
        ),
      ),
      'width' => 3,
    ),
    9 =>
    array (
      'type' => 'stat',
      'title' => 'Active Departments',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Department',
      'icon' => 'fas fa-layer-group',
      'aggregate' => 'count',
      'conditions' =>
      array (
        0 =>
        array (
          0 => 'is_active',
          1 => '=',
          2 => true,
        ),
      ),
      'width' => 3,
    ),
    10 =>
    array (
      'type' => 'stat',
      'title' => 'Active Locations',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Location',
      'icon' => 'fas fa-location-dot',
      'aggregate' => 'count',
      'conditions' =>
      array (
        0 =>
        array (
          0 => 'is_active',
          1 => '=',
          2 => true,
        ),
      ),
      'width' => 3,
    ),
    11 =>
    array (
      'type' => 'stat',
      'title' => 'Active Teams',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Team',
      'icon' => 'fas fa-people-group',
      'aggregate' => 'count',
      'conditions' =>
      array (
        0 =>
        array (
          0 => 'is_active',
          1 => '=',
          2 => true,
        ),
      ),
      'width' => 3,
    ),
    12 =>
    array (
      'type' => 'stat',
      'title' => 'New Companies (This Month)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Company',
      'icon' => 'fas fa-building-circle-check',
      'aggregate' => 'count',
      'conditions' =>
      array (
        0 =>
        array (
          0 => 'created_at',
          1 => '>=',
          2 => 'first day of this month',
        ),
      ),
      'width' => 3,
    ),
    13 =>
    array (
      'type' => 'chart',
      'title' => 'Companies by Status',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Company',
      'group_by' => 'status',
      'chart_type' => 'pie',
      'description' => 'Distribution of companies by status',
      'aggregate' => 'count',
      'width' => 4,
    ),
    14 =>
    array (
      'type' => 'chart',
      'title' => 'Departments by Status',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Department',
      'group_by' => 'is_active',
      'chart_type' => 'doughnut',
      'description' => 'Active vs. inactive departments',
      'aggregate' => 'count',
      'width' => 4,
    ),
    15 =>
    array (
      'type' => 'chart',
      'title' => 'Locations by Type',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Location',
      'group_by' => 'type',
      'chart_type' => 'pie',
      'description' => 'Distribution of locations by type',
      'aggregate' => 'count',
      'width' => 4,
    ),
    16 =>
    array (
      'type' => 'trend',
      'title' => 'Company Growth (Last 6 Months)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Company',
      'group_by' => 'month',
      'icon' => 'fas fa-chart-line',
      'description' => 'Companies added per month',
      'aggregate' => 'count',
      'date_field' => 'created_at',
      'period' => 6,
      'width' => 4,
    ),
    17 =>
    array (
      'type' => 'trend',
      'title' => 'Department Growth (Last 6 Months)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Department',
      'group_by' => 'month',
      'icon' => 'fas fa-chart-line',
      'description' => 'Departments added per month',
      'aggregate' => 'count',
      'date_field' => 'created_at',
      'period' => 6,
      'width' => 4,
    ),
    18 =>
    array (
      'type' => 'trend',
      'title' => 'Location Growth (Last 6 Months)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Location',
      'group_by' => 'month',
      'icon' => 'fas fa-chart-line',
      'description' => 'Locations added per month',
      'aggregate' => 'count',
      'date_field' => 'created_at',
      'period' => 6,
      'width' => 4,
    ),
    19 =>
    array (
      'type' => 'list',
      'title' => 'Recent Companies',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Company',
      'icon' => 'fas fa-building',
      'description' => 'Latest 5 companies',
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
          'label' => 'Status',
          'field' => 'status',
        ),
        3 =>
        array (
          'label' => 'Country',
          'field' => 'country_code',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/organization/companies',
    ),
    20 =>
    array (
      'type' => 'list',
      'title' => 'Recent Departments',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Department',
      'icon' => 'fas fa-sitemap',
      'description' => 'Latest 5 departments',
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
          'label' => 'Cost Center',
          'field' => 'cost_center',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/organization/departments',
    ),
    21 =>
    array (
      'type' => 'list',
      'title' => 'Recent Locations',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Location',
      'icon' => 'fas fa-map-marker-alt',
      'description' => 'Latest 5 locations',
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
          'label' => 'Type',
          'field' => 'type',
        ),
        2 =>
        array (
          'label' => 'City',
          'field' => 'city',
        ),
        3 =>
        array (
          'label' => 'Country',
          'field' => 'country_code',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/organization/locations',
    ),
    22 =>
    array (
      'type' => 'list',
      'title' => 'Recent Branches',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Branch',
      'icon' => 'fas fa-code-branch',
      'description' => 'Latest 5 branches',
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
          'label' => 'City',
          'field' => 'city',
        ),
        3 =>
        array (
          'label' => 'Country',
          'field' => 'country_code',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/organization/branches',
    ),
    23 =>
    array (
      'type' => 'list',
      'title' => 'Recent Teams',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Team',
      'icon' => 'fas fa-users',
      'description' => 'Latest 5 teams',
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
          'label' => 'Type',
          'field' => 'type',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/organization/teams',
    ),
    24 =>
    array (
      'type' => 'list',
      'title' => 'Recent Divisions',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Division',
      'icon' => 'fas fa-diagram-project',
      'description' => 'Latest 5 divisions',
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
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/organization/divisions',
    ),
    25 =>
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
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/organization/business-units',
    ),
    26 =>
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
    27 =>
    array (
      'type' => 'action_card',
      'title' => 'Manage Structure',
      'size' => 'col-12',
      'icon' => 'fas fa-sitemap',
      'description' => 'Configure departments and divisions',
      'actions' =>
      array (
        0 =>
        array (
          'label' => 'Go',
          'event' => 'openDrawer',
          'params' =>
          array (
            'component' => 'qf.data-table-form',
            'params' =>
            array (
              'configKey' => 'hr.department',
              'recordId' => null,
            ),
            'title' => 'Add Department',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    28 =>
   array (
     'type' => 'action_card',
     'title' => 'Add Location',
     'size' => 'col-12',
     'icon' => 'fas fa-location-plus',
     'description' => 'Register an office, warehouse, or remote site',
     'actions' =>
     array (
       0 =>
       array (
         'label' => 'Add',
         'event' => 'openDrawer',
         'params' =>
         array (
           'component' => 'qf.data-table-form',
           'params' =>
           array (
             'configKey' => 'hr.location',
             'recordId' => null,
           ),
           'title' => 'Add Location',
         ),
         'style' => 'primary',
       ),
     ),
     'width' => 3,
   ),
    29 =>
    array (
      'type' => 'action_card',
      'title' => 'Add Team',
      'size' => 'col-12',
      'icon' => 'fas fa-people-group',
      'description' => 'Create a team and assign it to a department',
      'actions' =>
      array (
        0 =>
        array (
          'label' => 'Add',
          'event' => 'openDrawer',
          'params' =>
          array (
            'component' => 'qf.data-table-form',
            'params' =>
            array (
              'configKey' => 'hr.team',
              'recordId' => null,
            ),
            'title' => 'Add Team',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    30 =>
   array (
     'type' => 'action_card',
     'title' => 'Add Branch',
     'size' => 'col-12',
     'icon' => 'fas fa-code-branch',
     'description' => 'Register a new company branch',
     'actions' =>
     array (
       0 =>
       array (
         'label' => 'Add',
         'event' => 'openDrawer',
         'params' =>
         array (
           'component' => 'qf.data-table-form',
           'params' =>
           array (
             'configKey' => 'organization.branch',
             'recordId' => null,
           ),
           'title' => 'Add Branch',
         ),
         'style' => 'primary',
       ),
     ),
     'width' => 3,
   ),
    31 =>
   array (
     'type' => 'action_card',
     'title' => 'Add Division',
     'size' => 'col-12',
     'icon' => 'fas fa-diagram-project',
     'description' => 'Create a new organizational division',
     'actions' =>
     array (
       0 =>
       array (
         'label' => 'Add',
         'event' => 'openDrawer',
         'params' =>
         array (
           'component' => 'qf.data-table-form',
           'params' =>
           array (
             'configKey' => 'organization.division',
             'recordId' => null,
           ),
           'title' => 'Add Division',
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
