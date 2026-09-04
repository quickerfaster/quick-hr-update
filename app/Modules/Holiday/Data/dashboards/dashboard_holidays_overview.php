<?php

return array (
  'title' => 'Holidays Overview',
  'description' => 'Monitor holidays, calendars, and upcoming events across the organization',
  'widgets' =>
  array (
    0 =>
    array (
      'type' => 'stat',
      'title' => 'Total Holidays',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Holiday\\Models\\Holiday',
      'icon' => 'fas fa-umbrella-beach',
      'aggregate' => 'count',
      'width' => 3,
    ),
    1 =>
    array (
      'type' => 'stat',
      'title' => 'Active Holidays',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Holiday\\Models\\Holiday',
      'icon' => 'fas fa-check-circle',
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
    2 =>
    array (
      'type' => 'stat',
      'title' => 'Holiday Calendars',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Holiday\\Models\\HolidayCalendar',
      'icon' => 'fas fa-calendar',
      'aggregate' => 'count',
      'width' => 3,
    ),
    3 =>
    array (
      'type' => 'stat',
      'title' => 'Upcoming (30 Days)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Holiday\\Models\\Holiday',
      'icon' => 'fas fa-calendar-week',
      'aggregate' => 'count',
      'conditions' =>
      array (
        0 =>
        array (
          0 => 'date',
          1 => '>=',
          2 => 'today',
        ),
        1 =>
        array (
          0 => 'date',
          1 => '<=',
          2 => '+30 days',
        ),
        2 =>
        array (
          0 => 'is_active',
          1 => '=',
          2 => true,
        ),
      ),
      'width' => 3,
    ),
    4 =>
    array (
      'type' => 'chart',
      'title' => 'Holidays by Type',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Holiday\\Models\\Holiday',
      'group_by' => 'holiday_type',
      'chart_type' => 'pie',
      'description' => 'Distribution of holiday types',
      'aggregate' => 'count',
      'width' => 4,
    ),
    5 =>
    array (
      'type' => 'chart',
      'title' => 'Holidays by Calendar',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Holiday\\Models\\Holiday',
      'group_by' => 'calendar_id',
      'chart_type' => 'bar',
      'description' => 'Holiday count per calendar',
      'aggregate' => 'count',
      'width' => 4,
    ),
    6 =>
    array (
      'type' => 'trend',
      'title' => 'Holidays Added (Last 6 Months)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Holiday\\Models\\Holiday',
      'group_by' => 'month',
      'icon' => 'fas fa-chart-line',
      'description' => 'Monthly holiday creation count',
      'aggregate' => 'count',
      'date_field' => 'created_at',
      'period' => 6,
      'width' => 4,
    ),
    7 =>
    array (
      'type' => 'list',
      'title' => 'Upcoming Holidays',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Holiday\\Models\\Holiday',
      'icon' => 'fas fa-calendar-week',
      'description' => 'Next 5 holidays',
      'limit' => 5,
      'sort' =>
      array (
        0 => 'date',
        1 => 'asc',
      ),
      'conditions' =>
      array (
        0 =>
        array (
          0 => 'date',
          1 => '>=',
          2 => 'today',
        ),
        1 =>
        array (
          0 => 'is_active',
          1 => '=',
          2 => true,
        ),
      ),
      'columns' =>
      array (
        0 =>
        array (
          'label' => 'Name',
          'field' => 'name',
        ),
        1 =>
        array (
          'label' => 'Date',
          'field' => 'date',
          'format' => 'date',
        ),
        2 =>
        array (
          'label' => 'Type',
          'field' => 'holiday_type',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/holiday/holidays',
    ),
    8 =>
    array (
      'type' => 'list',
      'title' => 'Holiday Calendars',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Holiday\\Models\\HolidayCalendar',
      'icon' => 'fas fa-calendar',
      'description' => 'All calendars alphabetically',
      'limit' => 5,
      'sort' =>
      array (
        0 => 'name',
        1 => 'asc',
      ),
      'columns' =>
      array (
        0 =>
        array (
          'label' => 'Name',
          'field' => 'name',
        ),
        1 =>
        array (
          'label' => 'Description',
          'field' => 'description',
          'truncate' => 40,
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/holiday/holiday-calendars',
    ),
    9 =>
   array (
     'type' => 'action_card',
     'title' => 'Add Holiday',
     'size' => 'col-12',
     'icon' => 'fas fa-plus-circle',
     'description' => 'Create a new holiday entry',
     'actions' =>
     array (
       0 =>
       array (
         'label' => 'Create',
         'event' => 'openDrawer',
         'params' =>
         array (
           'component' => 'qf.data-table-form',
           'params' =>
           array (
             'configKey' => 'holiday.holiday',
             'recordId' => null,
           ),
           'title' => 'Add Holiday',
         ),
         'style' => 'primary',
       ),
     ),
     'width' => 3,
   ),
    10 =>
    array (
      'type' => 'action_card',
      'title' => 'Batch Create',
      'size' => 'col-12',
      'icon' => 'fas fa-magic',
      'description' => 'Create multiple holidays at once',
      'actions' =>
      array (
        0 =>
        array (
          'label' => 'Create',
          'event' => 'navigate',
          'params' =>
          array (
            'url' => '/holiday/holiday-batch-creation',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    11 =>
    array (
      'type' => 'action_card',
      'title' => 'Manage Calendars',
      'size' => 'col-12',
      'icon' => 'fas fa-calendar-alt',
      'description' => 'Configure holiday calendars',
      'actions' =>
      array (
        0 =>
        array (
          'label' => 'Manage',
          'event' => 'openDrawer',
          'params' =>
          array (
            'component' => 'qf.data-table-form',
            'params' =>
            array (
              'configKey' => 'holiday.holiday_calendar',
              'recordId' => null,
            ),
            'title' => 'Create Calendar',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
  ),
  'roles' =>
  array (
    'admin' => 'full',
    'hr_manager' => 'full',
    'hr_admin' => 'limited',
    'manager' => 'limited',
  ),
  'layout' =>
  array (
    'columns' => 12,
    'gutter' => 3,
  ),
);
