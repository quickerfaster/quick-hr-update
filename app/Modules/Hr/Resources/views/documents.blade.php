@php
    $employeeId = request()->query('filter')['employee_id'] ?? null;
    $employee = $employeeId ? \App\Modules\Hr\Models\Employee::find($employeeId) : null;
    $pageTitle = $employee ? 'Documents — ' . trim($employee->first_name . ' ' . $employee->last_name) : null;
@endphp

<x-qf::navigation-layout configKey="hr.document" context="manage" moduleName="hr" :overrides=[]>
    <livewire:qf.data-table configKey="hr.document" :page-title="$pageTitle" />
</x-qf::navigation-layout>
