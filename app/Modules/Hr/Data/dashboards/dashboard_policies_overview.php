<?php

return array (
  'title' => 'Policies & Work Patterns Overview',
  'description' => 'Manage attendance policies, work patterns, and organisational assignments',
  'widgets' => 
  array (
    0 => 
    array (
      'type' => 'stat',
      'title' => 'Active Policies',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\AttendancePolicy',
      'icon' => 'fas fa-gavel',
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
    1 => 
    array (
      'type' => 'stat',
      'title' => 'Active Work Patterns',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\WorkPattern',
      'icon' => 'fas fa-calendar-alt',
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
      'title' => 'Total Policy Assignments',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\PolicyAssignment',
      'icon' => 'fas fa-link',
      'aggregate' => 'count',
      'width' => 3,
    ),
    3 => 
    array (
      'type' => 'stat',
      'title' => 'Employees with Work Pattern',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\EmployeeWorkPattern',
      'icon' => 'fas fa-user-tag',
      'aggregate' => 'count',
      'distinct' => 'employee_id',
      'width' => 3,
    ),
    4 => 
    array (
      'type' => 'chart',
      'title' => 'Policies by Country',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\AttendancePolicy',
      'group_by' => 'country_code',
      'chart_type' => 'bar',
      'description' => 'Active policies per jurisdiction',
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
      'title' => 'Work Patterns by Type',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\WorkPattern',
      'group_by' => 'pattern_type',
      'chart_type' => 'pie',
      'description' => 'Distribution of pattern types',
      'aggregate' => 'count',
      'width' => 4,
    ),
    6 => 
    array (
      'type' => 'chart',
      'title' => 'Policy Assignments by Entity',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\PolicyAssignment',
      'group_by' => 'assignable_type',
      'chart_type' => 'bar',
      'description' => 'Where policies are applied',
      'aggregate' => 'count',
      'width' => 4,
    ),
    7 => 
    array (
      'type' => 'list',
      'title' => 'Recent Attendance Policies',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\AttendancePolicy',
      'icon' => 'fas fa-file-contract',
      'description' => 'Latest 5 policies added',
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
          'label' => 'Policy Name',
          'field' => 'name',
        ),
        1 => 
        array (
          'label' => 'Code',
          'field' => 'code',
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
      'view_all_link' => '/hr/attendance-policies',
    ),
    8 => 
    array (
      'type' => 'list',
      'title' => 'Top Work Patterns',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\WorkPattern',
      'icon' => 'fas fa-chart-simple',
      'description' => 'Most frequently assigned patterns',
      'limit' => 5,
      'sort' => 
      array (
        0 => 'assigned_employee_count',
        1 => 'desc',
      ),
      'columns' => 
      array (
        0 => 
        array (
          'label' => 'Pattern Name',
          'field' => 'name',
        ),
        1 => 
        array (
          'label' => 'Shift',
          'field' => 'shift.name',
        ),
        2 => 
        array (
          'label' => 'Type',
          'field' => 'pattern_type',
        ),
        3 => 
        array (
          'label' => 'Employees',
          'field' => 'assigned_employee_count',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/hr/work-patterns',
    ),
    9 => 
    array (
      'type' => 'action_card',
      'title' => 'Create New Policy',
      'size' => 'col-12',
      'icon' => 'fas fa-plus-circle',
      'description' => 'Define a new attendance policy',
      'actions' => 
      array (
        0 => 
        array (
          'label' => 'Create',
          'event' => 'openPolicyWizard',
          'params' => 
          array (
            'type' => 'attendance',
          ),
          'style' => 'primary',
        ),
      ),
      'width' => 3,
    ),
    10 => 
    array (
      'type' => 'action_card',
      'title' => 'Manage Assignments',
      'size' => 'col-12',
      'icon' => 'fas fa-tasks',
      'description' => 'Assign policies to entities',
      'actions' => 
      array (
        0 => 
        array (
          'label' => 'Go to Assignments',
          'event' => 'navigate',
          'params' => 
          array (
            'url' => '/hr/policy-assignments',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    11 => 
    array (
      'type' => 'action_card',
      'title' => 'Create Work Pattern',
      'size' => 'col-12',
      'icon' => 'fas fa-calendar-plus',
      'description' => 'Define a new work schedule',
      'actions' => 
      array (
        0 => 
        array (
          'label' => 'Create',
          'event' => 'openWorkPatternWizard',
          'params' => 
          array (
            'type' => 'pattern',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    12 => 
    array (
      'type' => 'action_card',
      'title' => 'Bulk Assign Patterns',
      'size' => 'col-12',
      'icon' => 'fas fa-users',
      'description' => 'Assign patterns to multiple employees',
      'actions' => 
      array (
        0 => 
        array (
          'label' => 'Bulk Assign',
          'event' => 'openBulkPatternAssignment',
          'params' => 
          array (
            'type' => 'bulk',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    13 => 
    array (
      'type' => 'trend',
      'title' => 'New Policy Assignments Trend',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\PolicyAssignment',
      'group_by' => 'month',
      'icon' => 'fas fa-chart-line',
      'description' => 'Last 6 months',
      'aggregate' => 'count',
      'date_field' => 'created_at',
      'period' => 6,
      'width' => 6,
    ),
    14 => 
    array (
      'type' => 'list',
      'title' => 'Upcoming Expiring Policies',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\AttendancePolicy',
      'icon' => 'fas fa-calendar-exclamation',
      'description' => 'Policies expiring in next 30 days',
      'limit' => 5,
      'sort' => 
      array (
        0 => 'expiration_date',
        1 => 'asc',
      ),
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'expiration_date',
          1 => '<=',
          2 => '+30 days',
        ),
        1 => 
        array (
          0 => 'expiration_date',
          1 => '>=',
          2 => 'today',
        ),
      ),
      'columns' => 
      array (
        0 => 
        array (
          'label' => 'Policy',
          'field' => 'name',
        ),
        1 => 
        array (
          'label' => 'Code',
          'field' => 'code',
        ),
        2 => 
        array (
          'label' => 'Expires',
          'field' => 'expiration_date',
          'format' => 'date',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/hr/attendance-policies?filter[expiration]=upcoming',
    ),
    15 => 
    array (
      'type' => 'progress',
      'title' => 'Work Pattern Coverage',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\EmployeeWorkPattern',
      'icon' => 'fas fa-calendar-check',
      'description' => 'Employees with active work pattern assignment',
      'aggregate' => 'count',
      'distinct' => 'employee_id',
      'target_model' => 'App\\Modules\\Hr\\Models\\Employee',
      'target_aggregate' => 'count',
      'width' => 3,
    ),
    16 => 
    array (
      'type' => 'list',
      'title' => 'Recent Employee Pattern Assignments',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\EmployeeWorkPattern',
      'icon' => 'fas fa-user-clock',
      'description' => 'Latest 5 assignments',
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
          'label' => 'Employee',
          'field' => 'employee.employee_number',
        ),
        1 => 
        array (
          'label' => 'Pattern',
          'field' => 'workPattern.name',
        ),
        2 => 
        array (
          'label' => 'Start Date',
          'field' => 'start_date',
          'format' => 'date',
        ),
        3 => 
        array (
          'label' => 'End Date',
          'field' => 'end_date',
          'format' => 'date',
        ),
      ),
      'width' => 9,
      'show_view_all' => true,
      'view_all_link' => '/hr/employee-work-patterns',
    ),
  ),
  'roles' => 
  array (
    'admin' => 'full',
    'hr_manager' => 'full',
    'hr_admin' => 'limited',
    'manager' => 'limited',
  ),
  'layout' => 
  array (
    'columns' => 12,
    'gutter' => 3,
  ),
);
