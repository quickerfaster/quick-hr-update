<?php

return array (
  'id' => 'payroll_run_wizard',
  'title' => 'Process Payroll',
  'description' => 'Run payroll for a selected pay schedule',
  'returnPath' => '/hr/payroll-runs',
  'steps' => 
  array (
    0 => 
    array (
      'title' => 'Verification',
      'model' => 'App\\Modules\\Hr\\Models\\PayrollRun',
      'groups' => 
      array (
        0 => 'schedule_period',
      ),
      'isLinkSource' => true,
      'customValidation' => 
      array (
        0 => 'checkNoConflictingRun',
        1 => 'checkEmployeesHaveBankAccounts',
      ),
    ),
    1 => 
    array (
      'title' => 'Adjustments',
      'model' => NULL,
      'customComponent' => 'qf.payroll-wizard-adjustments',
      'description' => 'Add one‑time bonuses, deductions, or corrections',
    ),
    2 => 
    array (
      'title' => 'Review & Preview',
      'model' => NULL,
      'customComponent' => 'qf.payroll-wizard-preview',
      'description' => 'Verify all calculations before finalising',
    ),
  ),
  'completion' => 
  array (
    'title' => 'Payroll Run Ready for Review!',
    'message' => 'Payroll for {period_start} to {period_end} has been calculated. A manager must approve it before payments can be made.',
    'actions' => 
    array (
      0 => 
      array (
        'label' => 'View Payroll Run',
        'url' => '/hr/payroll-runs?id={id}',
        'primary' => true,
      ),
      1 => 
      array (
        'label' => 'Notify Manager',
        'event' => 'notifyManager',
        'eventParams' => 
        array (
          'payroll_run_id' => '{id}',
        ),
      ),
      2 => 
      array (
        'label' => 'Start Another Run',
        'url' => '/hr/payroll-wizard',
      ),
    ),
  ),
  'linkFields' => 
  array (
    'userField' => 'payroll_run_id',
    'databaseField' => 'payroll_run_id',
  ),
  'models' => 
  array (
    'primary' => 'App\\Modules\\Hr\\Models\\PayrollRun',
    'related' => 
    array (
    ),
  ),
);
