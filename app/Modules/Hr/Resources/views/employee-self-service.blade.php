
<x-qf::navigation-layout
    configKey="hr.employee"
    context="my-portal"
    moduleName="hr"
    :overrides="[
        'top_bar' => ['enabled' => false],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => false],
    ]"
>
    <livewire:qf.wizard configKey="leave.wizards.employee_self_service" />
</x-qf::navigation-layout>
