<?php

return array (
  'id' => 'employee_onboarding',
  'title' => 'New Hire Onboarding',
  'description' => 'Set up core employee and job details to get started',
  'returnPath' => '/hr/employees',
  'steps' => 
  array (
    0 => 
    array (
      'title' => 'Personal & Employment',
      'model' => 'App\\Modules\\Hr\\Models\\Employee',
      'groups' => 
      array (
        0 => 'identity',
        1 => 'employment_details',
      ),
      'isLinkSource' => true,
    ),
    1 => 
    array (
      'title' => 'Job Setup',
      'model' => 'App\\Modules\\Hr\\Models\\EmployeePosition',
      'groups' => 
      array (
        0 => 'job_information',
        1 => 'employment_details',
        2 => 'compensation',
      ),
      'requiresLink' => true,
    ),
    2 => 
    array (
      'title' => 'Review & Confirm',
      'description' => 'Please review all information before finalizing',
      'model' => NULL,
    ),
  ),
  'completion' => 
  array (
    'title' => 'New Hire Added Successfully!',
    'message' => 'The employee record is now active. You can view details or add another hire.',
    'actions' => 
    array (
      0 => 
      array (
        'label' => 'View Employee',
        'url' => '/employees/{id}',
        'primary' => true,
      ),
      1 => 
      array (
        'label' => 'Upload Documents (Modal)',
        'event' => 'openAddModal',
        'eventParams' => 
        array (
          'configKey' => 'hr.document',
          'prefilledData' => 
          array (
            'employee_id' => '{id}',
            'department_id' => '{department_id}',
            'status' => 'pending',
            'document_type' => 'contract',
          ),
          'allowedGroups' => 
          array (
          ),
        ),
      ),
      2 => 
      array (
        'label' => 'Add Profile Data',
        'event' => 'openAddModal',
        'eventParams' => 
        array (
          'configKey' => 'hr.employee_profile',
          'prefilledData' => 
          array (
            'employee_id' => '{id}',
          ),
        ),
      ),
      3 => 
      array (
        'label' => 'Employee Directory',
        'url' => '/hr/employees',
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
    'primary' => 'App\\Modules\\Hr\\Models\\Employee',
    'related' => 
    array (
      0 => 'App\\Modules\\Hr\\Models\\EmployeePosition',
    ),
  ),
);
