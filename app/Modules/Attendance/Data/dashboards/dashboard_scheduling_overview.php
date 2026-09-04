<?php

return array (
  'title' => 'Scheduling Overview',
  'description' => 'Configure shifts, shift schedules, and employee work patterns',
  'widgets' =>
  array (
    0 =>
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
    1 =>
    array (
      'type' => 'stat',
      'title' => 'Published Shift Schedules',
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
      ),
      'width' => 3,
    ),
    2 =>
    array (
      'type' => 'stat',
      'title' => 'Active Work Patterns',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\WorkPattern',
      'icon' => 'fas fa-calendar-week',
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
      'title' => 'Employee Work Pattern Assignments',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\EmployeeWorkPattern',
      'icon' => 'fas fa-user-tag',
      'aggregate' => 'count',
      'width' => 3,
    ),
    4 =>
    array (
      'type' => 'chart',
      'title' => 'Work Patterns by Type',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\WorkPattern',
      'group_by' => 'pattern_type',
      'chart_type' => 'pie',
      'description' => 'Distribution of pattern types',
      'aggregate' => 'count',
      'width' => 4,
    ),
    5 =>
    array (
      'type' => 'chart',
      'title' => 'Shifts by Category',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\Shift',
      'group_by' => 'shift_category',
      'chart_type' => 'bar',
      'description' => 'Active shifts across categories',
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
    6 =>
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
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/attendance/shift-schedules',
    ),
    7 =>
    array (
      'type' => 'list',
      'title' => 'Recent Work Patterns',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\WorkPattern',
      'icon' => 'fas fa-calendar-week',
      'description' => 'Latest 5 work patterns',
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
          'label' => 'Pattern',
          'field' => 'name',
        ),
        1 =>
        array (
          'label' => 'Type',
          'field' => 'pattern_type',
        ),
        2 =>
        array (
          'label' => 'Shift',
          'field' => 'shift.name',
        ),
        3 =>
        array (
          'label' => 'Active',
          'field' => 'is_active',
          'format' => 'boolean',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/attendance/work-patterns',
    ),
    8 =>
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
          'style' => 'primary',
        ),
      ),
      'width' => 3,
    ),
    9 =>
   array (
     'type' => 'action_card',
     'title' => 'Add Work Pattern',
     'size' => 'col-12',
     'icon' => 'fas fa-calendar-plus',
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
