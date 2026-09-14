@php
    use QuickerFaster\UILibrary\Services\Config\ConfigResolver;

    $resolver = app(ConfigResolver::class, ['configKey' => "leave.leave_request"]);
    $config = $resolver->getConfig();
    $customComponent = !empty($config['detailComponent']) ? $config['detailComponent'] : 'qf.data-table-detail';
@endphp

<x-qf::navigation-layout configKey="leave.leave_request" context="requests" moduleName="leave" :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]">
    @livewire($customComponent, ["inline" => true, "recordId" => $recordId, "configKey" => "leave.leave_request", "returnParams" => $returnParams])
</x-qf::navigation-layout>
