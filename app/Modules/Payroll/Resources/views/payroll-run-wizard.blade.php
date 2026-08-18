
<x-qf::navigation-layout
    configKey="hr.employee"
    context="payroll"
    moduleName="hr"
    :overrides="[
        'top_bar' => ['enabled' => false],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => false],
    ]"
>
    <livewire:qf.wizard configKey="hr.wizards.payroll_run_wizard" />
</x-qf::navigation-layout>