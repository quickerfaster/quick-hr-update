<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Adjust Attendance Record</h6>
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm mb-0">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Error message --}}
                    @if ($errorMessage)
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-1"></i> {{ $errorMessage }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- Success message --}}
                    @if ($isSaved)
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-1"></i> Attendance record updated successfully.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- Not loaded yet --}}
                    @if (!$attendanceId)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i> No attendance record specified. Please select a record from the attendance list.
                        </div>
                    @elseif (!$isLoaded && !$errorMessage)
                        <div class="d-flex justify-content-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    @elseif ($isLoaded)
                        {{-- Record info --}}
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label text-xs text-uppercase text-secondary fw-bold">Employee</label>
                                <p class="form-control-static text-sm mb-0">{{ $employeeName }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-xs text-uppercase text-secondary fw-bold">Employee #</label>
                                <p class="form-control-static text-sm mb-0">{{ $employeeNumber }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-xs text-uppercase text-secondary fw-bold">Date</label>
                                <p class="form-control-static text-sm mb-0">{{ $date }}</p>
                            </div>
                        </div>

                        {{-- Edit form --}}
                        <div class="row g-3">
                            {{-- Status --}}
                            <div class="col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" class="form-select" wire:model="status">
                                    @foreach ($availableStatuses as $s)
                                        <option value="{{ $s }}">{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Net Hours --}}
                            <div class="col-md-2">
                                <label for="net_hours" class="form-label">Net Hours</label>
                                <input type="number" id="net_hours" class="form-control" step="0.01" min="0" max="24"
                                    wire:model="net_hours">
                            </div>

                            {{-- Regular Hours --}}
                            <div class="col-md-2">
                                <label for="regular_hours" class="form-label">Regular Hours</label>
                                <input type="number" id="regular_hours" class="form-control" step="0.01" min="0" max="24"
                                    wire:model="regular_hours">
                            </div>

                            {{-- Overtime Hours --}}
                            <div class="col-md-2">
                                <label for="overtime_hours" class="form-label">OT Hours</label>
                                <input type="number" id="overtime_hours" class="form-control" step="0.01" min="0" max="12"
                                    wire:model="overtime_hours">
                            </div>

                            {{-- Double Time Hours --}}
                            <div class="col-md-2">
                                <label for="double_time_hours" class="form-label">DT Hours</label>
                                <input type="number" id="double_time_hours" class="form-control" step="0.01" min="0" max="12"
                                    wire:model="double_time_hours">
                            </div>

                            {{-- Approved --}}
                            <div class="col-md-4">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_approved" wire:model="is_approved">
                                    <label class="form-check-label" for="is_approved">
                                        Approved for Payroll
                                    </label>
                                </div>
                            </div>

                            {{-- Needs Review --}}
                            <div class="col-md-4">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="needs_review" wire:model="needs_review">
                                    <label class="form-check-label" for="needs_review">
                                        Needs Review
                                    </label>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="col-12">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea id="notes" class="form-control" rows="3" wire:model="notes"
                                    placeholder="Reason for adjustment..."></textarea>
                            </div>
                        </div>

                        {{-- Save button --}}
                        <div class="mt-4">
                            <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="save">
                                    <i class="fas fa-save me-1"></i> Save Changes
                                </span>
                                <span wire:loading wire:target="save">
                                    <span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...
                                </span>
                            </button>
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
