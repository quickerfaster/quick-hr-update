<?php

return array (
  'title' => 'People Management Overview',
  'description' => 'Key workforce metrics, hiring trends, and team analytics at a glance',
  'widgets' => 
  array (
    0 => 
    array (
      'type' => 'stat',
      'title' => 'Total Employees',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Employee',
      'icon' => 'fas fa-user-tie',
      'aggregate' => 'count',
      'width' => 3,
    ),
    1 => 
    array (
      'type' => 'stat',
      'title' => 'Employee Groups',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\EmployeeGroup',
      'icon' => 'fas fa-layer-group',
      'aggregate' => 'count',
      'width' => 3,
    ),
    2 => 
    array (
      'type' => 'stat',
      'title' => 'Active Teams',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Team',
      'icon' => 'fas fa-user-friends',
      'aggregate' => 'count',
      'where' => 
      array (
        'is_active' => true,
      ),
      'width' => 3,
    ),
    3 => 
    array (
      'type' => 'progress',
      'title' => 'Profile Completion',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\EmployeeProfile',
      'icon' => 'fas fa-id-card',
      'description' => 'Employees with complete profiles',
      'aggregate' => 'count',
      'target' => 100,
      'width' => 3,
    ),
    4 => 
    array (
      'type' => 'list',
      'title' => 'Recent Hires',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Employee',
      'icon' => 'fas fa-user-plus',
      'description' => 'Latest 5 employees joined',
      'limit' => 5,
      'sort' => 
      array (
        0 => 'hire_date',
        1 => 'desc',
      ),
      'columns' => 
      array (
        0 => 
        array (
          'label' => 'ID',
          'field' => 'employee_number',
          'format' => 'text',
        ),
        1 => 
        array (
          'label' => 'First Name',
          'field' => 'first_name',
          'format' => 'text',
        ),
        2 => 
        array (
          'label' => 'Last Name',
          'field' => 'last_name',
          'format' => 'text',
        ),
        3 => 
        array (
          'label' => 'Department',
          'field' => 'employeePosition.department.name',
          'format' => 'text',
        ),
        4 => 
        array (
          'label' => 'Hire Date',
          'field' => 'hire_date',
          'format' => 'date',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/hr/employees',
    ),
    5 => 
    array (
      'type' => 'trend',
      'title' => 'New Hires Trend (Last 6 Months)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Employee',
      'group_by' => 'month',
      'icon' => 'fas fa-chart-line',
      'description' => 'Monthly hiring count',
      'aggregate' => 'count',
      'date_field' => 'hire_date',
      'period' => 6,
      'width' => 6,
    ),
    6 => 
    array (
      'type' => 'chart',
      'title' => 'Employees by Department',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\EmployeePosition',
      'group_by' => 'department_id',
      'chart_type' => 'bar',
      'description' => 'Current headcount per department',
      'aggregate' => 'count',
      'width' => 6,
    ),
    7 => 
    array (
      'type' => 'action_card',
      'title' => 'Process Payroll',
      'size' => 'col-12',
      'icon' => 'fas fa-calculator',
      'description' => 'Run monthly payroll for all active employees',
      'actions' => 
      array (
        0 => 
        array (
          'label' => 'Start',
          'event' => 'openPayrollWizard',
          'params' => 
          array (
            'month' => 'current',
          ),
          'style' => 'primary',
        ),
      ),
      'width' => 6,
    ),
  ),
  'roles' => 
  array (
    'admin' => 'full',
    'manager' => 'limited',
    'user' => 'basic',
  ),
  'layout' => 
  array (
    'columns' => 12,
    'gutter' => 3,
  ),
);
