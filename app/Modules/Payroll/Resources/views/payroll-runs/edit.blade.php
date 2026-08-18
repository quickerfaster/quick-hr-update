<x-qf::navigation-layout configKey="hr.payroll_run" context="payroll" moduleName="hr" :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]">
    <livewire:qf.data-table-form configKey="hr.payroll_run" :recordId="$recordId" :inline="true" :returnParams="$returnParams" />
</x-qf::navigation-layout>