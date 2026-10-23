<div>
    @if (!$this->shouldShow())
        <div class="text-center py-4">
            <i class="fas fa-info-circle text-info fa-2x mb-3"></i>
            <p class="text-muted">Dates are automatically determined by the "{{ $this->getSourceOption() === 'copy_previous' ? 'Copy from Previous Year' : 'Import CSV File' }}" method.</p>
            <p class="text-muted small">You can proceed to the next step.</p>
        </div>
    @else
        {{-- Mode Tabs --}}
        <div class="d-flex gap-2 mb-4">
            <button type="button"
                class="btn btn-sm {{ $mode === 'quick' ? 'btn-primary' : 'btn-outline-secondary' }}"
                wire:click="setMode('quick')">
                <i class="fas fa-calendar-week me-1"></i> Quick Picker
            </button>
            <button type="button"
                class="btn btn-sm {{ $mode === 'multi' ? 'btn-primary' : 'btn-outline-secondary' }}"
                wire:click="setMode('multi')">
                <i class="fas fa-calendar-check me-1"></i> Multi-Date
            </button>
            <button type="button"
                class="btn btn-sm {{ $mode === 'csv' ? 'btn-primary' : 'btn-outline-secondary' }}"
                wire:click="setMode('csv')">
                <i class="fas fa-file-csv me-1"></i> CSV Upload
            </button>
        </div>

        {{-- Quick Picker --}}
        @if ($mode === 'quick')
            <div class="card bg-light border mb-3">
                <div class="card-body">
                    <label class="form-label fw-bold">Select Days of the Week</label>
                    <div class="d-flex flex-wrap gap-1 mb-3">
                        @foreach ([
                            'monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed',
                            'thursday' => 'Thu', 'friday' => 'Fri', 'saturday' => 'Sat', 'sunday' => 'Sun'
                        ] as $val => $label)
                            <input type="checkbox" class="btn-check" id="qd_{{ $val }}"
                                wire:click="toggleWeekday('{{ $val }}')"
                                @checked(in_array($val, $selectedWeekdays)) autocomplete="off">
                            <label class="btn btn-outline-secondary btn-sm {{ in_array($val, $selectedWeekdays) ? 'active' : '' }}"
                                for="qd_{{ $val }}">{{ $label }}</label>
                        @endforeach
                    </div>
                    <div class="d-flex gap-1 mb-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="selectAllWeekdays">
                            All Weekdays
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="selectWeekends">
                            Weekends
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="clearWeekdays">
                            Clear
                        </button>
                    </div>

                    <label class="form-label fw-bold">Date Range</label>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small">From</label>
                            <input type="date" class="form-control form-control-sm" wire:model.live="rangeStart">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">To</label>
                            <input type="date" class="form-control form-control-sm" wire:model.live="rangeEnd">
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Multi-Date Picker --}}
        @if ($mode === 'multi')
            <div class="card bg-light border mb-3">
                <div class="card-body">
                    <label class="form-label fw-bold">Add Dates</label>
                    <div class="input-group mb-3">
                        <input type="date" class="form-control" wire:model.live="newDate"
                            min="{{ $this->getTargetYear() }}-01-01"
                            max="{{ $this->getTargetYear() }}-12-31">
                        <button type="button" class="btn btn-primary" wire:click="addDate"
                            @if(empty($newDate)) disabled @endif>
                            <i class="fas fa-plus me-1"></i> Add
                        </button>
                    </div>

                    @if (!empty($pickedDates))
                        <div class="d-flex flex-wrap gap-1">
                            @foreach ($pickedDates as $i => $date)
                                <span class="badge bg-primary d-inline-flex align-items-center gap-1 py-2 px-3">
                                    {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                                    <button type="button" class="btn-close btn-close-white ms-1"
                                        style="font-size: 0.5rem;" wire:click="removeDate({{ $i }})"></button>
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small mb-0">No dates added yet. Use the date picker above.</p>
                    @endif
                </div>
            </div>
        @endif

        {{-- CSV Upload --}}
        @if ($mode === 'csv')
            <div class="card bg-light border mb-3">
                <div class="card-body">
                    <label class="form-label fw-bold">Upload CSV File</label>
                    <p class="text-muted small">
                        CSV should have a <strong>date</strong> column (YYYY-MM-DD). An optional <strong>name</strong> column is ignored.
                    </p>

                    <div class="mb-3">
                        <input type="file" class="form-control" accept=".csv"
                            wire:model="csvPreview"
                            id="csv-upload-input">
                        <div class="form-text">Max file size: 1MB</div>
                    </div>

                    @if (!empty($csvErrors))
                        <div class="alert alert-danger py-2 px-3">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            @foreach ($csvErrors as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    @if (!empty($csvDates))
                        <div class="alert alert-success py-2 px-3">
                            <i class="fas fa-check-circle me-1"></i>
                            {{ count($csvDates) }} valid date(s) found.
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Summary --}}
        @if ($dateCount > 0)
            <div class="alert alert-info d-flex align-items-center gap-2 py-2 px-3">
                <i class="fas fa-list-ol"></i>
                <strong>{{ $dateCount }}</strong> date(s) selected for {{ $this->getTargetYear() }}

                @if ($dateCount <= 10)
                    <span class="text-muted small ms-2">
                        ({{ implode(', ', array_map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'), array_slice($allDates, 0, 10))) }})
                    </span>
                @else
                    <span class="text-muted small ms-2">
                        ({{ implode(', ', array_map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'), array_slice($allDates, 0, 5))) }} ... and {{ $dateCount - 5 }} more)
                    </span>
                @endif
            </div>
        @else
            <div class="alert alert-light border py-2 px-3">
                <i class="fas fa-info-circle text-muted me-1"></i>
                <span class="text-muted">No dates selected — each holiday template will use its own date.</span>
            </div>
        @endif
    @endif
</div>

@push('scripts')
<script>
    // Handle CSV file upload via Livewire
    document.addEventListener('livewire:initialized', () => {
        const input = document.getElementById('csv-upload-input');
        if (!input) return;

        input.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (event) => {
                @this.processCsv(event.target.result);
            };
            reader.readAsText(file);
        });
    });
</script>
@endpush
