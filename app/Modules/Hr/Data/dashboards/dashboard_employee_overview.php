<?php

return array (
  'title' => 'Employee Overview',
  'description' => 'Personal dashboard – your leave, attendance, documents, and key metrics at a glance',
  'widgets' => 
  array (
    0 => 
    array (
      'type' => 'stat',
      'title' => 'Tenure',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Employee',
      'icon' => 'fas fa-calendar-alt',
      'custom_value' => '{{ tenure_years }} years',
      'width' => 3,
    ),
    1 => 
    array (
      'type' => 'stat',
      'title' => 'Hours This Month',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Attendance',
      'icon' => 'fas fa-clock',
      'aggregate' => 'sum',
      'field' => 'net_hours',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'employee.employee_number',
          1 => '=',
          2 => '{{ employee_number }}',
        ),
        1 => 
        array (
          0 => 'date',
          1 => '>=',
          2 => 'first day of this month',
        ),
        2 => 
        array (
          0 => 'date',
          1 => '<=',
          2 => 'last day of this month',
        ),
      ),
      'width' => 3,
    ),
    2 => 
    array (
      'type' => 'stat',
      'title' => 'Remaining PTO',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\LeaveBalance',
      'icon' => 'fas fa-umbrella-beach',
      'aggregate' => 'sum',
      'field' => 'balance',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'employee_id',
          1 => '=',
          2 => '{{ employee_number }}',
        ),
        1 => 
        array (
          0 => 'leave_type_id',
          1 => '=',
          2 => 'default_annual',
        ),
      ),
      'width' => 3,
    ),
    3 => 
    array (
      'type' => 'stat',
      'title' => 'Pending Approvals',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\LeaveRequest',
      'icon' => 'fas fa-hourglass-half',
      'aggregate' => 'count',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'employee_id',
          1 => '=',
          2 => '{{ employee_number }}',
        ),
        1 => 
        array (
          0 => 'status',
          1 => '=',
          2 => 'Pending',
        ),
      ),
      'width' => 3,
    ),
    4 => 
    array (
      'type' => 'list',
      'title' => 'Upcoming Time Off',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\LeaveRequest',
      'icon' => 'fas fa-calendar-week',
      'description' => 'Future approved leave',
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
          0 => 'employee_id',
          1 => '=',
          2 => '{{ employee_number }}',
        ),
        1 => 
        array (
          0 => 'status',
          1 => '=',
          2 => 'Approved',
        ),
        2 => 
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
          'label' => 'Type',
          'field' => 'leaveType.name',
        ),
        1 => 
        array (
          'label' => 'Start',
          'field' => 'start_date',
          'format' => 'date',
        ),
        2 => 
        array (
          'label' => 'End',
          'field' => 'end_date',
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
      'view_all_link' => '/hr/leave-requests?employee={{ employee_number }}',
    ),
    5 => 
    array (
      'type' => 'list',
      'title' => 'Recent Attendance',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Attendance',
      'icon' => 'fas fa-user-check',
      'description' => 'Last 5 records',
      'limit' => 5,
      'sort' => 
      array (
        0 => 'date',
        1 => 'desc',
      ),
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'employee.employee_number',
          1 => '=',
          2 => '{{ employee_number }}',
        ),
      ),
      'columns' => 
      array (
        0 => 
        array (
          'label' => 'Date',
          'field' => 'date',
          'format' => 'date',
        ),
        1 => 
        array (
          'label' => 'Status',
          'field' => 'status',
        ),
        2 => 
        array (
          'label' => 'Net Hours',
          'field' => 'net_hours',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/hr/attendance?employee={{ employee_number }}',
    ),
    6 => 
    array (
      'type' => 'action_card',
      'title' => 'Quick Contact',
      'size' => 'col-12',
      'icon' => 'fas fa-address-card',
      'description' => '{{ employee_email }}<br>{{ employee_phone }}',
      'actions' => 
      array (
      ),
      'width' => 3,
    ),
    7 => 
    array (
      'type' => 'list',
      'title' => 'Recent Clock Events',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\ClockEvent',
      'icon' => 'fas fa-history',
      'description' => 'Latest clock events',
      'limit' => 5,
      'sort' => 
      array (
        0 => 'timestamp',
        1 => 'desc',
      ),
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'employee_id',
          1 => '=',
          2 => '{{ employee_number }}',
        ),
      ),
      'columns' => 
      array (
        0 => 
        array (
          'label' => 'Event',
          'field' => 'event_type',
        ),
        1 => 
        array (
          'label' => 'Time',
          'field' => 'timestamp',
          'format' => 'datetime',
        ),
        2 => 
        array (
          'label' => 'Method',
          'field' => 'method',
        ),
      ),
      'width' => 3,
      'show_view_all' => true,
      'view_all_link' => '/hr/clock-events?employee={{ employee_number }}',
    ),
    8 => 
    array (
      'type' => 'progress',
      'title' => 'Next Work Anniversary',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Employee',
      'icon' => 'fas fa-birthday-cake',
      'description' => '{{ days_until_anniversary }} days remaining',
      'current_value' => '{{ days_until_anniversary }}',
      'target_value' => 365,
      'width' => 3,
    ),
    9 => 
    array (
      'type' => 'list',
      'title' => 'Expiring Documents (Next 30 Days)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Document',
      'icon' => 'fas fa-file-exclamation',
      'description' => 'Documents requiring attention',
      'limit' => 5,
      'sort' => 
      array (
        0 => 'expiry_date',
        1 => 'asc',
      ),
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'employee_id',
          1 => '=',
          2 => '{{ employee_number }}',
        ),
        1 => 
        array (
          0 => 'expiry_date',
          1 => '>=',
          2 => 'today',
        ),
        2 => 
        array (
          0 => 'expiry_date',
          1 => '<=',
          2 => '+30 days',
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
          'label' => 'Type',
          'field' => 'type',
        ),
        2 => 
        array (
          'label' => 'Expiry Date',
          'field' => 'expiry_date',
          'format' => 'date',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/hr/documents?employee={{ employee_number }}&filter=expiring',
    ),
    10 => 
    array (
      'type' => 'activity_log',
      'title' => 'Recent Location Activity',
      'size' => 'col-12',
      'icon' => 'fas fa-history',
      'log_name' => 'hr.location',
      'limit' => 5,
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/admin/activity-logs?filters[log_name]=hr.location',
    ),
  ),
  'layout' => 
  array (
    'columns' => 12,
    'gutter' => 3,
  ),
);
