@props(['field', 'value', 'name', 'label'])

<div class="mb-3">
    @php
        $employeeId = $this->fields['employee_id'] ?? null;
    @endphp
    <livewire:payroll.salary-components-editor :employeeId="$employeeId" />
</div>
