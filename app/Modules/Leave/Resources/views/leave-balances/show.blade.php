<x-qf::navigation-layout configKey="leave.leave_balance" context="balances" moduleName="leave" :overrides="[]">
    @livewire('qf.data-table-detail', ["inline" => true, "recordId" => $recordId, "configKey" => "leave.leave_balance", "returnParams" => $returnParams])
</x-qf::navigation-layout>
