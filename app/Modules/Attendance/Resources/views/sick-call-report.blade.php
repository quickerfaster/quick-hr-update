
<x-qf::navigation-layout
    configKey="hr.employee"
    context="self_service"
    moduleName="hr"
    :overrides="[
        'top_bar' => ['enabled' => false],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => false],
    ]"
>
    <livewire:qf.wizard configKey="hr.wizards.sick_call_report" />
</x-qf::navigation-layout>