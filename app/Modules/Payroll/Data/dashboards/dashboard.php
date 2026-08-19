<?php

return array (
  'title' => 'Payroll Dashboard',
  'description' => 'Overview of payroll runs, pay schedules, policies, payslips, and payroll costs',
  'widgets' => 
  array (
    0 => 
    array (
      'type' => 'stat',
      'title' => 'Active Payroll Profiles',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Payroll\\Models\\EmployeePayrollProfile',
      'icon' => 'fas fa-user-tie',
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
    1 => 
    array (
      'type' => 'stat',
      'title' => 'Active Pay Schedules',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Payroll\\Models\\PaySchedule',
      'icon' => 'fas fa-calendar-alt',
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
      'title' => 'Payroll Policies',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Payroll\\Models\\PayrollPolicy',
      'icon' => 'fas fa-gavel',
      'aggregate' => 'count',
      'width' => 3,
    ),
    3 => 
    array (
      'type' => 'stat',
      'title' => 'Total Payroll Runs',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Payroll\\Models\\PayrollRun',
      'icon' => 'fas fa-file-invoice-dollar',
      'aggregate' => 'count',
      'width' => 3,
    ),
    4 => 
    array (
      'type' => 'stat',
      'title' => 'Draft Pay Runs',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Payroll\\Models\\PayrollRun',
      'icon' => 'fas fa-pencil-alt',
      'aggregate' => 'count',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'status',
          1 => '=',
          2 => 'draft',
        ),
      ),
      'width' => 3,
    ),
    5 => 
    array (
      'type' => 'stat',
      'title' => 'Paid Runs (This Month)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Payroll\\Models\\PayrollRun',
      'icon' => 'fas fa-check-circle',
      'aggregate' => 'count',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'status',
          1 => '=',
          2 => 'paid',
        ),
        1 => 
        array (
          0 => 'period_start',
          1 => '>=',
          2 => 'first day of this month',
        ),
      ),
      'width' => 3,
    ),
    6 => 
    array (
      'type' => 'stat',
      'title' => 'Total Payslips',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Payroll\\Models\\PayrollPayslip',
      'icon' => 'fas fa-receipt',
      'aggregate' => 'count',
      'width' => 3,
    ),
    7 => 
    array (
      'type' => 'stat',
      'title' => 'Payslips (This Month)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Payroll\\Models\\PayrollPayslip',
      'icon' => 'fas fa-file-invoice',
      'aggregate' => 'count',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'created_at',
          1 => '>=',
          2 => 'first day of this month',
        ),
      ),
      'width' => 3,
    ),
    8 => 
    array (
      'type' => 'chart',
      'title' => 'Payroll Runs by Status',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Payroll\\Models\\PayrollRun',
      'group_by' => 'status',
      'chart_type' => 'pie',
      'description' => 'Distribution of runs across workflow states',
      'aggregate' => 'count',
      'width' => 4,
    ),
    9 => 
    array (
      'type' => 'chart',
      'title' => 'Payroll Cost by Pay Schedule',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Payroll\\Models\\PayrollRun',
      'group_by' => 'paySchedule.name',
      'chart_type' => 'bar',
      'description' => 'Total cash required per pay schedule',
      'aggregate' => 'sum',
      'field' => 'total_cash_required',
      'width' => 4,
    ),
    10 => 
    array (
      'type' => 'trend',
      'title' => 'Payroll Cost Trend (Last 6 Months)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Payroll\\Models\\PayrollRun',
      'group_by' => 'month',
      'icon' => 'fas fa-chart-line',
      'description' => 'Monthly payroll cash required',
      'aggregate' => 'sum',
      'field' => 'total_cash_required',
      'date_field' => 'period_start',
      'period' => 6,
      'width' => 4,
    ),
    11 => 
    array (
      'type' => 'grouped_list',
      'title' => 'Payslips by Payment Status',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Payroll\\Models\\PayrollPayslip',
      'icon' => 'fas fa-tags',
      'description' => 'Payslip volume and net pay by payment status',
      'group_by' => 'payment_status',
      'aggregates' => 
      array (
        'id' => 'count',
        'net_pay' => 'sum',
      ),
      'columns' => 
      array (
        0 => 
        array (
          'label' => 'Status',
          'field' => 'group_label',
        ),
        1 => 
        array (
          'label' => 'Payslips',
          'field' => 'id_count',
          'format' => 'number',
        ),
        2 => 
        array (
          'label' => 'Net Pay',
          'field' => 'net_pay_sum',
          'format' => 'currency',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/payroll/payroll-payslips',
    ),
    12 => 
    array (
      'type' => 'list',
      'title' => 'Recent Payroll Runs',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Payroll\\Models\\PayrollRun',
      'icon' => 'fas fa-file-invoice-dollar',
      'description' => 'Latest 5 runs',
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
          'label' => 'Period Start',
          'field' => 'period_start',
          'format' => 'date',
        ),
        1 => 
        array (
          'label' => 'Period End',
          'field' => 'period_end',
          'format' => 'date',
        ),
        2 => 
        array (
          'label' => 'Status',
          'field' => 'status',
        ),
        3 => 
        array (
          'label' => 'Total Gross',
          'field' => 'total_gross_pay',
          'format' => 'currency',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/payroll/payroll-runs',
    ),
    13 => 
    array (
      'type' => 'list',
      'title' => 'Upcoming Pay Dates',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Payroll\\Models\\PaySchedule',
      'icon' => 'fas fa-calendar-alt',
      'description' => 'Next 5 scheduled pay days',
      'limit' => 5,
      'sort' => 
      array (
        0 => 'next_pay_date',
        1 => 'asc',
      ),
      'conditions' => 
      array (
        0 => 
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
          'label' => 'Schedule',
          'field' => 'name',
        ),
        1 => 
        array (
          'label' => 'Frequency',
          'field' => 'frequency',
        ),
        2 => 
        array (
          'label' => 'Next Pay Date',
          'field' => 'next_pay_date',
          'format' => 'date',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/payroll/pay-schedules',
    ),
    14 => 
    array (
      'type' => 'list',
      'title' => 'Recent Payslips',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Payroll\\Models\\PayrollPayslip',
      'icon' => 'fas fa-receipt',
      'description' => 'Last 5 generated payslips',
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
          'label' => 'Payslip #',
          'field' => 'payslip_number',
        ),
        1 => 
        array (
          'label' => 'Period End',
          'field' => 'payrollRun.period_end',
          'format' => 'date',
        ),
        2 => 
        array (
          'label' => 'Net Pay',
          'field' => 'net_pay',
          'format' => 'currency',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/payroll/payroll-payslips',
    ),
    15 => 
    array (
      'type' => 'action_card',
      'title' => 'Run Payroll',
      'size' => 'col-12',
      'icon' => 'fas fa-play-circle',
      'description' => 'Create a new payroll run',
      'actions' => 
      array (
        0 => 
        array (
          'label' => 'Create',
          'event' => 'openPayrollWizard',
          'params' => 
          array (
            'type' => 'new',
          ),
          'style' => 'primary',
        ),
      ),
      'width' => 3,
    ),
    16 => 
    array (
      'type' => 'action_card',
      'title' => 'Create Pay Schedule',
      'size' => 'col-12',
      'icon' => 'fas fa-calendar-plus',
      'description' => 'Set up a new pay frequency',
      'actions' => 
      array (
        0 => 
        array (
          'label' => 'Create',
          'event' => 'navigate',
          'params' => 
          array (
            'url' => '/pay-schedules/create',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    17 => 
    array (
      'type' => 'action_card',
      'title' => 'Add Payroll Policy',
      'size' => 'col-12',
      'icon' => 'fas fa-gavel',
      'description' => 'Define a new payroll rule',
      'actions' => 
      array (
        0 => 
        array (
          'label' => 'Add',
          'event' => 'navigate',
          'params' => 
          array (
            'url' => '/payroll-policies/create',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    18 => 
    array (
      'type' => 'action_card',
      'title' => 'Export Bank File',
      'size' => 'col-12',
      'icon' => 'fas fa-file-export',
      'description' => 'Generate ACH/SEPA file for approved runs',
      'actions' => 
      array (
        0 => 
        array (
          'label' => 'Export',
          'event' => 'openBankFileExport',
          'params' => 
          array (
            'run_status' => 'approved',
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
    'payroll_officer' => 'full',
    'manager' => 'limited',
  ),
  'layout' => 
  array (
    'columns' => 12,
    'gutter' => 3,
  ),
);
