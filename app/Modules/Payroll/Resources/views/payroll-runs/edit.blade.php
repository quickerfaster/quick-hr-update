<x-qf::navigation-layout configKey="payroll.payroll_run" context="processing" moduleName="payroll" :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]">
    <livewire:qf.data-table-form configKey="payroll.payroll_run" :recordId="$recordId" :inline="true" :returnParams="$returnParams" />
</x-qf::navigation-layout>