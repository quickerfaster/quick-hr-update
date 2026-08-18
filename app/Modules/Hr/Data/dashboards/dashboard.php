<?php

return array (
  'title' => 'HR Executive Dashboard',
  'description' => 'Cross‑module summary of workforce, attendance, leave, payroll, and policy metrics',
  'widgets' =>
  array (
    0 =>
    array (
      'type' => 'stat',
      'title' => 'Total Employees',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Employee',
      'icon' => 'fas fa-users',
      'aggregate' => 'count',
      'width' => 3,
    ),
    1 =>
    array (
      'type' => 'stat',
      'title' => 'Active Employees',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\EmployeePosition',
      'icon' => 'fas fa-user-check',
      'aggregate' => 'count',
      'conditions' =>
      array (
        0 =>
        array (
          0 => 'employment_status',
          1 => '=',
          2 => 'Active',
        ),
      ),
      'width' => 3,
    ),
    2 =>
    array (
      'type' => 'stat',
      'title' => 'New Hires (This Month)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Employee',
      'icon' => 'fas fa-user-plus',
      'aggregate' => 'count',
      'conditions' =>
      array (
        0 =>
        array (
          0 => 'hire_date',
          1 => '>=',
          2 => 'first day of this month',
        ),
      ),
      'width' => 3,
    ),
    3 =>
    array (
      'type' => 'stat',
      'title' => 'Departments',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Department',
      'icon' => 'fas fa-sitemap',
      'aggregate' => 'count',
      'width' => 3,
    ),
    4 =>
    array (
      'type' => 'stat',
      'title' => 'Job Titles',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\JobTitle',
      'icon' => 'fas fa-briefcase',
      'aggregate' => 'count',
      'width' => 3,
    ),
    5 =>
    array (
      'type' => 'stat',
      'title' => 'Pending Leave Requests',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\LeaveRequest',
      'icon' => 'fas fa-clock',
      'aggregate' => 'count',
      'conditions' =>
      array (
        0 =>
        array (
          0 => 'status',
          1 => '=',
          2 => 'Pending',
        ),
      ),
      'width' => 3,
    ),
    6 =>
    array (
      'type' => 'list',
      'title' => 'Recent Leave Requests',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\LeaveRequest',
      'icon' => 'fas fa-calendar-alt',
      'description' => 'Latest 5 requests',
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
          'label' => 'Type',
          'field' => 'leaveType.name',
        ),
        2 =>
        array (
          'label' => 'Dates',
          'field' => 'start_date',
          'format' => 'date_range',
          'end_field' => 'end_date',
        ),
        3 =>
        array (
          'label' => 'Status',
          'field' => 'status',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/hr/leave-requests',
    ),
    7 =>
    array (
      'type' => 'stat',
      'title' => 'Present Today',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Attendance',
      'icon' => 'fas fa-user-check',
      'aggregate' => 'count',
      'conditions' =>
      array (
        0 =>
        array (
          0 => 'date',
          1 => '=',
          2 => 'today',
        ),
        1 =>
        array (
          0 => 'status',
          1 => '=',
          2 => 'present',
        ),
      ),
      'width' => 3,
    ),
    8 =>
    array (
      'type' => 'stat',
      'title' => 'Absent Today',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Attendance',
      'icon' => 'fas fa-user-times',
      'aggregate' => 'count',
      'conditions' =>
      array (
        0 =>
        array (
          0 => 'date',
          1 => '=',
          2 => 'today',
        ),
        1 =>
        array (
          0 => 'status',
          1 => '=',
          2 => 'absent',
        ),
      ),
      'width' => 3,
    ),
    9 =>
    array (
      'type' => 'trend',
      'title' => 'Attendance Trend (Last 6 Months)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Attendance',
      'group_by' => 'month',
      'icon' => 'fas fa-chart-line',
      'description' => 'Monthly attendance count',
      'aggregate' => 'count',
      'date_field' => 'date',
      'period' => 6,
      'width' => 6,
    ),
    10 =>
    array (
      'type' => 'stat',
      'title' => 'Upcoming Pay Runs',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\PayrollRun',
      'icon' => 'fas fa-calendar-week',
      'aggregate' => 'count',
      'conditions' =>
      array (
        0 =>
        array (
          0 => 'status',
          1 => '=',
          2 => 'draft',
        ),
        1 =>
        array (
          0 => 'period_start',
          1 => '<=',
          2 => '+7 days',
        ),
      ),
      'width' => 3,
    ),
    11 =>
    array (
      'type' => 'list',
      'title' => 'Recent Payroll Runs',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\PayrollRun',
      'icon' => 'fas fa-file-invoice-dollar',
      'description' => 'Latest 5 runs',
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
          'label' => 'Period Start',
          'field' => 'period_start',
          'format' => 'date',
        ),
        1 =>
        array (
          'label' => 'Period End',
          'field' => 'period_end',
          'format' => 'date',
        ),
        2 =>
        array (
          'label' => 'Status',
          'field' => 'status',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/hr/payroll-runs',
    ),
    12 =>
    array (
      'type' => 'stat',
      'title' => 'Active Attendance Policies',
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
    13 =>
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
    14 =>
    array (
      'type' => 'stat',
      'title' => 'Active Locations',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Location',
      'icon' => 'fas fa-map-marker-alt',
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
    15 =>
    array (
      'type' => 'list',
      'title' => 'Top Locations by Employee Count',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Location',
      'icon' => 'fas fa-building',
      'description' => 'Locations with highest headcount',
      'limit' => 5,
      'sort' =>
      array (
        0 => 'employee_count',
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
      'view_all_link' => '/hr/locations',
    ),
    16 =>
    array (
      'type' => 'action_card',
      'title' => 'Request Leave',
      'size' => 'col-12',
      'icon' => 'fas fa-calendar-plus',
      'description' => 'Submit a new leave request',
      'actions' =>
      array (
        0 =>
        array (
          'label' => 'Request',
          'event' => 'openLeaveWizard',
          'params' =>
          array (
            'type' => 'employee_self_service',
          ),
          'style' => 'primary',
        ),
      ),
      'width' => 3,
    ),
    17 =>
    array (
      'type' => 'action_card',
      'title' => 'Process Payroll',
      'size' => 'col-12',
      'icon' => 'fas fa-calculator',
      'description' => 'Run monthly payroll',
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
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    18 =>
    array (
      'type' => 'action_card',
      'title' => 'Clock In/Out',
      'size' => 'col-12',
      'icon' => 'fas fa-clock',
      'description' => 'Record your attendance',
      'actions' =>
      array (
        0 =>
        array (
          'label' => 'Clock In',
          'event' => 'openClockModal',
          'params' =>
          array (
            'action' => 'clock_in',
          ),
          'style' => 'primary',
        ),
      ),
      'width' => 3,
    ),
    19 =>
    array (
      'type' => 'action_card',
      'title' => 'View My Team',
      'size' => 'col-12',
      'icon' => 'fas fa-users',
      'description' => 'Team calendar and approvals',
      'actions' =>
      array (
        0 =>
        array (
          'label' => 'Go',
          'event' => 'navigate',
          'params' =>
          array (
            'url' => '/hr/team-calendar',
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
    'hr_manager' => 'full',
    'hr_admin' => 'full',
    'manager' => 'limited',
  ),
  'layout' =>
  array (
    'columns' => 12,
    'gutter' => 3,
  ),
);
