<?php

return array (
  'title' => 'Leave Management Dashboard',
  'description' => 'Overview of leave requests, balances, and approvals',
  'widgets' => 
  array (
    0 => 
    array (
      'type' => 'stat',
      'title' => 'Pending Requests',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Leave\\Models\\LeaveRequest',
      'icon' => 'fas fa-clock',
      'aggregate' => 'count',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'status',
          1 => '=',
          2 => 'Pending',
        ),
      ),
      'width' => 3,
    ),
    1 => 
    array (
      'type' => 'stat',
      'title' => 'Approved This Month',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Leave\\Models\\LeaveRequest',
      'icon' => 'fas fa-check-circle',
      'aggregate' => 'count',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'status',
          1 => '=',
          2 => 'Approved',
        ),
        1 => 
        array (
          0 => 'approved_at',
          1 => '>=',
          2 => 'first day of this month',
        ),
      ),
      'width' => 3,
    ),
    2 => 
    array (
      'type' => 'stat',
      'title' => 'Active Leave Types',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Leave\\Models\\LeaveType',
      'icon' => 'fas fa-tags',
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
    3 => 
    array (
      'type' => 'stat',
      'title' => 'Leave Approvers',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Leave\\Models\\LeaveApprover',
      'icon' => 'fas fa-user-shield',
      'aggregate' => 'count',
      'width' => 3,
    ),
  ),
);