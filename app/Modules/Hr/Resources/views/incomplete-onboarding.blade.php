<x-qf::navigation-layout
    configKey="hr.dashboards.incomplete_onboarding"
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
    <div class="mb-4">
        <h5 class="fw-bold">
            <i class="fas fa-clipboard-list me-2 text-warning"></i>Incomplete Onboarding
        </h5>
        <p class="text-muted small mb-0">
            Employees needing position or company assignment. Use the row actions to complete their onboarding.
        </p>
    </div>

    <livewire:qf.dashboard config-key="hr.dashboards.incomplete_onboarding" />
</x-qf::navigation-layout>
