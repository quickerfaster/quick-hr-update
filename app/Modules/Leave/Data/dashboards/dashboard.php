<?php

return array (
  'title' => 'Leave Dashboard',
  'description' => 'Overview of leave requests, balances, types, and approvals',
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
      'title' => 'Leave Balances',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Leave\\Models\\LeaveBalance',
      'icon' => 'fas fa-scale-balanced',
      'aggregate' => 'count',
      'width' => 3,
    ),
    4 =>
    array (
      'type' => 'stat',
      'title' => 'Rejected Requests',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Leave\\Models\\LeaveRequest',
      'icon' => 'fas fa-times-circle',
      'aggregate' => 'count',
      'conditions' =>
      array (
        0 =>
        array (
          0 => 'status',
          1 => '=',
          2 => 'Denied',
        ),
      ),
      'width' => 3,
    ),
    5 =>
    array (
      'type' => 'stat',
      'title' => 'Available Balances',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Leave\\Models\\LeaveBalance',
      'icon' => 'fas fa-balance-scale',
      'aggregate' => 'sum',
      'field' => 'balance',
      'width' => 3,
    ),
    6 =>
    array (
      'type' => 'chart',
      'title' => 'Leave Requests by Status',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Leave\\Models\\LeaveRequest',
      'group_by' => 'status',
      'chart_type' => 'pie',
      'description' => 'Distribution of requests across workflow states',
      'aggregate' => 'count',
      'width' => 4,
    ),
    7 =>
    array (
      'type' => 'chart',
      'title' => 'Leave by Type',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Leave\\Models\\LeaveRequest',
      'group_by' => 'leaveType.name',
      'chart_type' => 'bar',
      'description' => 'Number of requests per leave type',
      'aggregate' => 'count',
      'width' => 4,
    ),
    8 =>
    array (
      'type' => 'trend',
      'title' => 'Leave Requests Trend (Last 6 Months)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Leave\\Models\\LeaveRequest',
      'group_by' => 'month',
      'icon' => 'fas fa-chart-line',
      'description' => 'Monthly leave request volume',
      'aggregate' => 'count',
      'date_field' => 'created_at',
      'period' => 6,
      'width' => 4,
    ),
    9 =>
    array (
      'type' => 'list',
      'title' => 'Recent Leave Requests',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Leave\\Models\\LeaveRequest',
      'icon' => 'fas fa-calendar-alt',
      'description' => 'Latest 5 requests',
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
          'label' => 'Start',
          'field' => 'start_date',
          'format' => 'date',
        ),
        3 =>
        array (
          'label' => 'Status',
          'field' => 'status',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/leave/leave-hub?tab=all-requests',
    ),
    10 =>
    array (
      'type' => 'list',
      'title' => 'Upcoming Approved Leaves',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Leave\\Models\\LeaveRequest',
      'icon' => 'fas fa-calendar-check',
      'description' => 'Next 5 approved leaves',
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
      'view_all_link' => '/leave/leave-hub?tab=all-requests?filter[status]=Approved',
    ),
    11 =>
    array (
      'type' => 'action_card',
      'title' => 'Request Leave',
      'size' => 'col-12',
      'icon' => 'fas fa-plus-circle',
      'description' => 'Submit a new leave request',
      'actions' =>
      array (
        0 =>
        array (
          'label' => 'Request',
          'event' => 'openDrawer',
          'params' =>
          array (
            'component' => 'qf.data-table-form',
            'params' =>
            array (
              'configKey' => 'leave.leave_request',
              'recordId' => null,
            ),
            'title' => 'Request Leave',
          ),
          'style' => 'primary',
        ),
      ),
      'width' => 3,
    ),
    12 =>
    array (
      'type' => 'action_card',
      'title' => 'Add Leave Type',
      'size' => 'col-12',
      'icon' => 'fas fa-tags',
      'description' => 'Define a new leave category',
      'actions' =>
      array (
        0 =>
        array (
          'label' => 'Add',
          'event' => 'openDrawer',
          'params' =>
          array (
            'component' => 'qf.data-table-form',
            'params' =>
            array (
              'configKey' => 'leave.leave_type',
              'recordId' => null,
            ),
            'title' => 'Add Leave Type',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    13 =>
   array (
     'type' => 'action_card',
     'title' => 'Configure Approvers',
     'size' => 'col-12',
     'icon' => 'fas fa-user-check',
     'description' => 'Assign leave approval workflows',
     'actions' =>
     array (
       0 =>
       array (
         'label' => 'Add Approver',
         'event' => 'openDrawer',
         'params' =>
         array (
           'component' => 'qf.data-table-form',
           'params' =>
           array (
             'configKey' => 'leave.leave_approver',
             'recordId' => null,
           ),
           'title' => 'Add Leave Approver',
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
    'manager' => 'limited',
    'employee' => 'basic',
  ),
  'layout' =>
  array (
    'columns' => 12,
    'gutter' => 3,
  ),
);
