<x-qf::navigation-layout 
    configKey="holiday.dashboards.dashboard" 
    context="dashboard" 
    moduleName="holiday" 
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => false],
    ]"
>
    <livewire:qf.dashboard config-key="holiday.dashboards.dashboard" />
</x-qf::navigation-layout>