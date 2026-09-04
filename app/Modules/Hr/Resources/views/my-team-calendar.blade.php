<x-qf::navigation-layout
    context="my-portal"
    moduleName="hr"
    configKey="hr.dashboards.dashboard_team_calendar"
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]"
>
    <livewire:qf.dashboard config-key="hr.dashboards.dashboard_team_calendar" />
</x-qf::navigation-layout>