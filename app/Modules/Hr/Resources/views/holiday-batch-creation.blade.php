
<x-qf::navigation-layout
    configKey="hr.employee"
    context="time"
    moduleName="hr"
    :overrides="[
        'top_bar' => ['enabled' => false],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => false],
    ]"
>
    <livewire:qf.wizard configKey="hr.wizards.holiday_batch_creation" />
</x-qf::navigation-layout>