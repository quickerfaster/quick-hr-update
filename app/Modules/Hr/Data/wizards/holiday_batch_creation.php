<?php

return array (
  'id' => 'holiday_batch_creation',
  'title' => 'Batch Create Holidays',
  'description' => 'Create multiple holidays for a year or import from templates',
  'returnPath' => '',
  'steps' => 
  array (
    0 => 
    array (
      'title' => 'Select Calendar & Year',
      'model' => 'App\\Modules\\Hr\\Models\\Holiday',
      'fields' => 
      array (
        0 => 
        array (
          'calendar_id' => 
          array (
            'type' => 'select',
            'foreign' => 
            array (
              'table' => 'holiday_calendars',
              'column' => 'id',
            ),
            'validation' => 
            array (
              0 => 'required',
            ),
            'label' => 'Holiday Calendar',
          ),
        ),
        1 => 
        array (
          'target_year' => 
          array (
            'type' => 'select',
            'options' => 
            array (
              0 => 2024,
              1 => 2025,
              2 => 2026,
              3 => 2027,
              4 => 2028,
            ),
            'validation' => 
            array (
              0 => 'required',
            ),
            'label' => 'Target Year',
          ),
        ),
        2 => 
        array (
          'source_option' => 
          array (
            'type' => 'select',
            'options' => 
            array (
              'empty' => 'Start Fresh',
              'copy_previous' => 'Copy from Previous Year',
              'template' => 'Use Country Template',
              'import' => 'Import CSV File',
            ),
            'default' => 'copy_previous',
            'label' => 'Creation Method',
          ),
        ),
      ),
    ),
    1 => 
    array (
      'title' => 'Select Holidays',
      'model' => NULL,
      'dynamic' => true,
      'fields' => 
      array (
        0 => 
        array (
          'selected_holidays' => 
          array (
            'type' => 'checklist',
            'source' => 'getAvailableHolidayTemplates',
            'label' => 'Select Holidays to Create',
          ),
        ),
      ),
    ),
    2 => 
    array (
      'title' => 'Review & Confirm',
      'description' => 'Review the holidays that will be created',
      'model' => NULL,
      'preview' => 
      array (
        'showCalendarPreview' => true,
        'showImpactAnalysis' => true,
      ),
    ),
  ),
  'completion' => 
  array (
    'title' => 'Holidays Created Successfully!',
    'message' => '{count} holidays have been added to the calendar for {year}.',
    'actions' => 
    array (
      0 => 
      array (
        'label' => 'View Calendar',
        'url' => '/hr/holiday-calendars/{calendar_id}',
        'primary' => true,
      ),
      1 => 
      array (
        'label' => 'View Holidays',
        'url' => '/hr/holidays?calendar_id={calendar_id}&year={year}',
      ),
      2 => 
      array (
        'label' => 'Create Another Set',
        'url' => '/hr/holidays/batch-create',
      ),
    ),
  ),
  'models' => 
  array (
    'primary' => 'App\\Modules\\Hr\\Models\\Holiday',
    'related' => 
    array (
    ),
  ),
);
