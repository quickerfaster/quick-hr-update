<?php

/**
 * Incomplete Onboarding — Dedicated Dashboard
 *
 * Full-page version of the onboarding overview widget, showing all
 * employees with incomplete onboarding (not company_pending or
 * position_pending). Uses the same list widget type and drawer-based
 * row actions as the dashboard gadget.
 */
return [
    'title'       => 'Incomplete Onboarding',
    'description' => 'Employees needing position or company assignment',
    'widgets'     => [
        [
            'type'        => 'list',
            'title'       => 'Incomplete Onboarding',
            'size'        => 'col-12',
            'model'       => 'App\\Modules\\Hr\\Models\\Employee',
            'icon'        => 'fas fa-clipboard-list',
            'description' => 'Employees needing position or company assignment',
            'limit'       => 100,
            'sort'        => ['created_at', 'asc'],
            'conditions'  => [
                ['onboarding_status', '!=', 'complete'],
                ['onboarding_status', '!=', null],
            ],
            'columns'     => [
                ['label' => 'Name', 'field' => 'first_name'],
                ['label' => 'Email', 'field' => 'email'],
                ['label' => 'Status', 'field' => 'onboarding_status', 'format' => 'badge'],
            ],
            'width'        => 12,
            'show_view_all' => false,
            'id_field'     => 'user_id',
            'row_actions'  => [
                [
                    'label'  => 'Assign Company',
                    'icon'   => 'fas fa-building',
                    'event'  => 'openDrawer',
                    'params' => [
                        'component' => 'qf.user-company-assignment',
                        'params'    => ['user' => '{{ user_id }}'],
                        'title'     => 'Assign Company',
                    ],
                    'style'  => 'danger',
                ],
                [
                    'label'  => 'Add Job Info',
                    'icon'   => 'fas fa-briefcase',
                    'event'  => 'openDrawer',
                    'params' => [
                        'component' => 'qf.data-table-form',
                        'params'    => [
                            'configKey'     => 'hr.employee_position',
                            'recordId'      => '{{ employeePosition.id }}',
                            'prefilledData' => [
                                'employee_id' => '{{ id }}',
                                'company_id'  => '{{ company_id }}',
                            ],
                            'inline'        => true,
                        ],
                        'title'     => 'Add Job Details',
                    ],
                    'style'  => 'warning',
                ],
            ],
        ],
    ],
];
