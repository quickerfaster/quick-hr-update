<x-qf::navigation-layout 
    configKey="hr.dashboards.dashboard_policies_overview" 
    context="policies" 
    moduleName="hr" 
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]"
>
    <livewire:qf.dashboard config-key="hr.dashboards.dashboard_policies_overview" />
</x-qf::navigation-layout>