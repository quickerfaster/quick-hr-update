<x-qf::navigation-layout 
    configKey="hr.dashboards.dashboard_leave_overview" 
    context="leave" 
    moduleName="hr" 
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]"
>
    <livewire:qf.dashboard config-key="hr.dashboards.dashboard_leave_overview" />
</x-qf::navigation-layout>