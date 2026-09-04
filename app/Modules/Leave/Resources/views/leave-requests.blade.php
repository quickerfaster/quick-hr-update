@php
    $employeeId = request()->query('filter')['employee_id'] ?? null;
    $employee = $employeeId ? \App\Modules\Hr\Models\Employee::find($employeeId) : null;
    $pageTitle = $employee ? 'Leave Requests — ' . trim($employee->first_name . ' ' . $employee->last_name) : null;
@endphp

<x-qf::navigation-layout configKey="leave.leave_request" context="leave" moduleName="leave" :overrides=[]>
    <livewire:qf.data-table configKey="leave.leave_request" :page-title="$pageTitle" />
</x-qf::navigation-layout>
