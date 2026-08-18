<?php

return array (
  'title' => 'Payroll Overview',
  'description' => 'Monitor payroll runs, employee profiles, pay schedules, and financial metrics',
  'widgets' => 
  array (
    0 => 
    array (
      'type' => 'stat',
      'title' => 'Active Payroll Profiles',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\EmployeePayrollProfile',
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
      'title' => 'Upcoming Pay Runs',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\PayrollRun',
      'icon' => 'fas fa-calendar-week',
      'aggregate' => 'count',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'status',
          1 => '=',
          2 => 'draft',
        ),
        1 => 
        array (
          0 => 'period_start',
          1 => '<=',
          2 => '+7 days',
        ),
      ),
      'width' => 3,
    ),
    2 => 
    array (
      'type' => 'stat',
      'title' => 'Paid Runs (This Month)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\PayrollRun',
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
    3 => 
    array (
      'type' => 'stat',
      'title' => 'Employees Paid (This Month)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\PayrollPayslip',
      'icon' => 'fas fa-users',
      'aggregate' => 'count',
      'distinct' => 'employee_id',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'payroll_run_id',
          1 => 'in',
          2 => 
          array (
            'subquery' => 'SELECT id FROM payroll_runs WHERE status = \'Paid\' AND period_start >= DATE_FORMAT(NOW(),\'%Y-%m-01\')',
          ),
        ),
      ),
      'width' => 3,
    ),
    4 => 
    array (
      'type' => 'chart',
      'title' => 'Payroll Runs by Status',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\PayrollRun',
      'group_by' => 'status',
      'chart_type' => 'pie',
      'description' => 'Distribution of runs across workflow states',
      'aggregate' => 'count',
      'width' => 4,
    ),
    5 => 
    array (
      'type' => 'trend',
      'title' => 'Payroll Runs Trend (Last 6 Months)',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\PayrollRun',
      'group_by' => 'month',
      'icon' => 'fas fa-chart-line',
      'description' => 'Number of runs created per month',
      'aggregate' => 'count',
      'date_field' => 'period_start',
      'period' => 6,
      'width' => 5,
    ),
    6 => 
    array (
      'type' => 'list',
      'title' => 'Recent Payroll Runs',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\PayrollRun',
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
          'label' => 'Total Net',
          'field' => 'payslips_sum_net_pay',
          'aggregate' => 'sum',
          'format' => 'currency',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/hr/payroll-runs',
    ),
    7 => 
    array (
      'type' => 'list',
      'title' => 'Employees Missing Payroll Profile',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\Employee',
      'icon' => 'fas fa-user-plus',
      'description' => 'Active employees without payroll profile',
      'limit' => 5,
      'sort' => 
      array (
        0 => 'employee_number',
        1 => 'asc',
      ),
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'id',
          1 => 'not in',
          2 => 
          array (
            'subquery' => 'SELECT employee_id FROM employee_payroll_profiles',
          ),
        ),
      ),
      'columns' => 
      array (
        0 => 
        array (
          'label' => 'Employee #',
          'field' => 'employee_number',
        ),
        1 => 
        array (
          'label' => 'Name',
          'field' => 'first_name',
        ),
        2 => 
        array (
          'label' => 'Hire Date',
          'field' => 'hire_date',
          'format' => 'date',
        ),
      ),
      'width' => 6,
      'show_view_all' => true,
      'view_all_link' => '/hr/employee-payroll-profiles?filter[missing]=true',
    ),
    8 => 
    array (
      'type' => 'action_card',
      'title' => 'Start New Pay Run',
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
    9 => 
    array (
      'type' => 'action_card',
      'title' => 'Run Payroll Report',
      'size' => 'col-12',
      'icon' => 'fas fa-chart-bar',
      'description' => 'Generate detailed payroll summary',
      'actions' => 
      array (
        0 => 
        array (
          'label' => 'Report',
          'event' => 'openReportModal',
          'params' => 
          array (
            'report_type' => 'payroll_summary',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    10 => 
    array (
      'type' => 'action_card',
      'title' => 'Bulk Import Profiles',
      'size' => 'col-12',
      'icon' => 'fas fa-file-import',
      'description' => 'Import employee payroll data',
      'actions' => 
      array (
        0 => 
        array (
          'label' => 'Import',
          'event' => 'openImportModal',
          'params' => 
          array (
            'type' => 'payroll_profiles',
          ),
          'style' => 'secondary',
        ),
      ),
      'width' => 3,
    ),
    11 => 
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
    12 => 
    array (
      'type' => 'list',
      'title' => 'Upcoming Pay Dates',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\PaySchedule',
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
      'view_all_link' => '/hr/pay-schedules',
    ),
    13 => 
    array (
      'type' => 'list',
      'title' => 'Recent Payslips',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\PayrollPayslip',
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
          'label' => 'Employee',
          'field' => 'employee.employee_number',
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
      'view_all_link' => '/hr/payroll-payslips',
    ),
    14 => 
    array (
      'type' => 'progress',
      'title' => 'Payroll Completion Rate',
      'size' => 'col-12',
      'model' => 'App\\Modules\\Hr\\Models\\PayrollPayslip',
      'icon' => 'fas fa-check-double',
      'description' => 'Payslips generated for current month',
      'aggregate' => 'count',
      'distinct' => 'employee_id',
      'conditions' => 
      array (
        0 => 
        array (
          0 => 'payroll_run_id',
          1 => 'in',
          2 => 
          array (
            'subquery' => 'SELECT id FROM payroll_runs WHERE status = \'Paid\' AND period_start >= DATE_FORMAT(NOW(),\'%Y-%m-01\')',
          ),
        ),
      ),
      'target_model' => 'App\\Modules\\Hr\\Models\\EmployeePayrollProfile',
      'target_aggregate' => 'count',
      'target_conditions' => 
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
