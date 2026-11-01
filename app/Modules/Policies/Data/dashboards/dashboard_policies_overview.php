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
            'type' => 'metric',
            'title' => 'Total Policies',
            'model' => \App\Modules\Policies\Models\CompanyPolicy::class,
            'aggregate' => 'count',
            'color' => 'primary',
            'icon' => 'fas fa-file-contract',
            'order' => 1,
        ],
        [
            'type' => 'metric',
            'title' => 'Published',
            'model' => \App\Modules\Policies\Models\CompanyPolicy::class,
            'aggregate' => 'count',
            'conditions' => [
                ['status', '=', 'published'],
            ],
            'color' => 'success',
            'icon' => 'fas fa-check-circle',
            'order' => 2,
        ],
        [
            'type' => 'metric',
            'title' => 'Requiring Acknowledgment',
            'model' => \App\Modules\Policies\Models\CompanyPolicy::class,
            'aggregate' => 'count',
            'conditions' => [
                ['requires_acknowledgment', '=', true],
                ['status', '=', 'published'],
            ],
            'color' => 'warning',
            'icon' => 'fas fa-clipboard-check',
            'order' => 3,
        ],
        [
            'type' => 'metric',
            'title' => 'Drafts',
            'model' => \App\Modules\Policies\Models\CompanyPolicy::class,
            'aggregate' => 'count',
            'conditions' => [
                ['status', '=', 'draft'],
            ],
            'color' => 'secondary',
            'icon' => 'fas fa-pencil-alt',
            'order' => 4,
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
            'order' => 10,
        ],
    ],
];
