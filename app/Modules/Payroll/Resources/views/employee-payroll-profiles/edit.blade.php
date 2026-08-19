<x-qf::navigation-layout configKey="payroll.employee_payroll_profile" context="payroll" moduleName="payroll" :overrides="[
        'top_bar' => ['enabled' => true],
        'breadcrumb' => ['enabled' => false],
        'title' => ['enabled' => false],
        'titleRow' => ['enabled' => false],
        'context_menu' => ['enabled' => true],
    ]">
    <livewire:qf.data-table-form configKey="payroll.employee_payroll_profile" :recordId="$recordId" :inline="true" :returnParams="$returnParams" />
</x-qf::navigation-layout>