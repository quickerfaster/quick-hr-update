<x-qf::navigation-layout
    configKey="payroll.payroll_run"
    context="processing"
    moduleName="payroll"
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => true],
        'title' => ['enabled' => true],
    ]"
>
    @livewire('qf.approval-request-list', ['view' => 'pending', 'workflowKey' => 'payroll_run'])
</x-qf::navigation-layout>