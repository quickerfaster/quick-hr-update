@php
    use QuickerFaster\UILibrary\Services\Config\ConfigResolver;
    $resolver = app(ConfigResolver::class, ['configKey' => "hr.pay_schedule"]);
    $config = $resolver->getConfig();
    $customComponent = !empty($config['detailComponent']) ? $config['detailComponent'] : 'qf.data-table-detail';
@endphp

<x-qf::navigation-layout configKey="hr.pay_schedule" context="payroll" moduleName="hr" :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]">
    @livewire($customComponent, ["inline" => true, "recordId" => $recordId, "configKey" => "hr.pay_schedule", "returnParams" => $returnParams])
</x-qf::navigation-layout>