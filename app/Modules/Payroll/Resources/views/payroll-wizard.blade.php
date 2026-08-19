
<x-qf::navigation-layout
    configKey="payroll.employee"
    context="payroll"
    moduleName="payroll"
    :overrides="[
        'top_bar' => ['enabled' => false],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => false],
    ]"
>
    <livewire:qf.payroll-run-wizard :payrollRunId="request('id') ?? null" />

</x-qf::navigation-layout>
