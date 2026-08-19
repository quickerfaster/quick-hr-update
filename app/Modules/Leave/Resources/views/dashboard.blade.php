<x-qf::navigation-layout 
    configKey="leave.dashboards.dashboard" 
    context="dashboard" 
    moduleName="leave" 
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => false],
    ]"
>
    <livewire:qf.dashboard config-key="leave.dashboards.dashboard" />
</x-qf::navigation-layout>