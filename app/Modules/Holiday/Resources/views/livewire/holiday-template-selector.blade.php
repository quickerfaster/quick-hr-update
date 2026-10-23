<div>
    {{-- Header with counts and filter --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="fw-bold">{{ $this->selectedCount() }}</span>
            <span class="text-muted">of {{ $this->totalCount() }} holidays selected</span>
        </div>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm" style="width: auto;" wire:model.live="filterType">
                <option value="all">All Types</option>
                <option value="public">Public</option>
                <option value="company">Company</option>
                <option value="regional">Regional</option>
                <option value="observance">Observance</option>
                <option value="optional">Optional</option>
            </select>
            <div class="form-check form-check-inline mb-0">
                <input type="checkbox" class="form-check-input" id="selectAll" wire:model.live="selectAll">
                <label class="form-check-label small" for="selectAll">Select All</label>
            </div>
        </div>
    </div>

    {{-- Template list --}}
    @if (empty($this->filteredTemplates()))
        <div class="text-center py-4 text-muted">
            <i class="fas fa-calendar-times fa-2x mb-2"></i>
            <p>No holiday templates found for the selected criteria.</p>
        </div>
    @else
        <div class="list-group">
            @foreach ($this->filteredTemplates() as $template)
                @php
                    $id = $template['id'];
                    $isChecked = in_array($id, $selectedIds);
                @endphp
                <label class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3"
                    style="cursor: pointer; {{ $isChecked ? 'background-color: #f0f7ff; border-left: 3px solid #0d6efd;' : '' }}">
                    <input type="checkbox" class="form-check-input mt-0 flex-shrink-0"
                        wire:model.live="selectedIds" value="{{ $id }}"
                        id="tpl_{{ $id }}">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>{{ $template['name'] }}</strong>
                            <div class="d-flex gap-1">
                                <span class="badge {{ $this->getTypeBadgeClass($template['type']) }}">
                                    {{ ucfirst($template['type']) }}
                                </span>
                                @if ($template['pay_rate'] > 1.0)
                                    <span class="badge {{ $this->getPayRateBadgeClass($template['pay_rate']) }}">
                                        {{ number_format($template['pay_rate'], 1) }}x
                                    </span>
                                @endif
                                @if ($template['is_paid'])
                                    <span class="badge bg-success">Paid</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-muted small mt-1">
                            <i class="far fa-calendar me-1"></i>
                            {{ \Carbon\Carbon::parse($template['target_date'])->format('D, M d, Y') }}
                            @if ($template['is_recurring'])
                                <span class="ms-2"><i class="fas fa-redo me-1"></i>Recurring</span>
                            @endif
                        </div>
                        @if (!empty($template['description']))
                            <div class="text-muted small mt-1">{{ \Str::limit($template['description'], 80) }}</div>
                        @endif
                    </div>
                </label>
            @endforeach
        </div>
    @endif
</div>
