<?php

return array (
  'title' => 'Organization Overview',
  'description' => 'Company structure, locations, departments, job titles, and key metrics',
  'widgets' =>
  array (
    0 =>
    array (
      'type' => 'stat',
      'title' => 'Total Locations',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Location',
      'icon' => 'fas fa-map-marker-alt',
      'aggregate' => 'count',
      'width' => 3,
    ),
    1 =>
    array (
      'type' => 'stat',
      'title' => 'Active Locations',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Location',
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
    2 =>
    array (
      'type' => 'stat',
      'title' => 'Departments',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Department',
      'icon' => 'fas fa-sitemap',
      'aggregate' => 'count',
      'width' => 3,
    ),
    3 =>
    array (
      'type' => 'stat',
      'title' => 'Job Titles',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\JobTitle',
      'icon' => 'fas fa-briefcase',
      'aggregate' => 'count',
      'width' => 3,
    ),
    4 =>
    array (
      'type' => 'chart',
      'title' => 'Locations by Country',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Location',
      'group_by' => 'country_code',
      'chart_type' => 'bar',
      'description' => 'Active locations per country',
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
      'width' => 4,
    ),
    5 =>
    array (
      'type' => 'chart',
      'title' => 'Locations by Type',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Location',
      'group_by' => 'is_remote',
      'chart_type' => 'pie',
      'description' => 'Physical vs. Remote locations',
      'aggregate' => 'count',
      'width' => 4,
    ),
    6 =>
    array (
      'type' => 'chart',
      'title' => 'Departments by Company',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Department',
      'group_by' => 'company_id',
      'chart_type' => 'bar',
      'description' => 'Department count per company',
      'aggregate' => 'count',
      'width' => 4,
    ),
    7 =>
    array (
      'type' => 'list',
      'title' => 'Recent Locations',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Location',
      'icon' => 'fas fa-building',
      'description' => 'Latest 5 added locations',
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
          'label' => 'City',
          'field' => 'city',
        ),
        2 =>
        array (
          'label' => 'Country',
          'field' => 'country_code',
        ),
        3 =>
        array (
          'label' => 'Status',
          'field' => 'is_active',
          'format' => 'boolean',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/hr/locations',
    ),
    8 =>
    array (
      'type' => 'list',
      'title' => 'Departments (A–Z)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Department',
      'icon' => 'fas fa-users',
      'description' => 'All departments alphabetically',
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
          'label' => 'Company',
          'field' => 'company.name',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/hr/departments',
    ),
    9 =>
    array (
      'type' => 'action_card',
      'title' => 'Add New Location',
      'size' => 'col-12',
      'icon' => 'fas fa-map-pin',
      'description' => 'Create a new office or branch',
      'actions' =>
      array (
        0 =>
        array (
          'label' => 'Create',
          'event' => 'openLocationWizard',
          'params' =>
          array (
            'type' => 'location',
          ),
          'style' => 'primary',
        ),
      ),
      'width' => 3,
    ),
    10 =>
    array (
      'type' => 'action_card',
      'title' => 'Add Department',
      'size' => 'col-12',
      'icon' => 'fas fa-folder-open',
      'description' => 'Create a new department',
      'actions' =>
      array (
        0 =>
        array (
          'label' => 'Create',
          'event' => 'openDepartmentWizard',
          'params' =>
          array (
            'type' => 'department',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    11 =>
    array (
      'type' => 'action_card',
      'title' => 'Manage Companies',
      'size' => 'col-12',
      'icon' => 'fas fa-building',
      'description' => 'Configure company entities',
      'actions' =>
      array (
        0 =>
        array (
          'label' => 'View',
          'event' => 'navigate',
          'params' =>
          array (
            'url' => '/hr/companies',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    12 =>
    array (
      'type' => 'action_card',
      'title' => 'Job Titles',
      'size' => 'col-12',
      'icon' => 'fas fa-tag',
      'description' => 'Define job roles and titles',
      'actions' =>
      array (
        0 =>
        array (
          'label' => 'Manage',
          'event' => 'navigate',
          'params' =>
          array (
            'url' => '/hr/job-titles',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    13 =>
    array (
      'type' => 'list',
      'title' => 'Active Companies',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Company',
      'icon' => 'fas fa-flag',
      'description' => 'Companies with active status',
      'limit' => 5,
      'sort' =>
      array (
        0 => 'name',
        1 => 'asc',
      ),
      'conditions' =>
      array (
        0 =>
        array (
          0 => 'status',
          1 => '=',
          2 => 'active',
        ),
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
          'label' => 'Level',
          'field' => 'level',
        ),
        2 =>
        array (
          'label' => 'Status',
          'field' => 'status',
          'format' => 'text',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/hr/companies',
    ),
    14 =>
    array (
      'type' => 'list',
      'title' => 'Recent Job Titles',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\JobTitle',
      'icon' => 'fas fa-briefcase',
      'description' => 'Latest job titles created',
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
          'label' => 'Title',
          'field' => 'title',
        ),
        1 =>
        array (
          'label' => 'Description',
          'field' => 'description',
          'truncate' => 50,
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/hr/job-titles',
    ),
    15 =>
    array (
      'type' => 'progress',
      'title' => 'Organization Setup Completion',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Location',
      'icon' => 'fas fa-check-double',
      'description' => 'Locations with complete address (street + city)',
      'aggregate' => 'count',
      'conditions' =>
      array (
        0 =>
        array (
          0 => 'address_line_1',
          1 => '!=',
          2 => NULL,
        ),
        1 =>
        array (
          0 => 'city',
          1 => '!=',
          2 => NULL,
        ),
      ),
      'target_model' => 'App\\Modules\\Hr\\Models\\Location',
      'target_aggregate' => 'count',
      'width' => 3,
    ),
  ),
  'roles' =>
  array (
    'admin' => 'full',
    'hr_manager' => 'full',
    'hr_admin' => 'limited',
  ),
  'layout' =>
  array (
    'columns' => 12,
    'gutter' => 3,
  ),
);
