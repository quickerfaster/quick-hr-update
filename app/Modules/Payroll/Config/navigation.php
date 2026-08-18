<?php

return [
    'context_groups' => [
        'payroll' => [
            'label' => 'Payroll',
            'icon' => 'fas fa-file-invoice-dollar',
            'order' => 999,
            'route' => NULL,
            'url' => 'payroll/dashboard-payroll-overview',
        ],
    ],
    'contexts' => [
        'payroll' => [
            [
                'key' => 'payroll_overview',
                'label' => 'Overview',
                'icon' => 'fas fa-chart-bar',
                'route' => '/payroll/dashboard-payroll-overview',
                'permission' => 'view_payroll_overview',
                'order' => 1,
                'page_title' => NULL,
            ],
            [
                'key' => 'pay_schedule',
                'label' => 'Pay Schedules',
                'icon' => 'fas fa-calendar-alt',
                'route' => '/payroll/pay-schedules',
                'permission' => 'view_pay_schedule',
                'order' => 2,
                'page_title' => NULL,
            ],
            [
                'key' => 'employee_payroll_profile',
                'label' => 'Employee Profiles',
                'icon' => 'fas fa-user-tie',
                'route' => '/payroll/employee-payroll-profiles',
                'permission' => 'view_employee_payroll_profile',
                'order' => 3,
                'page_title' => NULL,
            ],
            [
                'key' => 'payroll_run',
                'label' => 'Payroll Runs',
                'icon' => 'fas fa-file-invoice-dollar',
                'route' => '/payroll/payroll-runs',
                'permission' => 'view_payroll_run',
                'order' => 4,
                'page_title' => NULL,
            ],
            [
                'key' => 'payroll_payslip',
                'label' => 'Payslips',
                'icon' => 'fas fa-receipt',
                'route' => '/payroll/payroll-payslips',
                'permission' => 'view_payroll_payslip',
                'order' => 5,
                'page_title' => NULL,
            ],
            [
                'key' => 'payroll_policy',
                'label' => 'Payroll Policies',
                'icon' => 'fas fa-gavel',
                'route' => '/payroll/payroll-policies',
                'permission' => 'view_payroll_policy',
                'order' => 6,
                'page_title' => NULL,
            ],
            [
                'key' => 'payroll_run_adjustment',
                'label' => 'One‑Time Adjustments',
                'icon' => 'fas fa-edit',
                'route' => '/payroll/payroll-run-adjustments',
                'permission' => 'view_payroll_run_adjustment',
                'order' => 7,
                'page_title' => NULL,
            ],
            [
                'key' => 'employee_adjustment_profile',
                'label' => 'Recurring Adjustments',
                'icon' => 'fas fa-sync-alt',
                'route' => '/payroll/employee-adjustment-profiles',
                'permission' => 'view_employee_adjustment_profile',
                'order' => 8,
                'page_title' => NULL,
            ],
            [
                'key' => 'payslip_item',
                'label' => 'Payslip Items',
                'icon' => 'fas fa-list-ul',
                'route' => '/payroll/payslip-items',
                'permission' => 'view_payslip_item',
                'order' => 999,
                'page_title' => NULL,
            ],
            [
                'key' => 'payroll_policy_assignment',
                'label' => 'Policy Assignments',
                'icon' => 'fas fa-link',
                'route' => '/payroll/payroll-policy-assignments',
                'permission' => 'view_payroll_policy_assignment',
                'order' => 9,
                'page_title' => NULL,
            ],
        ],
    ],
    'layout' => [
        'top_bar' => [
            'enabled' => true,
        ],
        'context_menu' => [
            'type' => 'sidebar',
            'position' => 'left',
            'allow_switch' => true,
            'default_type' => 'sidebar',
        ],
        'sidebar' => [
            'initial_state' => 'full',
        ],
        'bottom_bar' => [
            'enabled' => true,
        ],
        'breadcrumb' => [
            'enabled' => true,
        ],
        'title' => [
            'enabled' => true,
        ],
    ],
    'shared_items' => [
        'header' => [],
        'footer' => [],
    ],
    'shared_top_items' => [
        'left' => [],
        'right' => [],
    ],
];