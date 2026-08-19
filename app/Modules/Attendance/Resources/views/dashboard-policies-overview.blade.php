<x-qf::navigation-layout
    configKey="attendance.dashboards.dashboard_policies_overview"
    context="policies"
    moduleName="attendance"
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]"
>
    <livewire:qf.dashboard config-key="attendance.dashboards.dashboard_policies_overview" />
</x-qf::navigation-layout>