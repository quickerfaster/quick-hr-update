<?php

return [
    'title' => 'Manage Overview',
    'description' => 'Job titles, tags, job history, and documents — occasional HR management at a glance',
    'widgets' => [
        0 => [
            'type' => 'stat',
            'title' => 'Job Titles',
            'size' => 'col-12',
            'model' => 'App\\Modules\\Hr\\Models\\JobTitle',
            'icon' => 'fas fa-briefcase',
            'aggregate' => 'count',
            'width' => 3,
        ],
        1 => [
            'type' => 'stat',
            'title' => 'Tags',
            'size' => 'col-12',
            'model' => 'App\\Modules\\Hr\\Models\\Tag',
            'icon' => 'fas fa-tags',
            'aggregate' => 'count',
            'width' => 3,
        ],
        2 => [
            'type' => 'stat',
            'title' => 'Job History Records',
            'size' => 'col-12',
            'model' => 'App\\Modules\\Hr\\Models\\EmployeeJobHistory',
            'icon' => 'fas fa-history',
            'aggregate' => 'count',
            'width' => 3,
        ],
        3 => [
            'type' => 'stat',
            'title' => 'Documents',
            'size' => 'col-12',
            'model' => 'App\\Modules\\Hr\\Models\\Document',
            'icon' => 'fas fa-folder-open',
            'aggregate' => 'count',
            'width' => 3,
        ],
        4 => [
            'type' => 'list',
            'title' => 'Recent Job Titles',
            'size' => 'col-12',
            'model' => 'App\\Modules\\Hr\\Models\\JobTitle',
            'icon' => 'fas fa-briefcase',
            'description' => 'Latest job titles created',
            'limit' => 5,
            'sort' => [
                0 => 'created_at',
                1 => 'desc',
            ],
            'columns' => [
                0 => [
                    'label' => 'Title',
                    'field' => 'title',
                ],
                1 => [
                    'label' => 'Description',
                    'field' => 'description',
                    'truncate' => 50,
                ],
            ],
            'width' => 6,
            'show_view_all' => true,
            'view_all_link' => '/hr/job-titles',
        ],
        5 => [
            'type' => 'list',
            'title' => 'Tags (A–Z)',
            'size' => 'col-12',
            'model' => 'App\\Modules\\Hr\\Models\\Tag',
            'icon' => 'fas fa-tags',
            'description' => 'All tags alphabetically',
            'limit' => 5,
            'sort' => [
                0 => 'name',
                1 => 'asc',
            ],
            'columns' => [
                0 => [
                    'label' => 'Name',
                    'field' => 'name',
                ],
                1 => [
                    'label' => 'Slug',
                    'field' => 'slug',
                ],
                2 => [
                    'label' => 'Status',
                    'field' => 'is_active',
                    'format' => 'boolean',
                ],
            ],
            'width' => 6,
            'show_view_all' => true,
            'view_all_link' => '/hr/tags',
        ],
        6 => [
            'type' => 'list',
            'title' => 'Recent Job History',
            'size' => 'col-12',
            'model' => 'App\\Modules\\Hr\\Models\\EmployeeJobHistory',
            'icon' => 'fas fa-history',
            'description' => 'Latest job history entries',
            'limit' => 5,
            'sort' => [
                0 => 'effective_date',
                1 => 'desc',
            ],
            'columns' => [
                0 => [
                    'label' => 'Job Title',
                    'field' => 'job_title',
                ],
                1 => [
                    'label' => 'Department',
                    'field' => 'department',
                ],
                2 => [
                    'label' => 'Effective',
                    'field' => 'effective_date',
                    'format' => 'date',
                ],
                3 => [
                    'label' => 'Status',
                    'field' => 'employment_status',
                ],
            ],
            'width' => 6,
            'show_view_all' => true,
            'view_all_link' => '/hr/employee-job-histories',
        ],
        7 => [
            'type' => 'list',
            'title' => 'Recent Documents',
            'size' => 'col-12',
            'model' => 'App\\Modules\\Hr\\Models\\Document',
            'icon' => 'fas fa-folder-open',
            'description' => 'Latest documents uploaded',
            'limit' => 5,
            'sort' => [
                0 => 'created_at',
                1 => 'desc',
            ],
            'columns' => [
                0 => [
                    'label' => 'Name',
                    'field' => 'name',
                ],
                1 => [
                    'label' => 'Type',
                    'field' => 'type',
                ],
                2 => [
                    'label' => 'Expiry',
                    'field' => 'expiry_date',
                    'format' => 'date',
                ],
            ],
            'width' => 6,
            'show_view_all' => true,
            'view_all_link' => '/hr/documents',
        ],
        8 => [
            'type' => 'action_card',
            'title' => 'Job Titles',
            'size' => 'col-12',
            'icon' => 'fas fa-briefcase',
            'description' => 'Define job roles and titles',
            'actions' => [
                0 => [
                    'label' => 'Manage',
                    'event' => 'openDrawer',
                    'params' => [
                        'component' => 'qf.data-table-form',
                        'params' => [
                            'configKey' => 'hr.job_title',
                            'recordId' => null,
                        ],
                        'title' => 'Add Job Title',
                    ],
                    'style' => 'primary',
                ],
            ],
            'width' => 3,
        ],
        9 => [
            'type' => 'action_card',
            'title' => 'Tags',
            'size' => 'col-12',
            'icon' => 'fas fa-tags',
            'description' => 'Organize employees and records with tags',
            'actions' => [
                0 => [
                    'label' => 'Manage',
                    'event' => 'openDrawer',
                    'params' => [
                        'component' => 'qf.data-table-form',
                        'params' => [
                            'configKey' => 'hr.tag',
                            'recordId' => null,
                        ],
                        'title' => 'Add Tag',
                    ],
                    'style' => 'secondary',
                ],
            ],
            'width' => 3,
        ],
        10 => [
           'type' => 'action_card',
           'title' => 'Job History',
           'size' => 'col-12',
           'icon' => 'fas fa-history',
           'description' => 'Review employee job history records',
           'actions' => [
               0 => [
                   'label' => 'Add Entry',
                   'event' => 'openDrawer',
                   'params' => [
                       'component' => 'qf.data-table-form',
                       'params' => [
                           'configKey' => 'hr.employee_job_history',
                           'recordId' => null,
                       ],
                       'title' => 'Add Job History',
                   ],
                   'style' => 'secondary',
               ],
           ],
           'width' => 3,
       ],
    ],
    'roles' => [
        'admin' => 'full',
        'hr_manager' => 'full',
        'hr_admin' => 'limited',
    ],
    'layout' => [
        'columns' => 12,
        'gutter' => 3,
    ],
];
