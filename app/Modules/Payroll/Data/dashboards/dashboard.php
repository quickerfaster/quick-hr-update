<?php

return array (
  'title' => 'Payroll Dashboard',
  'description' => 'Overview of payroll runs, schedules, and employee payroll profiles',
  'widgets' => 
  array (
    0 => 
    array (
      'type' => 'stat',
      'title' => 'Active Payroll Profiles',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Payroll\\Models\\EmployeePayrollProfile',
      'icon' => 'fas fa-user-tie',
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
      'title' => 'Upcoming Pay Runs',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Payroll\\Models\\PayrollRun',
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
      ),
      'width' => 3,
    ),
    2 => 
    array (
      'type' => 'stat',
      'title' => 'Paid Runs (This Month)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Payroll\\Models\\PayrollRun',
      'icon' => 'fas fa-check-circle',
      'aggregate' => 'count',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'status',
          1 => '=',
          2 => 'paid',
        ),
        1 => 
        array (
          0 => 'period_start',
          1 => '>=',
          2 => 'first day of this month',
        ),
      ),
      'width' => 3,
    ),
    3 => 
    array (
      'type' => 'stat',
      'title' => 'Pay Schedules',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Payroll\\Models\\PaySchedule',
      'icon' => 'fas fa-calendar',
      'aggregate' => 'count',
      'width' => 3,
    ),
    4 => 
    array (
      'type' => 'stat',
      'title' => 'Payroll Policies',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Payroll\\Models\\PayrollPolicy',
      'icon' => 'fas fa-gavel',
      'aggregate' => 'count',
      'width' => 3,
    ),
  ),
);