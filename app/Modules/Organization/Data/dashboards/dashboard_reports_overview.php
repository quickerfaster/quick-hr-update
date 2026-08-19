<?php

return array (
  'title' => 'Reports Overview',
  'description' => 'Access company, department, location, and growth reports',
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
      'title' => 'Total Departments',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Department',
      'icon' => 'fas fa-sitemap',
      'aggregate' => 'count',
      'width' => 3,
    ),
    2 =>
    array (
      'type' => 'stat',
      'title' => 'Total Locations',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Location',
      'icon' => 'fas fa-map-marker-alt',
      'aggregate' => 'count',
      'width' => 3,
    ),
    3 =>
    array (
      'type' => 'action_card',
      'title' => 'Company Reports',
      'size' => 'col-12',
      'icon' => 'fas fa-file-alt',
      'description' => 'View reports filtered by company',
      'actions' =>
      array (
        0 =>
        array (
          'label' => 'View',
          'event' => 'navigate',
          'params' =>
          array (
            'url' => '/organization/reports/companies',
          ),
          'style' => 'primary',
        ),
      ),
      'width' => 3,
    ),
    4 =>
    array (
      'type' => 'action_card',
      'title' => 'Department Reports',
      'size' => 'col-12',
      'icon' => 'fas fa-file-alt',
      'description' => 'View reports filtered by department',
      'actions' =>
      array (
        0 =>
        array (
          'label' => 'View',
          'event' => 'navigate',
          'params' =>
          array (
            'url' => '/organization/reports/departments',
          ),
          'style' => 'primary',
        ),
      ),
      'width' => 3,
    ),
    5 =>
    array (
      'type' => 'action_card',
      'title' => 'Location Reports',
      'size' => 'col-12',
      'icon' => 'fas fa-file-alt',
      'description' => 'View reports filtered by location',
      'actions' =>
      array (
        0 =>
        array (
          'label' => 'View',
          'event' => 'navigate',
          'params' =>
          array (
            'url' => '/organization/reports/locations',
          ),
          'style' => 'primary',
        ),
      ),
      'width' => 3,
    ),
    6 =>
    array (
      'type' => 'action_card',
      'title' => 'Growth Reports',
      'size' => 'col-12',
      'icon' => 'fas fa-chart-line',
      'description' => 'View organizational growth trends',
      'actions' =>
      array (
        0 =>
        array (
          'label' => 'View',
          'event' => 'navigate',
          'params' =>
          array (
            'url' => '/organization/reports/growth',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    7 =>
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
          'field' => 'is_active',
          'format' => 'boolean',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/organization/companies',
    ),
    8 =>
    array (
      'type' => 'list',
      'title' => 'Recent Locations',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Location',
      'icon' => 'fas fa-location-dot',
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
          'label' => 'City',
          'field' => 'city',
        ),
        2 =>
        array (
          'label' => 'Country',
          'field' => 'country_code',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/organization/locations',
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