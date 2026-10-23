<div>
    <p class="text-muted mb-4">Select the target calendar, year, and how you want to populate the holidays.</p>

    {{-- Company Selector (multi-company only) --}}
    @if ($isMultiCompany)
        <div class="mb-4">
            <label class="form-label fw-bold">Company</label>
            <select class="form-select" wire:model.live="companyId">
                <option value="">— All Companies —</option>
                @foreach ($companies as $company)
                    <option value="{{ $company['id'] }}">{{ $company['name'] }}</option>
                @endforeach
            </select>
            <small class="text-muted d-block">Filter calendars by company. Leave as "All Companies" to see all.</small>
        </div>
    @endif

    {{-- Calendar Selector --}}
    <div class="mb-4">
        <label class="form-label fw-bold">Holiday Calendar</label>
        <select class="form-select" wire:model.live="calendarId">
            <option value="">— Select a Calendar —</option>
            @foreach ($calendars as $calendar)
                <option value="{{ $calendar['id'] }}">
                    {{ $calendar['name'] }}
                    ({{ $calendar['country_code'] ?? 'N/A' }})
                </option>
            @endforeach
        </select>
        @if (empty($calendars))
            <div class="alert alert-warning py-2 px-3 mt-2">
                <i class="fas fa-exclamation-triangle me-1"></i>
                No active calendars found. Please create one first.
            </div>
        @endif
    </div>

    {{-- Target Year --}}
    <div class="mb-4">
        <label class="form-label fw-bold">Target Year</label>
        <input type="number" class="form-control" wire:model.live="targetYear"
            min="{{ now()->year }}" max="{{ now()->year + 5 }}"
            style="max-width: 150px;">
        <small class="text-muted d-block">Holidays will be created for this year.</small>
    </div>

    {{-- Creation Method --}}
    <div class="mb-3">
        <label class="form-label fw-bold">Creation Method</label>
        <div class="d-flex flex-column gap-2">
            @foreach ([
                'copy_previous' => ['Copy from Previous Year', 'Copy recurring holidays from the previous year into the target year.', 'fas fa-copy'],
                'empty'         => ['Start Fresh', 'Select dates manually and choose from common holiday templates.', 'fas fa-plus'],
                'template'      => ['Use Country Template', 'Load holidays based on the calendar\'s country code.', 'fas fa-globe'],
                'import'        => ['Import CSV File', 'Upload a CSV file with holiday dates and names.', 'fas fa-file-csv'],
            ] as $value => [$label, $desc, $icon])
                <label class="card border p-3 {{ $sourceOption === $value ? 'border-primary bg-light' : '' }}"
                    style="cursor: pointer;">
                    <div class="d-flex align-items-start gap-3">
                        <input type="radio" name="source_option" value="{{ $value }}"
                            wire:model.live="sourceOption" class="form-check-input mt-1">
                        <div>
                            <div class="fw-bold">
                                <i class="{{ $icon }} me-2 text-primary"></i>{{ $label }}
                            </div>
                            <small class="text-muted">{{ $desc }}</small>
                        </div>
                    </div>
                </label>
            @endforeach
        </div>
    </div>
</div>
