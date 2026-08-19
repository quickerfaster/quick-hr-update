<x-qf::navigation-layout 
    configKey="attendance.dashboards.dashboard" 
    context="dashboard" 
    moduleName="attendance" 
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => false],
    ]"
>
    <livewire:qf.dashboard config-key="attendance.dashboards.dashboard" />
</x-qf::navigation-layout>