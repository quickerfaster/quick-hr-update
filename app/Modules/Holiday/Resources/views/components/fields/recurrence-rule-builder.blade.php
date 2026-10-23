@props(['field', 'value', 'name', 'label'])

<div class="mb-3">
    <label class="form-label fw-bold">{{ $label }}</label>
    <livewire:holiday.recurrence-rule-builder
        :existingJson="$value"
        wire:key="recurrence-builder-{{ $name }}-{{ uniqid() }}"
    />
    @error($name)
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>
