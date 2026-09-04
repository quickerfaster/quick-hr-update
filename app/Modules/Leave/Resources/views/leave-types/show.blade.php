<x-qf::navigation-layout configKey="leave.leave_type" context="configuration" moduleName="leave" :overrides="[]">
    @livewire('qf.data-table-detail', ["inline" => true, "recordId" => $recordId, "configKey" => "leave.leave_type", "returnParams" => $returnParams])
</x-qf::navigation-layout>
