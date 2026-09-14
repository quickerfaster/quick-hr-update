<x-qf::navigation-layout
    context="requests"
    moduleName="leave"
    :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => true],
        'title' => ['enabled' => true],
    ]"
>
    @livewire('qf.approval-request-list', ['view' => 'pending', 'workflowKey' => 'leave_request'])
</x-qf::navigation-layout>
