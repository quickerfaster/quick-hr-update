<?php

return array (
  'module' => 'hr',
  'model' => 'App\\Modules\\Hr\\Models\\Employee',
  'context' => 'people',
  'configKey' => 'hr_employee',
  'title' => 'Employee Directory',
  'label' => 'Employee Directory',
  'type' => 'tabular',
  'description' => 'Complete list of all employees',
  'fields' => 
  array (
    0 => 'employee_number',
    1 => 'first_name',
    2 => 'last_name',
    3 => 'email',
    4 => 'hire_date',
  ),
  'filters' => 
  array (
  ),
  'default_sort' => 
  array (
    0 => 'last_name',
    1 => 'asc',
  ),
  'per_page' => 25,
);
