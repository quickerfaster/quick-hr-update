<x-qf::navigation-layout
    configKey="hr.employee"
    context="onboarding"
    moduleName="hr"
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]"
>
    <div class="mb-3">
        <h5 class="fw-bold">
            <i class="fas fa-clipboard-list me-2 text-warning"></i>Incomplete Onboarding
        </h5>
        <p class="text-muted small mb-0">
            Employees needing position or company assignment. Use the actions to complete their onboarding.
        </p>
    </div>

    <livewire:qf.data-table
        configKey="hr.employee"
        :queryFilters="[['onboarding_status', '!=', 'complete'], ['onboarding_status', '!=', null]]"
        :hiddenFields="['onTable' => ['onboarding_status']]"
        :moreActions="[
            [
                'title' => 'Assign Company',
                'icon' => 'fas fa-building',
                'route' => 'employees.edit',
                'params' => ['id' => '{id}'],
                'style' => 'danger',
                'condition' => ['onboarding_status' => ['company_pending']],
            ],
            [
                'title' => 'Add Job Info',
                'icon' => 'fas fa-briefcase',
                'route' => 'employees.edit',
                'params' => ['id' => '{id}'],
                'style' => 'warning',
                'condition' => ['onboarding_status' => ['position_pending']],
            ],
        ]"
        :controls="[
            'search' => true,
            'filterColumns' => true,
            'addButton' => false,
            'editable' => true,
            'showHideColumns' => true,
            'files' => ['export' => ['csv', 'pdf'], 'print' => true],
        ]"
    />
</x-qf::navigation-layout>
