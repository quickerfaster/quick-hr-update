<?php

return array (
  'title' => 'Attendance Dashboard',
  'description' => 'Overview of attendance tracking, shifts, and workforce availability',
  'widgets' => 
  array (
    0 => 
    array (
      'type' => 'stat',
      'title' => 'Present Today',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\Attendance',
      'icon' => 'fas fa-user-check',
      'aggregate' => 'count',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'date',
          1 => '=',
          2 => 'today',
        ),
        1 => 
        array (
          0 => 'status',
          1 => '=',
          2 => 'present',
        ),
      ),
      'width' => 3,
    ),
    1 => 
    array (
      'type' => 'stat',
      'title' => 'Absent Today',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\Attendance',
      'icon' => 'fas fa-user-times',
      'aggregate' => 'count',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'date',
          1 => '=',
          2 => 'today',
        ),
        1 => 
        array (
          0 => 'status',
          1 => '=',
          2 => 'absent',
        ),
      ),
      'width' => 3,
    ),
    2 => 
    array (
      'type' => 'stat',
      'title' => 'Active Shifts',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\Shift',
      'icon' => 'fas fa-clock',
      'aggregate' => 'count',
      'width' => 3,
    ),
    3 => 
    array (
      'type' => 'stat',
      'title' => 'Work Patterns',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\WorkPattern',
      'icon' => 'fas fa-calendar-alt',
      'aggregate' => 'count',
      'width' => 3,
    ),
    4 => 
    array (
      'type' => 'stat',
      'title' => 'Attendance Policies',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\AttendancePolicy',
      'icon' => 'fas fa-gavel',
      'aggregate' => 'count',
      'width' => 3,
    ),
    5 => 
    array (
      'type' => 'stat',
      'title' => 'Pending Adjustments',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Attendance\\Models\\AttendanceAdjustment',
      'icon' => 'fas fa-edit',
      'aggregate' => 'count',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'status',
          1 => '=',
          2 => 'pending',
        ),
      ),
      'width' => 3,
    ),
  ),
);