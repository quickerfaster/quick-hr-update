@php
    use QuickerFaster\UILibrary\Services\Config\ConfigResolver;
    $resolver = app(ConfigResolver::class, ['configKey' => "hr.payroll_run"]);
    $config = $resolver->getConfig();
    $customComponent = !empty($config['detailComponent']) ? $config['detailComponent'] : 'qf.data-table-detail';
@endphp

<x-qf::navigation-layout configKey="hr.payroll_run" context="payroll" moduleName="hr" :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]">
    @livewire($customComponent, ["inline" => true, "recordId" => $recordId, "configKey" => "hr.payroll_run", "returnParams" => $returnParams])
</x-qf::navigation-layout>