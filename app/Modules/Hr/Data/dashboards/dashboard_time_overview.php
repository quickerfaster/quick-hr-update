<?php

return array (
  'title' => 'Time & Attendance Overview',
  'description' => 'Monitor attendance tracking, shift schedules, holidays, and workforce availability',
  'widgets' => 
  array (
    0 => 
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
    1 => 
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
    2 => 
    array (
      'type' => 'stat',
      'title' => 'Pending Approvals',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Attendance',
      'icon' => 'fas fa-clock',
      'aggregate' => 'count',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'is_approved',
          1 => '=',
          2 => false,
        ),
        1 => 
        array (
          0 => 'date',
          1 => '>=',
          2 => 'first day of this month',
        ),
      ),
      'width' => 3,
    ),
    3 => 
    array (
      'type' => 'stat',
      'title' => 'Upcoming Holidays',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Holiday',
      'icon' => 'fas fa-gift',
      'aggregate' => 'count',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'date',
          1 => '>=',
          2 => 'today',
        ),
        1 => 
        array (
          0 => 'date',
          1 => '<=',
          2 => '+30 days',
        ),
        2 => 
        array (
          0 => 'is_active',
          1 => '=',
          2 => true,
        ),
      ),
      'width' => 3,
    ),
    4 => 
    array (
      'type' => 'chart',
      'title' => 'Attendance Status (This Month)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Attendance',
      'group_by' => 'status',
      'chart_type' => 'pie',
      'description' => 'Distribution of attendance records',
      'aggregate' => 'count',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'date',
          1 => '>=',
          2 => 'first day of this month',
        ),
      ),
      'width' => 4,
    ),
    5 => 
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
      'width' => 5,
    ),
    6 => 
    array (
      'type' => 'action_card',
      'title' => 'Quick Actions',
      'size' => 'col-12',
      'icon' => 'fas fa-clock',
      'description' => 'Clock in/out or manage schedules',
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
        1 => 
        array (
          'label' => 'Clock Out',
          'event' => 'openClockModal',
          'params' => 
          array (
            'action' => 'clock_out',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    7 => 
    array (
      'type' => 'list',
      'title' => 'Recent Attendance Records',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Attendance',
      'icon' => 'fas fa-calendar-alt',
      'description' => 'Latest 5 records',
      'limit' => 5,
      'sort' => 
      array (
        0 => 'date',
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
          'label' => 'Date',
          'field' => 'date',
          'format' => 'date',
        ),
        2 => 
        array (
          'label' => 'Status',
          'field' => 'status',
        ),
        3 => 
        array (
          'label' => 'Net Hours',
          'field' => 'net_hours',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/hr/attendances',
    ),
    8 => 
    array (
      'type' => 'list',
      'title' => 'Upcoming Holidays',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Holiday',
      'icon' => 'fas fa-calendar-week',
      'description' => 'Next 5 holidays',
      'limit' => 5,
      'sort' => 
      array (
        0 => 'date',
        1 => 'asc',
      ),
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'date',
          1 => '>=',
          2 => 'today',
        ),
        1 => 
        array (
          0 => 'is_active',
          1 => '=',
          2 => true,
        ),
      ),
      'columns' => 
      array (
        0 => 
        array (
          'label' => 'Holiday',
          'field' => 'name',
        ),
        1 => 
        array (
          'label' => 'Date',
          'field' => 'date',
          'format' => 'date',
        ),
        2 => 
        array (
          'label' => 'Type',
          'field' => 'holiday_type',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/hr/holidays',
    ),
    9 => 
    array (
      'type' => 'progress',
      'title' => 'Attendance Completion',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Attendance',
      'icon' => 'fas fa-chart-simple',
      'description' => '% of employees with attendance recorded today',
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
          1 => '!=',
          2 => 'absent',
        ),
      ),
      'target_model' => 'App\\Modules\\Hr\\Models\\Employee',
      'target_aggregate' => 'count',
      'width' => 3,
    ),
    10 => 
    array (
      'type' => 'list',
      'title' => 'Today\'s Shift Schedules',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\ShiftSchedule',
      'icon' => 'fas fa-calendar-check',
      'description' => 'Shifts scheduled for today',
      'limit' => 10,
      'sort' => 
      array (
        0 => 'schedule_date',
        1 => 'asc',
      ),
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'schedule_date',
          1 => '=',
          2 => 'today',
        ),
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
          'label' => 'Shift',
          'field' => 'shift.name',
        ),
        2 => 
        array (
          'label' => 'Status',
          'field' => 'status',
        ),
      ),
      'width' => 9,
      'show_view_all' => true,
      'view_all_link' => '/hr/shift-schedules',
    ),
    11 => 
    array (
      'type' => 'chart',
      'title' => 'Overtime vs Regular Hours (This Month)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Attendance',
      'chart_type' => 'bar',
      'description' => 'Comparison of regular and overtime hours',
      'group_by' => NULL,
      'aggregates' => 
      array (
        'regular_hours' => 'sum',
        'overtime_hours' => 'sum',
      ),
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'date',
          1 => '>=',
          2 => 'first day of this month',
        ),
      ),
      'width' => 5,
    ),
    12 => 
    array (
      'type' => 'list',
      'title' => 'Recent Shift Schedules',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\ShiftSchedule',
      'icon' => 'fas fa-calendar-alt',
      'description' => 'Latest 5 schedule assignments',
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
          'label' => 'Date',
          'field' => 'schedule_date',
          'format' => 'date',
        ),
        2 => 
        array (
          'label' => 'Shift',
          'field' => 'shift.name',
        ),
        3 => 
        array (
          'label' => 'Status',
          'field' => 'status',
        ),
      ),
      'width' => 7,
      'show_view_all' => true,
      'view_all_link' => '/hr/shift-schedules',
    ),
  ),
  'roles' => 
  array (
    'admin' => 'full',
    'manager' => 'limited',
    'payroll_officer' => 'limited',
    'employee' => 'basic',
  ),
  'layout' => 
  array (
    'columns' => 12,
    'gutter' => 3,
  ),
);
