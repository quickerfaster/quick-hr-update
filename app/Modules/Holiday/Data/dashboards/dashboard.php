<?php

return array (
  'title' => 'Holiday Dashboard',
  'description' => 'Overview of holiday calendars and upcoming holidays',
  'widgets' => 
  array (
    0 => 
    array (
      'type' => 'stat',
      'title' => 'Holiday Calendars',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Holiday\\Models\\HolidayCalendar',
      'icon' => 'fas fa-calendar',
      'aggregate' => 'count',
      'width' => 4,
    ),
    1 => 
    array (
      'type' => 'stat',
      'title' => 'Total Holidays',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Holiday\\Models\\Holiday',
      'icon' => 'fas fa-umbrella-beach',
      'aggregate' => 'count',
      'width' => 4,
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
      'width' => 4,
    ),
  ),
);