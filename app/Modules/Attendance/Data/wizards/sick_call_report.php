<?php

return array (
  'id' => 'sick_call_report',
  'title' => 'Report Sudden Absence',
  'description' => 'Quickly notify your manager of an unplanned absence',
  'returnPath' => '',
  'steps' => 
  array (
    0 => 
    array (
      'title' => 'Reason & Duration',
      'model' => 'App\\Modules\\Hr\\Models\\Attendance',
      'groups' => 
      array (
        0 => 'absence_details',
      ),
      'fields' => 
      array (
        0 => 
        array (
          'absence_type' => 
          array (
            'type' => 'select',
            'options' => 
            array (
              'sick_leave' => 'Sick Leave',
              'emergency' => 'Family Emergency',
              'personal' => 'Personal Matter',
              'other' => 'Other',
            ),
            'default' => 'sick_leave',
          ),
        ),
        1 => 
        array (
          'start_date' => 
          array (
            'type' => 'datepicker',
            'default' => 'today',
            'validation' => 
            array (
              0 => 'required|date|before_or_equal:today',
            ),
          ),
        ),
        2 => 
        array (
          'expected_duration' => 
          array (
            'type' => 'select',
            'options' => 
            array (
              1 => '1 day',
              2 => '2 days',
              3 => '3 days',
              '4+' => '4+ days',
              'uncertain' => 'Uncertain',
            ),
            'default' => 1,
          ),
        ),
        3 => 
        array (
          'absence_reason' => 
          array (
            'type' => 'textarea',
            'placeholder' => 'Brief explanation for your absence...',
            'validation' => 
            array (
              0 => 'required|string|min:10|max:500',
            ),
          ),
        ),
      ),
    ),
    1 => 
    array (
      'title' => 'Notification Preferences',
      'model' => NULL,
      'fields' => 
      array (
        0 => 
        array (
          'notify_manager' => 
          array (
            'type' => 'boolcheckbox',
            'default' => true,
            'label' => 'Notify manager immediately',
          ),
        ),
        1 => 
        array (
          'create_leave_request' => 
          array (
            'type' => 'boolcheckbox',
            'default' => true,
            'label' => 'Create draft leave request for later',
          ),
        ),
        2 => 
        array (
          'emergency_contact_notified' => 
          array (
            'type' => 'boolcheckbox',
            'default' => false,
            'label' => 'Emergency procedures followed (if applicable)',
          ),
        ),
      ),
    ),
  ),
  'completion' => 
  array (
    'title' => 'Absence Reported Successfully',
    'message' => 'Your manager has been notified. {create_leave_request ? \'A draft leave request has been created for you to complete later.\' : \'Please submit a formal leave request when possible.\'}',
    'actions' => 
    array (
      0 => 
      array (
        'label' => 'View My Attendance',
        'url' => '/hr/my-attendance',
        'primary' => true,
      ),
      1 => 
      array (
        'label' => 'Complete Leave Request',
        'url' => '/hr/leave-request',
        'condition' => ['create_leave_request' => [true]],
      ),
      2 => 
      array (
        'label' => 'Back to Dashboard',
        'url' => '/hr/dashboard',
      ),
    ),
  ),
  'linkFields' => 
  array (
    'userField' => 'employee_number',
    'databaseField' => 'employee_id',
  ),
  'roles' => 
  array (
    0 => 'employee',
  ),
  'models' => 
  array (
    'primary' => 'App\\Modules\\Hr\\Models\\Attendance',
    'related' => 
    array (
      0 => 'App\\Modules\\Hr\\Models\\LeaveRequest',
    ),
  ),
);
