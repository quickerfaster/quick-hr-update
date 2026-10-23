<div>
    <p class="text-muted mb-3">
        Configure default values that will apply to <strong>all</strong> holidays in this batch.
        Individual holidays can still be edited in the review step.
    </p>

    {{-- Enable toggle --}}
    <div class="form-check form-switch mb-3">
        <input type="checkbox" class="form-check-input" id="applyDefaults" wire:model.live="applyDefaults">
        <label class="form-check-label fw-bold" for="applyDefaults">
            Apply batch defaults to all holidays
        </label>
    </div>

    @if ($applyDefaults)
        <div class="card bg-light border">
            <div class="card-body">
                {{-- Pay Rate --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Default Holiday Pay Rate</label>
                    <select class="form-select" wire:model.live="defaultPayRate" style="max-width: 200px;">
                        <option value="1.0">1.0x (Standard)</option>
                        <option value="1.5">1.5x (Time and a half)</option>
                        <option value="2.0">2.0x (Double time)</option>
                        <option value="2.5">2.5x</option>
                        <option value="3.0">3.0x (Triple time)</option>
                    </select>
                    <small class="text-muted d-block">Multiplier applied to base pay for hours worked on this holiday.</small>
                </div>

                {{-- Business Impact --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Default Business Impact</label>
                    <select class="form-select" wire:model.live="defaultBusinessImpact" style="max-width: 300px;">
                        <option value="office_closed">Office Closed</option>
                        <option value="reduced_staff">Reduced Staff</option>
                        <option value="normal_operations">Normal Operations</option>
                        <option value="remote_only">Remote Work Only</option>
                        <option value="essential_only">Essential Staff Only</option>
                    </select>
                </div>

                {{-- Eligible Employee Types --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Default Eligible Employee Types</label>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach ([
                            'full_time' => 'Full-time', 'part_time' => 'Part-time',
                            'contract' => 'Contractors', 'intern' => 'Interns',
                            'temporary' => 'Temporary', 'all' => 'All Employees'
                        ] as $val => $label)
                            <input type="checkbox" class="btn-check" id="et_{{ $val }}"
                                wire:model.live="defaultEligibleEmployeeTypes" value="{{ $val }}" autocomplete="off">
                            <label class="btn btn-outline-secondary btn-sm {{ in_array($val, $defaultEligibleEmployeeTypes) ? 'active' : '' }}"
                                for="et_{{ $val }}">{{ $label }}</label>
                        @endforeach
                    </div>
                </div>

                {{-- Paid / Recurring toggles --}}
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="defaultIsPaid" wire:model.live="defaultIsPaid">
                            <label class="form-check-label" for="defaultIsPaid">Paid Holiday</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="defaultIsRecurring" wire:model.live="defaultIsRecurring">
                            <label class="form-check-label" for="defaultIsRecurring">Recurring Yearly</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-3 text-muted">
            <i class="fas fa-info-circle me-1"></i>
            Each holiday will use its own template values. Enable the toggle above to override.
        </div>
    @endif
</div>
