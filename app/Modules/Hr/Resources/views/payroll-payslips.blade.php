<x-qf::navigation-layout configKey="hr.payroll_payslip" context="payroll" moduleName="hr" :overrides=[]>
    <livewire:qf.data-table
        configKey="hr.payroll_payslip"
        :queryFilters="request()->query('filters', [])"
    />
</x-qf::navigation-layout>
