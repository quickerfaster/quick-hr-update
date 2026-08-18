<x-qf::navigation-layout 
    configKey="hr.dashboards.dashboard" 
    context="dashboard" 
    moduleName="hr" 
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => false],
    ]"
>
    <livewire:qf.dashboard config-key="hr.dashboards.dashboard" />
</x-qf::navigation-layout>