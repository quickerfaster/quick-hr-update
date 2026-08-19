<?php

return array (
  'title' => 'Holiday Dashboard',
  'description' => 'Overview of holiday calendars, upcoming holidays, and holiday distribution',
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
      'title' => 'Holiday Calendars',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Holiday\\Models\\HolidayCalendar',
      'icon' => 'fas fa-calendar',
      'aggregate' => 'count',
      'width' => 3,
    ),
    2 => 
    array (
      'type' => 'stat',
      'title' => 'Upcoming Holidays',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Holiday\\Models\\Holiday',
      'icon' => 'fas fa-gift',
      'aggregate' => 'count',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'date',
          1 => '>=',
          2 => 'today',
        ),
      ),
      'width' => 3,
    ),
    3 => 
    array (
      'type' => 'stat',
      'title' => 'Holidays This Month',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Holiday\\Models\\Holiday',
      'icon' => 'fas fa-calendar-check',
      'aggregate' => 'count',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'date',
          1 => '>=',
          2 => 'first day of this month',
        ),
        1 => 
        array (
          0 => 'date',
          1 => '<=',
          2 => 'last day of this month',
        ),
      ),
      'width' => 3,
    ),
    4 => 
    array (
      'type' => 'stat',
      'title' => 'Public Holidays',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Holiday\\Models\\Holiday',
      'icon' => 'fas fa-flag',
      'aggregate' => 'count',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'holiday_type',
          1 => '=',
          2 => 'public',
        ),
      ),
      'width' => 3,
    ),
    5 => 
    array (
      'type' => 'chart',
      'title' => 'Holidays by Calendar',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Holiday\\Models\\Holiday',
      'group_by' => 'calendar.name',
      'chart_type' => 'bar',
      'description' => 'Number of holidays per calendar',
      'aggregate' => 'count',
      'width' => 4,
    ),
    6 => 
    array (
      'type' => 'trend',
      'title' => 'Holidays by Month (Last 12 Months)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Holiday\\Models\\Holiday',
      'group_by' => 'month',
      'icon' => 'fas fa-chart-line',
      'description' => 'Monthly holiday count trend',
      'aggregate' => 'count',
      'date_field' => 'date',
      'period' => 12,
      'width' => 4,
    ),
    7 => 
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
    8 => 
    array (
      'type' => 'list',
      'title' => 'Upcoming Holidays',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Holiday\\Models\\Holiday',
      'icon' => 'fas fa-gift',
      'description' => 'Next 5 upcoming holidays',
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
      ),
      'columns' => 
      array (
        0 => 
        array (
          'label' => 'Holiday',
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
        3 => 
        array (
          'label' => 'Calendar',
          'field' => 'calendar.name',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/holiday/holidays',
    ),
    9 => 
    array (
      'type' => 'list',
      'title' => 'Recent Holiday Calendars',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Holiday\\Models\\HolidayCalendar',
      'icon' => 'fas fa-calendar',
      'description' => 'Latest 5 calendars',
      'limit' => 5,
      'sort' => 
      array (
        0 => 'created_at',
        1 => 'desc',
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
          'label' => 'Year',
          'field' => 'year',
        ),
        2 => 
        array (
          'label' => 'Country',
          'field' => 'country_code',
        ),
        3 => 
        array (
          'label' => 'Holidays',
          'field' => 'holiday_count',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/holiday/holiday-calendars',
    ),
    10 => 
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
          'label' => 'Add',
          'event' => 'navigate',
          'params' => 
          array (
            'url' => '/holiday/holidays/create',
          ),
          'style' => 'primary',
        ),
      ),
      'width' => 3,
    ),
    11 => 
    array (
      'type' => 'action_card',
      'title' => 'Create Calendar',
      'size' => 'col-12',
      'icon' => 'fas fa-calendar-plus',
      'description' => 'Set up a new holiday calendar',
      'actions' => 
      array (
        0 => 
        array (
          'label' => 'Create',
          'event' => 'navigate',
          'params' => 
          array (
            'url' => '/holiday/holiday-calendars/create',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    12 => 
    array (
      'type' => 'action_card',
      'title' => 'Batch Create Holidays',
      'size' => 'col-12',
      'icon' => 'fas fa-magic',
      'description' => 'Generate multiple holidays at once',
      'actions' => 
      array (
        0 => 
        array (
          'label' => 'Batch',
          'event' => 'navigate',
          'params' => 
          array (
            'url' => '/holiday/holiday-batch-creation',
          ),
          'style' => 'warning',
        ),
      ),
      'width' => 3,
    ),
  ),
  'roles' => 
  array (
    'admin' => 'full',
    'hr_manager' => 'full',
    'manager' => 'limited',
    'employee' => 'basic',
  ),
  'layout' => 
  array (
    'columns' => 12,
    'gutter' => 3,
  ),
);