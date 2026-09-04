@php
    $employeeId = request()->query('filter')['employee_id'] ?? null;
    $employee = $employeeId ? \App\Modules\Hr\Models\Employee::find($employeeId) : null;
    $pageTitle = $employee ? 'Clock Events — ' . trim($employee->first_name . ' ' . $employee->last_name) : null;
@endphp

<x-qf::navigation-layout configKey="attendance.clock_event" context="time" moduleName="attendance" :overrides=[]>
    <livewire:qf.data-table configKey="attendance.clock_event" :page-title="$pageTitle" />
</x-qf::navigation-layout>
