<?php

return array (
  'id' => 'employee_self_service',
  'title' => 'Request Leave',
  'description' => 'Submit a new leave request with your available balances shown',
  'returnPath' => '',
  'steps' =>
  array (
    0 =>
    array (
      'title' => 'Request Leave',
      'model' => 'App\\Modules\\Leave\\Models\\LeaveRequest',
      'formComponent' => 'leave-wizard-form',
      'draftSuccessMessage' => 'Leave request saved as draft. You can resume it later from My Leave.',
      'groups' =>
      array (
        0 => 'request_details',
      ),
      'isLinkSource' => true,
      'customValidation' =>
      array (
        0 => 'checkLeaveBalance',
        1 => 'checkDateConflicts',
      ),
      'dynamicFields' =>
      array (
        'leave_type_id' =>
        array (
          'loadOptionsFrom' => 'getAvailableLeaveTypes',
        ),
        'start_date' =>
        array (
          'minDate' => 'today',
          'disableWeekends' => true,
          'highlightHolidays' => true,
          'showTeamAbsences' => true,
        ),
        'end_date' =>
        array (
          'minDate' => 'field:start_date',
          'disableWeekends' => true,
          'highlightHolidays' => true,
          'showTeamAbsences' => true,
        ),
      ),
    ),
    1 =>
    array (
      'title' => 'Review & Submit',
      'preview' =>
      array (
        'showBalance' => true,
        'showTeamCalendar' => false,
        'showApprovalPath' => true,
        'workflowKey' => 'leave_request',
        'balanceCallback' => 'App\\Modules\\Leave\\Services\\LeaveBalanceResolver::getBalance',
      ),
    ),
  ),
  'completion' =>
  array (
    'title' => 'Leave Request Submitted!',
    'message' => 'Your leave request has been submitted for approval. You\'ll be notified when it\'s reviewed.',
    'actions' =>
    array (
      0 =>
      array (
        'label' => 'View My Requests',
        'url' => '/hr/leave-hub?tab=my-leaves',
        'primary' => true,
      ),
      1 =>
      array (
        'label' => 'Request Another',
        'url' => '/hr/leave-hub?tab=apply',
      ),
      2 =>
      array (
        'label' => 'Team Calendar',
        'url' => '/hr/my-team-calendar',
      ),
    ),
  ),
  'linkFields' =>
  array (
    'userField' => 'employee_number',
    'databaseField' => 'leave_request_id',
  ),
  'models' =>
  array (
    'primary' => 'App\\Modules\\Leave\\Models\\LeaveRequest',
    'related' =>
    array (
    ),
  ),
);
