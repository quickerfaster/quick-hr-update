<x-qf::navigation-layout configKey="hr.employee_payroll_profile" context="payroll" moduleName="hr" :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]">
    <livewire:qf.data-table-form configKey="hr.employee_payroll_profile" :recordId="$recordId" :inline="true" :returnParams="$returnParams" />
</x-qf::navigation-layout>