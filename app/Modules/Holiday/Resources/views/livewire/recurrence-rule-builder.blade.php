<div>
    {{-- Preset quick-select buttons --}}
    <div class="mb-3">
        <label class="form-label fw-bold small text-uppercase text-muted">Quick Presets</label>
        <div class="d-flex flex-wrap gap-1">
            <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="applyPreset('thanksgiving')">
                🇺🇸 Thanksgiving
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="applyPreset('memorial_day')">
                🇺🇸 Memorial Day
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="applyPreset('labor_day')">
                🇺🇸 Labor Day
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="applyPreset('monthly_first_monday')">
                📅 First Monday
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="applyPreset('monthly_15th')">
                📅 15th Monthly
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="applyPreset('biweekly_friday')">
                🔄 Biweekly Friday
            </button>
        </div>
    </div>

    {{-- Frequency selector --}}
    <div class="mb-3">
        <label class="form-label fw-bold">Frequency</label>
        <select class="form-select" wire:model.live="frequency">
            <option value="yearly">Yearly</option>
            <option value="monthly">Monthly</option>
            <option value="weekly">Weekly</option>
            <option value="daily">Daily</option>
        </select>
    </div>

    {{-- Yearly options --}}
    @if ($frequency === 'yearly')
        <div class="card bg-light border mb-3">
            <div class="card-body">
                <div class="mb-2">
                    <div class="form-check form-check-inline">
                        <input type="radio" id="yearly_fixed" value="fixed" wire:model.live="yearlyMode" class="form-check-input">
                        <label class="form-check-label" for="yearly_fixed">Fixed Date</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="radio" id="yearly_floating" value="floating" wire:model.live="yearlyMode" class="form-check-input">
                        <label class="form-check-label" for="yearly_floating">Floating Date</label>
                    </div>
                </div>

                @if ($yearlyMode === 'fixed')
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small">Month</label>
                            <select class="form-select form-select-sm" wire:model.live="yearlyMonth">
                                @foreach (['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $month)
                                    <option value="{{ $i + 1 }}">{{ $month }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Day</label>
                            <input type="number" class="form-control form-control-sm" wire:model.live="yearlyDay" min="1" max="31">
                        </div>
                    </div>
                @else
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label small">Week</label>
                            <select class="form-select form-select-sm" wire:model.live="yearlyFloatingWeek">
                                <option value="first">First</option>
                                <option value="second">Second</option>
                                <option value="third">Third</option>
                                <option value="fourth">Fourth</option>
                                <option value="last">Last</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Day</label>
                            <select class="form-select form-select-sm" wire:model.live="yearlyFloatingDay">
                                <option value="monday">Monday</option>
                                <option value="tuesday">Tuesday</option>
                                <option value="wednesday">Wednesday</option>
                                <option value="thursday">Thursday</option>
                                <option value="friday">Friday</option>
                                <option value="saturday">Saturday</option>
                                <option value="sunday">Sunday</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Of Month</label>
                            <select class="form-select form-select-sm" wire:model.live="yearlyFloatingMonth">
                                @foreach (['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $month)
                                    <option value="{{ $i + 1 }}">{{ $month }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Monthly options --}}
    @if ($frequency === 'monthly')
        <div class="card bg-light border mb-3">
            <div class="card-body">
                <div class="mb-2">
                    <div class="form-check form-check-inline">
                        <input type="radio" id="monthly_dom" value="day_of_month" wire:model.live="monthlyMode" class="form-check-input">
                        <label class="form-check-label" for="monthly_dom">Day of Month</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="radio" id="monthly_dow" value="day_of_week" wire:model.live="monthlyMode" class="form-check-input">
                        <label class="form-check-label" for="monthly_dow">Day of Week</label>
                    </div>
                </div>

                @if ($monthlyMode === 'day_of_month')
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small">Day</label>
                            <input type="number" class="form-control form-control-sm" wire:model.live="monthlyDayOfMonth" min="1" max="31">
                        </div>
                    </div>
                @else
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small">Week</label>
                            <select class="form-select form-select-sm" wire:model.live="monthlyWeekOfMonth">
                                <option value="first">First</option>
                                <option value="second">Second</option>
                                <option value="third">Third</option>
                                <option value="fourth">Fourth</option>
                                <option value="last">Last</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Day</label>
                            <select class="form-select form-select-sm" wire:model.live="monthlyDayOfWeek">
                                <option value="monday">Monday</option>
                                <option value="tuesday">Tuesday</option>
                                <option value="wednesday">Wednesday</option>
                                <option value="thursday">Thursday</option>
                                <option value="friday">Friday</option>
                                <option value="saturday">Saturday</option>
                                <option value="sunday">Sunday</option>
                            </select>
                        </div>
                    </div>
                @endif

                <div class="mt-2">
                    <label class="form-label small">Every</label>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control" wire:model.live="monthlyInterval" min="1" max="12">
                        <span class="input-group-text">month(s)</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Weekly options --}}
    @if ($frequency === 'weekly')
        <div class="card bg-light border mb-3">
            <div class="card-body">
                <label class="form-label small">Days</label>
                <div class="d-flex flex-wrap gap-1 mb-2">
                    @foreach (['monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed', 'thursday' => 'Thu', 'friday' => 'Fri', 'saturday' => 'Sat', 'sunday' => 'Sun'] as $val => $label)
                        <input type="checkbox" class="btn-check" id="wd_{{ $val }}" wire:model.live="weeklyDays" value="{{ $val }}" autocomplete="off">
                        <label class="btn btn-outline-secondary btn-sm" for="wd_{{ $val }}">{{ $label }}</label>
                    @endforeach
                </div>

                <div class="mt-2">
                    <label class="form-label small">Every</label>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control" wire:model.live="weeklyInterval" min="1" max="52">
                        <span class="input-group-text">week(s)</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Daily options --}}
    @if ($frequency === 'daily')
        <div class="card bg-light border mb-3">
            <div class="card-body">
                <label class="form-label small">Every</label>
                <div class="input-group input-group-sm">
                    <input type="number" class="form-control" wire:model.live="dailyInterval" min="1" max="365">
                    <span class="input-group-text">day(s)</span>
                </div>
            </div>
        </div>
    @endif

    {{-- End condition --}}
    <div class="mb-3">
        <label class="form-label fw-bold">End</label>
        <div>
            <div class="form-check form-check-inline">
                <input type="radio" id="end_never" value="never" wire:model.live="endType" class="form-check-input">
                <label class="form-check-label" for="end_never">Never</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio" id="end_after" value="after" wire:model.live="endType" class="form-check-input">
                <label class="form-check-label" for="end_after">After</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio" id="end_date" value="date" wire:model.live="endType" class="form-check-input">
                <label class="form-check-label" for="end_date">On Date</label>
            </div>
        </div>

        @if ($endType === 'after')
            <div class="input-group input-group-sm mt-2" style="max-width: 200px;">
                <input type="number" class="form-control" wire:model.live="endAfterCount" min="1">
                <span class="input-group-text">occurrences</span>
            </div>
        @elseif ($endType === 'date')
            <div class="mt-2" style="max-width: 200px;">
                <input type="date" class="form-control form-control-sm" wire:model.live="endDate">
            </div>
        @endif
    </div>

    {{-- Generated rule preview --}}
    @if ($generatedRule)
        <div class="alert alert-success mb-0 py-2 px-3">
            <i class="fas fa-check-circle me-1"></i>
            <strong>{{ $generatedRule }}</strong>
        </div>
    @endif
</div>
