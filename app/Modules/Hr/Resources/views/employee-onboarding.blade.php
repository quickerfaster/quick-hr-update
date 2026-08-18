
<x-qf::navigation-layout
    configKey="hr.employee"
    context="people"
    moduleName="hr"
    :overrides="[
        'top_bar' => ['enabled' => false],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => false],
    ]"
>
    <livewire:qf.wizard configKey="hr.wizards.employee_onboarding" />
</x-qf::navigation-layout>