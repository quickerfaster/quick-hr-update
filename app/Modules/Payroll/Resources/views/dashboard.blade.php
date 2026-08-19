<x-qf::navigation-layout 
    configKey="payroll.dashboards.dashboard" 
    context="dashboard" 
    moduleName="payroll" 
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => false],
    ]"
>
    <livewire:qf.dashboard config-key="payroll.dashboards.dashboard" />
</x-qf::navigation-layout>