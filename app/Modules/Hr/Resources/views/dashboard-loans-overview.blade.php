<x-qf::navigation-layout
    configKey="loans.dashboards.dashboard_loans_overview"
    context="loans"
    moduleName="hr"
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]"
>
    <livewire:qf.dashboard config-key="loans.dashboards.dashboard_loans_overview" />
</x-qf::navigation-layout>
