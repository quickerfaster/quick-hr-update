<?php

return array (
  'title' => 'Leave Management Overview',
  'description' => 'Monitor leave requests, balances, approvals, and upcoming absences',
  'widgets' => 
  array (
    0 => 
    array (
      'type' => 'stat',
      'title' => 'Pending Requests',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\LeaveRequest',
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
      'model' => 'App\\Modules\\Hr\\Models\\LeaveRequest',
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
      'title' => 'Employees with Low Balance',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\LeaveBalance',
      'icon' => 'fas fa-exclamation-triangle',
      'aggregate' => 'count',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'balance',
          1 => '<',
          2 => 2,
        ),
        1 => 
        array (
          0 => 'leave_type_id',
          1 => '=',
          2 => 
          array (
            'subquery' => 'SELECT id FROM leave_types WHERE deducts_from_balance = 1 LIMIT 1',
          ),
        ),
      ),
      'width' => 3,
    ),
    3 => 
    array (
      'type' => 'stat',
      'title' => 'Active Leave Types',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\LeaveType',
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
    4 => 
    array (
      'type' => 'chart',
      'title' => 'Leave Requests by Status',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\LeaveRequest',
      'group_by' => 'status',
      'chart_type' => 'pie',
      'description' => 'Distribution across workflow states',
      'aggregate' => 'count',
      'width' => 4,
    ),
    5 => 
    array (
      'type' => 'chart',
      'title' => 'Approved Requests by Leave Type',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\LeaveRequest',
      'group_by' => 'leave_type_id',
      'chart_type' => 'bar',
      'description' => 'Most used leave categories',
      'aggregate' => 'count',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'status',
          1 => '=',
          2 => 'Approved',
        ),
      ),
      'width' => 4,
    ),
    6 => 
    array (
      'type' => 'trend',
      'title' => 'Leave Requests Trend (Last 6 Months)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\LeaveRequest',
      'group_by' => 'month',
      'icon' => 'fas fa-chart-line',
      'description' => 'Monthly request volume',
      'aggregate' => 'count',
      'date_field' => 'created_at',
      'period' => 6,
      'width' => 4,
    ),
    7 => 
    array (
      'type' => 'list',
      'title' => 'Recent Leave Requests',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\LeaveRequest',
      'icon' => 'fas fa-list-alt',
      'description' => 'Latest 5 requests submitted',
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
          'label' => 'Employee',
          'field' => 'employee.employee_number',
        ),
        1 => 
        array (
          'label' => 'Type',
          'field' => 'leaveType.name',
        ),
        2 => 
        array (
          'label' => 'Dates',
          'field' => 'start_date',
          'format' => 'date_range',
          'end_field' => 'end_date',
        ),
        3 => 
        array (
          'label' => 'Status',
          'field' => 'status',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/hr/leave-requests',
    ),
    8 => 
    array (
      'type' => 'list',
      'title' => 'Pending Approvals',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\LeaveRequest',
      'icon' => 'fas fa-user-check',
      'description' => 'Awaiting manager action',
      'limit' => 5,
      'sort' => 
      array (
        0 => 'created_at',
        1 => 'asc',
      ),
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'status',
          1 => '=',
          2 => 'Pending',
        ),
      ),
      'columns' => 
      array (
        0 => 
        array (
          'label' => 'Employee',
          'field' => 'employee.employee_number',
        ),
        1 => 
        array (
          'label' => 'Type',
          'field' => 'leaveType.name',
        ),
        2 => 
        array (
          'label' => 'Start',
          'field' => 'start_date',
          'format' => 'date',
        ),
        3 => 
        array (
          'label' => 'End',
          'field' => 'end_date',
          'format' => 'date',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/hr/leave-requests?status=Pending',
    ),
    9 => 
    array (
      'type' => 'action_card',
      'title' => 'Request Leave',
      'size' => 'col-12',
      'icon' => 'fas fa-calendar-plus',
      'description' => 'Submit a new leave request',
      'actions' => 
      array (
        0 => 
        array (
          'label' => 'Request',
          'event' => 'openLeaveWizard',
          'params' => 
          array (
            'type' => 'employee_self_service',
          ),
          'style' => 'primary',
        ),
      ),
      'width' => 3,
    ),
    10 => 
    array (
      'type' => 'action_card',
      'title' => 'My Leave Balance',
      'size' => 'col-12',
      'icon' => 'fas fa-scale-balanced',
      'description' => 'View your current leave balances',
      'actions' => 
      array (
        0 => 
        array (
          'label' => 'View',
          'event' => 'navigate',
          'params' => 
          array (
            'url' => '/hr/my-leave-balance',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    11 => 
    array (
      'type' => 'action_card',
      'title' => 'Manage Leave Types',
      'size' => 'col-12',
      'icon' => 'fas fa-tags',
      'description' => 'Configure leave categories',
      'actions' => 
      array (
        0 => 
        array (
          'label' => 'Manage',
          'event' => 'navigate',
          'params' => 
          array (
            'url' => '/hr/leave-types',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    12 => 
    array (
      'type' => 'action_card',
      'title' => 'Approval Rules',
      'size' => 'col-12',
      'icon' => 'fas fa-user-check',
      'description' => 'Define manager approvals',
      'actions' => 
      array (
        0 => 
        array (
          'label' => 'Configure',
          'event' => 'navigate',
          'params' => 
          array (
            'url' => '/hr/leave-approvers',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    13 => 
    array (
      'type' => 'list',
      'title' => 'Upcoming Leave (Next 30 Days)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\LeaveRequest',
      'icon' => 'fas fa-calendar-week',
      'description' => 'Approved leave starting soon',
      'limit' => 5,
      'sort' => 
      array (
        0 => 'start_date',
        1 => 'asc',
      ),
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
          0 => 'start_date',
          1 => '>=',
          2 => 'today',
        ),
        2 => 
        array (
          0 => 'start_date',
          1 => '<=',
          2 => '+30 days',
        ),
      ),
      'columns' => 
      array (
        0 => 
        array (
          'label' => 'Employee',
          'field' => 'employee.employee_number',
        ),
        1 => 
        array (
          'label' => 'Type',
          'field' => 'leaveType.name',
        ),
        2 => 
        array (
          'label' => 'Start',
          'field' => 'start_date',
          'format' => 'date',
        ),
        3 => 
        array (
          'label' => 'Days',
          'field' => 'workdays_count',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/hr/leave-requests?status=Approved&start_date=upcoming',
    ),
    14 => 
    array (
      'type' => 'list',
      'title' => 'Employees with Low Balance (<2 days)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\LeaveBalance',
      'icon' => 'fas fa-exclamation-triangle',
      'description' => 'Critical leave balance alert',
      'limit' => 5,
      'sort' => 
      array (
        0 => 'balance',
        1 => 'asc',
      ),
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'balance',
          1 => '<',
          2 => 2,
        ),
        1 => 
        array (
          0 => 'leave_type_id',
          1 => '=',
          2 => 
          array (
            'subquery' => 'SELECT id FROM leave_types WHERE deducts_from_balance = 1 LIMIT 1',
          ),
        ),
      ),
      'columns' => 
      array (
        0 => 
        array (
          'label' => 'Employee',
          'field' => 'employee.employee_number',
        ),
        1 => 
        array (
          'label' => 'Leave Type',
          'field' => 'leaveType.name',
        ),
        2 => 
        array (
          'label' => 'Balance',
          'field' => 'balance',
          'format' => 'decimal',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/hr/leave-balances?balance_low=true',
    ),
    15 => 
    array (
      'type' => 'progress',
      'title' => 'Leave Utilization',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\LeaveBalance',
      'icon' => 'fas fa-chart-pie',
      'description' => 'Average balance used vs total entitlement',
      'aggregate' => 'sum',
      'field' => 'balance',
      'target_model' => 'App\\Modules\\Hr\\Models\\LeaveBalance',
      'target_aggregate' => 'sum',
      'target_field' => 'balance',
      'target_conditions' => 
      array (
        0 => 
        array (
          0 => 'year',
          1 => '=',
          2 => 'currentYear',
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
