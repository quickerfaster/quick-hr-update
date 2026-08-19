<x-qf::navigation-layout
    configKey="payroll.dashboards.dashboard_payroll_overview"
    context="payroll"
    moduleName="payroll"
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]"
>
    <livewire:qf.dashboard config-key="payroll.dashboards.dashboard_payroll_overview" />
</x-qf::navigation-layout>