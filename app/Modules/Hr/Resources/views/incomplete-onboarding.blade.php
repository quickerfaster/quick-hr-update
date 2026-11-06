<x-qf::navigation-layout
    configKey="hr.dashboards.incomplete_onboarding"
    context="onboarding"
    moduleName="hr"
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]"
>
    <livewire:qf.dashboard config-key="hr.dashboards.incomplete_onboarding" />
</x-qf::navigation-layout>
