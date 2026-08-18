<?php

return array (
  'module' => 'hr',
  'context' => 'time',
  'configKey' => 'hr_employee',
  'title' => 'Absence & Leave Tracking',
  'label' => 'Absence & Leave',
  'type' => 'dashboard',
  'description' => 'Absenteeism rate, unplanned vs planned leave, remaining balances.',
  'widgets' => 
  array (
    0 => 
    array (
      'width' => 3,
      'type' => 'absenteeism_rate',
      'title' => 'Absenteeism Rate (month)',
      'months' => 1,
      'end_date' => '',
    ),
    1 => 
    array (
      'width' => 6,
      'type' => 'trend',
      'title' => 'Absence Days Trend',
      'model' => 'App\\Modules\\Hr\\Models\\LeaveRequest',
      'group_by' => 'month',
      'aggregate' => 'sum',
      'field' => 'workdays_count',
      'date_field' => 'start_date',
      'period' => 12,
    ),
    2 => 
    array (
      'width' => 6,
      'type' => 'list',
      'title' => 'Employees with Low Leave Balance',
      'model' => 'App\\Modules\\Hr\\Models\\LeaveBalance',
      'limit' => 10,
      'sort' => 
      array (
        0 => 'balance',
        1 => 'asc',
      ),
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'balance',
          1 => '<',
          2 => 5,
        ),
      ),
      'columns' => 
      array (
        0 => 
        array (
          'label' => 'Employee',
          'field' => 'employee.full_name',
        ),
        1 => 
        array (
          'label' => 'Leave Type',
          'field' => 'leave_type.name',
        ),
        2 => 
        array (
          'label' => 'Remaining Days',
          'field' => 'balance',
        ),
      ),
    ),
  ),
  'layout' => 
  array (
    'columns' => 12,
    'gutter' => 3,
  ),
);
