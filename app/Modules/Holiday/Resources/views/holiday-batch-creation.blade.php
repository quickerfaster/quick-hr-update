
<x-qf::navigation-layout
    configKey="holiday.employee"
    context="time"
    moduleName="holiday"
    :overrides="[
        'top_bar' => ['enabled' => false],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => false],
    ]"
>
    <livewire:qf.wizard configKey="holiday.wizards.holiday_batch_creation" />
</x-qf::navigation-layout>