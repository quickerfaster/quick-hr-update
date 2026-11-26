@props(['field', 'value', 'name', 'label'])

<div class="mb-3">
    {{-- DEBUG: Temporarily replaced Livewire component to isolate 500 error. --}}
    <p class="text-muted">Salary components editor loads here. Employee ID: {{ $value['employee_id'] ?? 'not set' }}</p>
</div>
