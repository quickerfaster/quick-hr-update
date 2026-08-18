<?php

return array (
  'module' => 'hr',
  'context' => 'people',
  'configKey' => 'hr_employee',
  'title' => 'HR Compliance & Audit Report',
  'label' => 'Compliance & Audit',
  'type' => 'dashboard',
  'description' => 'Mandatory training completion, policy breaches, document expiry.',
  'widgets' => 
  array (
    0 => 
    array (
      'width' => 3,
      'type' => 'stat',
      'title' => 'Policy Breaches (Last 12 months)',
      'model' => 'App\\Modules\\Hr\\Models\\PolicyBreach',
      'aggregate' => 'count',
    ),
    1 => 
    array (
      'width' => 6,
      'type' => 'list',
      'title' => 'Expiring Documents (Next 30 days)',
      'model' => 'App\\Modules\\Hr\\Models\\Document',
      'limit' => 10,
      'sort' => 
      array (
        0 => 'expiry_date',
        1 => 'asc',
      ),
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'expiry_date',
          1 => '>=',
          2 => 'today',
        ),
        1 => 
        array (
          0 => 'expiry_date',
          1 => '<=',
          2 => '+30 days',
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
          'label' => 'Document Type',
          'field' => 'type',
        ),
        2 => 
        array (
          'label' => 'Expiry Date',
          'field' => 'expiry_date',
          'format' => 'date',
        ),
      ),
    ),
    2 => 
    array (
      'width' => 6,
      'type' => 'list',
      'title' => 'Missing Mandatory Trainings',
      'model' => 'App\\Modules\\Hr\\Models\\TrainingAssignment',
      'limit' => 10,
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'completed',
          1 => '=',
          2 => false,
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
          'label' => 'Training',
          'field' => 'training.name',
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
