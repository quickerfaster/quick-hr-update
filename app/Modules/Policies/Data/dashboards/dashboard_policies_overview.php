<?php

/**
 * Dashboard Configuration: Policies Overview
 *
 * Admin compliance dashboard for company policies.
 * Shows acknowledgment rates, pending counts, and policy status overview.
 *
 * Config Key: policies.dashboards.dashboard_policies_overview
 */

return [
    'title' => 'Policies Overview',
    'description' => 'Company policy compliance and acknowledgment tracking',

    'widgets' => [
        [
            'type' => 'stat',
            'title' => 'Total Policies',
            'model' => \App\Modules\Policies\Models\CompanyPolicy::class,
            'aggregate' => 'count',
            'color' => 'primary',
            'icon' => 'fas fa-file-contract',
            'width' => 3,
        ],
        [
            'type' => 'stat',
            'title' => 'Published',
            'model' => \App\Modules\Policies\Models\CompanyPolicy::class,
            'aggregate' => 'count',
            'conditions' => [
                ['status', '=', 'published'],
            ],
            'color' => 'success',
            'icon' => 'fas fa-check-circle',
            'width' => 3,
        ],
        [
            'type' => 'stat',
            'title' => 'Requiring Acknowledgment',
            'model' => \App\Modules\Policies\Models\CompanyPolicy::class,
            'aggregate' => 'count',
            'conditions' => [
                ['requires_acknowledgment', '=', true],
                ['status', '=', 'published'],
            ],
            'color' => 'warning',
            'icon' => 'fas fa-clipboard-check',
            'width' => 3,
        ],
        [
            'type' => 'stat',
            'title' => 'Drafts',
            'model' => \App\Modules\Policies\Models\CompanyPolicy::class,
            'aggregate' => 'count',
            'conditions' => [
                ['status', '=', 'draft'],
            ],
            'color' => 'secondary',
            'icon' => 'fas fa-pencil-alt',
            'width' => 3,
        ],
        [
            'type' => 'stat',
            'title' => 'Acknowledgment Rate',
            'model' => \App\Modules\Policies\Models\PolicyAcknowledgment::class,
            'aggregate' => 'count',
            'color' => 'success',
            'icon' => 'fas fa-clipboard-check',
            'width' => 3,
        ],
        [
            'type' => 'stat',
            'title' => 'Pending Acknowledgments',
            'model' => \App\Modules\Policies\Models\PolicyAcknowledgment::class,
            'aggregate' => 'count',
            'conditions' => [
                ['status', '=', 'pending'],
            ],
            'color' => 'warning',
            'icon' => 'fas fa-hourglass-half',
            'width' => 3,
        ],
        [
            'type' => 'action_card',
            'title' => 'Create Policy',
            'icon' => 'fas fa-plus-circle',
            'color' => 'primary',
            'description' => 'Create a new company policy',
            'actions' => [
                [
                    'label' => 'Create',
                    'event' => 'openDrawer',
                    'params' => [
                        'component' => 'qf.data-table-form',
                        'params' => [
                            'configKey' => 'policies.company_policy',
                            'recordId' => null,
                        ],
                        'title' => 'Create Policy',
                    ],
                    'style' => 'primary',
                ],
            ],
            'width' => 3,
        ],
        [
            'type' => 'action_card',
            'title' => 'View Acknowledgments',
            'icon' => 'fas fa-clipboard-list',
            'color' => 'info',
            'description' => 'Review policy acknowledgment status',
            'actions' => [
                [
                    'label' => 'View',
                    'event' => 'navigate',
                    'params' => [
                        'url' => '/hr/policy-acknowledgments',
                    ],
                    'style' => 'info',
                ],
            ],
            'width' => 3,
        ],
        [
            'type' => 'chart',
            'title' => 'Policies by Category',
            'model' => \App\Modules\Policies\Models\CompanyPolicy::class,
            'group_by' => 'category',
            'chart_type' => 'bar',
            'description' => 'Policy distribution across categories',
            'aggregate' => 'count',
            'width' => 6,
        ],
        [
            'type' => 'list',
            'title' => 'Recent Policies',
            'model' => \App\Modules\Policies\Models\CompanyPolicy::class,
            'icon' => 'fas fa-list-alt',
            'description' => 'Latest 5 policies created',
            'limit' => 5,
            'sort' => ['created_at', 'desc'],
            'columns' => [
                ['label' => 'Title', 'field' => 'title'],
                ['label' => 'Category', 'field' => 'category'],
                ['label' => 'Status', 'field' => 'status'],
                ['label' => 'Version', 'field' => 'version'],
            ],
            'width' => 6,
        ],
    ],
];
