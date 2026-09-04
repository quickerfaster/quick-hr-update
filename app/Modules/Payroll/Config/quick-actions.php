<?php

/**
 * Payroll Module — Quick Actions Configuration
 *
 * Registers payroll management actions for the command palette.
 * These actions appear when the user presses Cmd+K / Ctrl+K.
 */
return [
    'quick_actions' => [
        [
            'id'          => 'payroll.view_dashboard',
            'label'       => 'View Payroll Dashboard',
            'description' => 'Go to the payroll dashboard overview',
            'icon'        => 'fas fa-tachometer-alt',
            'action'      => 'navigate',
            'module'      => 'payroll',
            'keywords'    => ['dashboard', 'home', 'overview', 'payroll', 'salary', 'compensation'],
            'category'    => 'Dashboard',
            'route'       => '/payroll/dashboard-processing-overview',
            'roles'       => ['super_admin', 'company_admin'],
        ],
        [
            'id'          => 'payroll.run_payroll',
            'label'       => 'Run Payroll',
            'description' => 'Start a new payroll run',
            'icon'        => 'fas fa-file-invoice-dollar',
            'action'      => 'navigate',
            'module'      => 'payroll',
            'keywords'    => ['payroll', 'run', 'process', 'pay', 'salary', 'wages', 'compensation'],
            'category'    => 'Processing',
            'route'       => '/payroll/payroll-runs',
            'permission'  => 'view_payroll_run',
            'roles'       => ['super_admin', 'company_admin'],
        ],
        [
            'id'          => 'payroll.add_pay_schedule',
            'label'       => 'Add Pay Schedule',
            'description' => 'Create a new pay schedule for employees',
            'icon'        => 'fas fa-calendar-alt',
            'action'      => 'navigate',
            'module'      => 'payroll',
            'keywords'    => ['pay', 'schedule', 'add', 'create', 'new', 'frequency', 'period'],
            'category'    => 'Configuration',
            'route'       => '/payroll/pay-schedules',
            'permission'  => 'view_pay_schedule',
            'roles'       => ['super_admin', 'company_admin'],
        ],
        [
            'id'          => 'payroll.policies',
            'label'       => 'Payroll Policies',
            'description' => 'View and manage payroll policies and rules',
            'icon'        => 'fas fa-gavel',
            'action'      => 'navigate',
            'module'      => 'payroll',
            'keywords'    => ['policies', 'payroll', 'rules', 'policy', 'compliance', 'tax'],
            'category'    => 'Configuration',
            'route'       => '/payroll/payroll-policies',
            'permission'  => 'view_payroll_policy',
            'roles'       => ['super_admin', 'company_admin'],
        ],
        [
            'id'          => 'payroll.bank_files',
            'label'       => 'Bank Files',
            'description' => 'Export and manage bank payment files',
            'icon'        => 'fas fa-file-export',
            'action'      => 'navigate',
            'module'      => 'payroll',
            'keywords'    => ['bank', 'files', 'export', 'payment', 'bacs', 'nacha', 'sepa', 'transfer'],
            'category'    => 'Processing',
            'route'       => '/payroll/payroll-runs',
            'permission'  => 'view_payroll_run',
            'roles'       => ['super_admin', 'company_admin'],
        ],
    ],
];