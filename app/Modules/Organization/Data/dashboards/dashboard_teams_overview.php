<?php

return array (
  'title' => 'Teams Overview',
  'description' => 'Monitor teams, team membership, and cross-functional structures',
  'widgets' =>
  array (
    0 =>
    array (
      'type' => 'stat',
      'title' => 'Total Teams',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Team',
      'icon' => 'fas fa-users',
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
      'type' => 'chart',
      'title' => 'Teams by Department',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Team',
      'group_by' => 'department_id',
      'chart_type' => 'bar',
      'description' => 'Team count per department',
      'aggregate' => 'count',
      'width' => 4,
    ),
    3 =>
    array (
      'type' => 'list',
      'title' => 'Teams (A–Z)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Team',
      'icon' => 'fas fa-people-group',
      'description' => 'All teams alphabetically',
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
          'label' => 'Department',
          'field' => 'department.name',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/organization/teams',
    ),
    4 =>
    array (
      'type' => 'list',
      'title' => 'Recent Teams',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Organization\\Models\\Team',
      'icon' => 'fas fa-user-plus',
      'description' => 'Latest 5 created teams',
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
          'label' => 'Department',
          'field' => 'department.name',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/organization/teams',
    ),
    5 =>
    array (
      'type' => 'action_card',
      'title' => 'Add Team',
      'size' => 'col-12',
      'icon' => 'fas fa-plus-circle',
      'description' => 'Create a new team in the organization',
      'actions' =>
      array (
        0 =>
        array (
          'label' => 'Create',
          'event' => 'navigate',
          'params' =>
          array (
            'url' => '/organization/teams',
          ),
          'style' => 'primary',
        ),
      ),
      'width' => 3,
    ),
    6 =>
    array (
      'type' => 'action_card',
      'title' => 'Manage Departments',
      'size' => 'col-12',
      'icon' => 'fas fa-sitemap',
      'description' => 'Configure departments and structure',
      'actions' =>
      array (
        0 =>
        array (
          'label' => 'Go',
          'event' => 'navigate',
          'params' =>
          array (
            'url' => '/organization/departments',
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