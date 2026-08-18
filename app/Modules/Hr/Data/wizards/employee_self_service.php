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
      'title' => 'Select Leave Type',
      'model' => 'App\\Modules\\Hr\\Models\\LeaveRequest',
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
        ),
        'end_date' => 
        array (
          'minDate' => 'field:start_date',
        ),
      ),
    ),
    1 => 
    array (
      'title' => 'Review & Submit',
      'description' => 'Review your leave request and available balance',
      'model' => NULL,
      'preview' => 
      array (
        'showBalance' => true,
        'showTeamCalendar' => false,
        'showApprovalPath' => true,
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
        'url' => '/hr/my-leaves',
        'primary' => true,
      ),
      1 => 
      array (
        'label' => 'Request Another',
        'url' => '/hr/leave-request',
      ),
      2 => 
      array (
        'label' => 'Team Calendar',
        'url' => '/hr/team-calendar',
      ),
    ),
  ),
  'linkFields' => 
  array (
    'userField' => 'employee_number',
    'databaseField' => 'employee_id',
  ),
  'models' => 
  array (
    'primary' => 'App\\Modules\\Hr\\Models\\LeaveRequest',
    'related' => 
    array (
    ),
  ),
);
