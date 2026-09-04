<?php

return array (
  'title' => 'Attendance Dashboard',
  'description' => 'Overview of attendance tracking, clock events, shifts, policies, and workforce availability',
  'widgets' =>
  array (
    0 =>
    array (
      'type' => 'stat',
      'title' => 'Present Today',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\Attendance',
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
      'model' => 'App\\Modules\\Attendance\\Models\\Attendance',
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
      'title' => 'Active Shifts',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\Shift',
      'icon' => 'fas fa-calendar-day',
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
    3 =>
    array (
      'type' => 'stat',
      'title' => 'Clock Events Today',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\ClockEvent',
      'icon' => 'fas fa-clock',
      'aggregate' => 'count',
      'conditions' =>
      array (
        0 =>
        array (
          0 => 'timestamp',
          1 => '>=',
          2 => 'today',
        ),
      ),
      'width' => 3,
    ),
    4 =>
    array (
      'type' => 'stat',
      'title' => 'Work Patterns',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\WorkPattern',
      'icon' => 'fas fa-calendar-week',
      'aggregate' => 'count',
      'width' => 3,
    ),
    5 =>
    array (
      'type' => 'stat',
      'title' => 'Attendance Policies',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\AttendancePolicy',
      'icon' => 'fas fa-gavel',
      'aggregate' => 'count',
      'width' => 3,
    ),
    6 =>
    array (
      'type' => 'stat',
      'title' => 'Pending Adjustments',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\AttendanceAdjustment',
      'icon' => 'fas fa-edit',
      'aggregate' => 'count',
      'width' => 3,
    ),
    7 =>
    array (
      'type' => 'stat',
      'title' => 'Active Shift Schedules',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\ShiftSchedule',
      'icon' => 'fas fa-calendar-check',
      'aggregate' => 'count',
      'conditions' =>
      array (
        0 =>
        array (
          0 => 'is_published',
          1 => '=',
          2 => true,
        ),
        1 =>
        array (
          0 => 'schedule_date',
          1 => '>=',
          2 => 'today',
        ),
      ),
      'width' => 3,
    ),
    8 =>
    array (
      'type' => 'chart',
      'title' => 'Attendance by Status',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\Attendance',
      'group_by' => 'status',
      'chart_type' => 'pie',
      'description' => 'Distribution of attendance records this month',
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
    9 =>
    array (
      'type' => 'chart',
      'title' => 'Clock Events by Type',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\ClockEvent',
      'group_by' => 'event_type',
      'chart_type' => 'bar',
      'description' => 'Volume of clock in/out activity',
      'aggregate' => 'count',
      'width' => 4,
    ),
    10 =>
    array (
      'type' => 'trend',
      'title' => 'Attendance Trend (Last 30 Days)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\Attendance',
      'group_by' => 'day',
      'icon' => 'fas fa-chart-line',
      'description' => 'Daily attendance count',
      'aggregate' => 'count',
      'date_field' => 'date',
      'period' => 30,
      'width' => 4,
    ),
    11 =>
    array (
      'type' => 'list',
      'title' => 'Recent Clock Events',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\ClockEvent',
      'icon' => 'fas fa-clock',
      'description' => 'Latest 5 clock events',
      'limit' => 5,
      'sort' =>
      array (
        0 => 'timestamp',
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
          'field' => 'event_type',
        ),
        2 =>
        array (
          'label' => 'Time',
          'field' => 'timestamp',
          'format' => 'datetime',
        ),
        3 =>
        array (
          'label' => 'Method',
          'field' => 'method',
        ),
      ),
      'width' => 4,
      'show_view_all' => true,
      'view_all_link' => '/attendance/clock-events',
    ),
    12 =>
    array (
      'type' => 'list',
      'title' => 'Recent Attendance Adjustments',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\AttendanceAdjustment',
      'icon' => 'fas fa-edit',
      'description' => 'Latest 5 adjustments',
      'limit' => 5,
      'sort' =>
      array (
        0 => 'adjusted_at',
        1 => 'desc',
      ),
      'columns' =>
      array (
        0 =>
        array (
          'label' => 'Adjusted By',
          'field' => 'adjusted_by',
        ),
        1 =>
        array (
          'label' => 'Status',
          'field' => 'adjusted_status',
        ),
        2 =>
        array (
          'label' => 'Net Hours',
          'field' => 'adjusted_net_hours',
        ),
        3 =>
        array (
          'label' => 'Reason',
          'field' => 'reason',
        ),
      ),
      'width' => 4,
      'show_view_all' => true,
      'view_all_link' => '/attendance/attendance-adjustments',
    ),
    13 =>
    array (
      'type' => 'list',
      'title' => 'Upcoming Shift Schedules',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\ShiftSchedule',
      'icon' => 'fas fa-calendar-check',
      'description' => 'Next 5 scheduled shifts',
      'limit' => 5,
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
          1 => '>=',
          2 => 'today',
        ),
        1 =>
        array (
          0 => 'is_published',
          1 => '=',
          2 => true,
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
      'width' => 4,
      'show_view_all' => true,
      'view_all_link' => '/attendance/shift-schedules',
    ),
    14 =>
   array (
     'type' => 'action_card',
     'title' => 'Clock In',
     'size' => 'col-12',
     'icon' => 'fas fa-sign-in-alt',
     'description' => 'Start your work session',
     'actions' =>
     array (
       0 =>
       array (
         'label' => 'Clock In',
         'event' => 'navigate',
         'params' =>
         array (
           'url' => '/attendance/clock-events',
         ),
         'style' => 'primary',
       ),
       1 =>
       array (
         'label' => 'Clock Out',
         'event' => 'navigate',
         'params' =>
         array (
           'url' => '/attendance/clock-events',
         ),
         'style' => 'secondary',
       ),
     ),
     'width' => 3,
   ),
    15 =>
   array (
     'type' => 'action_card',
     'title' => 'Record Attendance',
     'size' => 'col-12',
     'icon' => 'fas fa-user-check',
     'description' => 'Manually record attendance',
     'actions' =>
     array (
       0 =>
       array (
         'label' => 'Record',
         'event' => 'openDrawer',
         'params' =>
         array (
           'component' => 'qf.data-table-form',
           'params' =>
           array (
             'configKey' => 'attendance.attendance',
             'recordId' => null,
           ),
           'title' => 'Record Attendance',
         ),
         'style' => 'primary',
       ),
     ),
     'width' => 3,
   ),
    16 =>
    array (
      'type' => 'action_card',
      'title' => 'Create Shift',
      'size' => 'col-12',
      'icon' => 'fas fa-calendar-plus',
      'description' => 'Define a new shift template',
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
              'configKey' => 'attendance.shift',
              'recordId' => null,
            ),
            'title' => 'Create Shift',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    17 =>
   array (
     'type' => 'action_card',
     'title' => 'Add Work Pattern',
     'size' => 'col-12',
     'icon' => 'fas fa-calendar-week',
     'description' => 'Create a recurring work pattern',
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
             'configKey' => 'attendance.work_pattern',
             'recordId' => null,
           ),
           'title' => 'Add Work Pattern',
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
