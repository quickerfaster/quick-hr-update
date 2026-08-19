<x-qf::navigation-layout configKey="payroll.payroll_payslip" context="processing" moduleName="payroll" :overrides=[]>
    <livewire:qf.data-table
        configKey="payroll.payroll_payslip"
        :queryFilters="request()->query('filters', [])"
    />
</x-qf::navigation-layout>
