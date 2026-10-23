<?php

return array (
  'id' => 'holiday_batch_creation',
  'title' => 'Batch Create Holidays',
  'description' => 'Create multiple holidays for a year or import from templates',
  'returnPath' => '/holiday/holidays',
  'steps' =>
  array (
    0 =>
    array (
      'title' => 'Select Calendar & Year',
      'customComponent' => 'holiday.batch-step-one',
      'description' => 'Choose the target calendar, year, and how you want to populate the holidays.',
    ),
    1 =>
    array (
      'title' => 'Batch Defaults',
      'customComponent' => 'holiday.batch-defaults',
      'description' => 'Optionally set default pay rate, business impact, and eligibility for all holidays in this batch.',
    ),
    2 =>
    array (
      'title' => 'Select Dates',
      'customComponent' => 'holiday.batch-date-selector',
      'description' => 'Optional — pick dates to cross-join with templates. Leave empty to use each template\'s own date.',
    ),
    3 =>
    array (
      'title' => 'Select Holidays',
      'customComponent' => 'holiday.template-selector',
      'description' => 'Choose which holidays to create. Filter by type and select individually or all at once.',
    ),
    4 =>
    array (
      'title' => 'Review & Confirm',
      'description' => 'Review the holidays that will be created. Edit or remove entries before confirming.',
      'customComponent' => 'holiday.batch-preview',
    ),
  ),
  'completion' =>
  array (
    'title' => 'Holidays Created Successfully!',
    'message' => 'The selected holidays have been added to the calendar. You can view them below or create another set.',
    'actions' =>
    array (
      0 =>
      array (
        'label' => 'View Calendar',
        'url' => '/holiday/holiday-calendars',
        'primary' => true,
      ),
      1 =>
      array (
        'label' => 'View Holidays',
        'url' => '/holiday/holidays',
      ),
      2 =>
      array (
        'label' => 'Create Another Set',
        'url' => '/holiday/holiday-batch-creation',
      ),
    ),
  ),
  'models' =>
  array (
    'primary' => 'App\\Modules\\Holiday\\Models\\Holiday',
    'related' =>
    array (
    ),
  ),
);
