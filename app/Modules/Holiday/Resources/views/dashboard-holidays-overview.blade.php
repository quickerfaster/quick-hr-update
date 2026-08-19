<x-qf::navigation-layout
    configKey="holiday.dashboards.dashboard_holidays_overview"
    context="holidays"
    moduleName="holiday"
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]"
>
    <livewire:qf.dashboard config-key="holiday.dashboards.dashboard_holidays_overview" />
</x-qf::navigation-layout>