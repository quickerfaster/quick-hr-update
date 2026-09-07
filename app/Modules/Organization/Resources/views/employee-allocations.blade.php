<x-qf::navigation-layout
    configKey="organization.employee_allocations"
    context="companies"
    moduleName="organization"
    :overrides="[]">

    @livewire('qf.user-company-assignment', ['user' => request('user')])
</x-qf::navigation-layout>
