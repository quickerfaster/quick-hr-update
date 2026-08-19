
<x-qf::navigation-layout
    configKey="attendance.employee"
    context="self_service"
    moduleName="attendance"
    :overrides="[
        'top_bar' => ['enabled' => false],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => false],
    ]"
>
    <livewire:qf.wizard configKey="attendance.wizards.sick_call_report" />
</x-qf::navigation-layout>