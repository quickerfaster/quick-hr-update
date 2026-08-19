<x-qf::navigation-layout
    configKey="payroll.dashboards.dashboard_processing_overview"
    context="processing"
    moduleName="payroll"
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]"
>
    <livewire:qf.dashboard config-key="payroll.dashboards.dashboard_processing_overview" />
</x-qf::navigation-layout>
