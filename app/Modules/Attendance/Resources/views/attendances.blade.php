@php
    $employeeId = request()->query('filter')['employee_id'] ?? null;
    $employee = $employeeId ? \App\Modules\Hr\Models\Employee::find($employeeId) : null;
    $pageTitle = $employee ? 'Attendance — ' . trim($employee->first_name . ' ' . $employee->last_name) : null;
@endphp

<x-qf::navigation-layout configKey="attendance.attendance" context="time" moduleName="attendance" :overrides=[]>
    <livewire:qf.data-table configKey="attendance.attendance" :page-title="$pageTitle" />
</x-qf::navigation-layout>
