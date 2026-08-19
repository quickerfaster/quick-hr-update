<x-qf::navigation-layout 
    configKey="leave.dashboards.dashboard_requests_overview" 
    context="requests" 
    moduleName="leave" 
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]"
>
    <livewire:qf.dashboard config-key="leave.dashboards.dashboard_requests_overview" />
</x-qf::navigation-layout>