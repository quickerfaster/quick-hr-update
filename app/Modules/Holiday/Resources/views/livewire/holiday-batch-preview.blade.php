<div>
    @if ($submitted)
        {{-- Post-submission success state --}}
        <div class="text-center py-4">
            <div class="mb-3">
                <i class="fas fa-check-circle text-success fa-3x"></i>
            </div>
            <h5 class="fw-bold">{{ $submittedCount }} holiday(s) created successfully!</h5>
            <p class="text-muted">
                Calendar: <strong>{{ $this->getCalendarName() }}</strong> &middot;
                Year: <strong>{{ $this->getTargetYear() }}</strong>
            </p>
            @if ($submitError)
                <div class="alert alert-warning py-2 px-3 mt-2">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    {{ $submitError }}
                </div>
            @endif
            <p class="text-muted small mt-3">Click "Complete Setup" below to finish.</p>
        </div>
    @else
        {{-- Summary header --}}
        <div class="mb-3">
            <h5 class="mb-1">{{ count($previewItems) }} holiday(s) will be created</h5>
            <p class="text-muted small mb-0">
                Calendar: <strong>{{ $this->getCalendarName() }}</strong> &middot;
                Year: <strong>{{ $this->getTargetYear() }}</strong> &middot;
                Method: <strong>{{ $this->getSourceLabel() }}</strong>
            </p>
            <small class="text-muted">Click "Save & Continue" below to create the holidays.</small>
        </div>

    @if (empty($previewItems))
        <div class="text-center py-5 text-muted">
            <i class="fas fa-calendar-xmark fa-3x mb-3"></i>
            <h5>No holidays to create</h5>
            <p>Go back and select at least one holiday template or date.</p>
        </div>
    @else
        {{-- Tabular preview --}}
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Pay Rate</th>
                        <th>Paid</th>
                        <th style="width: 80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($previewItems as $i => $item)
                        @if ($editingIndex === $i)
                            {{-- Inline edit row --}}
                            <tr class="table-primary">
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <input type="text" class="form-control form-control-sm"
                                        wire:model="editItem.name">
                                </td>
                                <td>
                                    <input type="date" class="form-control form-control-sm"
                                        wire:model="editItem.date">
                                </td>
                                <td>
                                    <select class="form-select form-select-sm" wire:model="editItem.type">
                                        <option value="public">Public</option>
                                        <option value="company">Company</option>
                                        <option value="regional">Regional</option>
                                        <option value="observance">Observance</option>
                                        <option value="optional">Optional</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm"
                                        wire:model="editItem.pay_rate" step="0.1" min="1.0" max="3.0"
                                        style="width: 80px;">
                                </td>
                                <td>
                                    <select class="form-select form-select-sm" wire:model="editItem.is_paid" style="width: 80px;">
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-success"
                                            wire:click="saveEdit" title="Save">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                            wire:click="cancelEdit" title="Cancel">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @else
                            {{-- Display row --}}
                            <tr>
                                <td class="text-muted small">{{ $i + 1 }}</td>
                                <td>
                                    <strong>{{ $item['name'] }}</strong>
                                    @if (!empty($item['description']))
                                        <br><small class="text-muted">{{ \Str::limit($item['description'], 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-nowrap">
                                        <i class="far fa-calendar me-1 text-muted"></i>
                                        {{ \Carbon\Carbon::parse($item['date'])->format('D, M d, Y') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $this->getTypeBadgeClass($item['type']) }}">
                                        {{ ucfirst($item['type']) }}
                                    </span>
                                </td>
                                <td>
                                    @if (($item['pay_rate'] ?? 1.0) > 1.0)
                                        <span class="badge bg-warning text-dark">
                                            {{ number_format($item['pay_rate'] ?? 1.0, 1) }}x
                                        </span>
                                    @else
                                        <span class="text-muted">1.0x</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($item['is_paid'] ?? true)
                                        <i class="fas fa-check-circle text-success"></i>
                                    @else
                                        <i class="fas fa-times-circle text-danger"></i>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                            wire:click="startEdit({{ $i }})" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            wire:click="removeItem({{ $i }})" title="Remove">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    @endif
</div>
