<?php

namespace App\Modules\Holiday\Http\Livewire;

use Livewire\Component;
use App\Modules\Holiday\Models\Holiday;
use App\Modules\Holiday\Models\HolidayCalendar;
use App\Modules\Holiday\Http\Livewire\HolidayBatchStepOne;
use App\Modules\Holiday\Traits\AdvancesWizardStep;

/**
 * Wizard step component that replaces the bare checklist in the
 * Holiday Batch Creation wizard with an enriched selector showing
 * dates, types, pay rates, and filters.
 *
 * Loads available holiday templates based on the source option
 * (copy_previous, template, empty) and lets the user pick which
 * ones to create. Stores selections in the session for the
 * preview step.
 */
class HolidayTemplateSelector extends Component
{
    use AdvancesWizardStep;

    public string $configKey;
    public int $stepIndex;
    public ?int $recordId = null;
    public ?int $primaryModelId = null;
    public array $presetData = [];
    public string $wizardId;

    public array $templates = [];
    public array $selectedIds = [];
    public string $filterType = 'all';
    public bool $selectAll = false;

    public function mount(
        string $configKey,
        int $stepIndex,
        ?int $recordId = null,
        ?int $primaryModelId = null,
        array $presetData = [],
        string $wizardId = ''
    ): void {
        $this->configKey      = $configKey;
        $this->stepIndex      = $stepIndex;
        $this->recordId       = $recordId;
        $this->primaryModelId = $primaryModelId;
        $this->presetData     = $presetData;
        $this->wizardId       = $wizardId;

        $this->loadTemplates();

        // Restore selections from session
        $sessionKey = $this->getSessionKey();
        if (session()->has($sessionKey)) {
            $saved = session()->get($sessionKey);
            $this->selectedIds = $saved['selectedIds'] ?? [];
        }
    }

    // -----------------------------------------------------------------
    // Template loading
    // -----------------------------------------------------------------

    protected function loadTemplates(): void
    {
        $stepOne = HolidayBatchStepOne::getStepOneData($this->wizardId);
        $sourceOption = $stepOne['sourceOption'] ?? $this->presetData['source_option'] ?? 'copy_previous';
        $calendarId   = $stepOne['calendarId'] ?? $this->presetData['calendar_id'] ?? null;
        $targetYear   = (int) ($stepOne['targetYear'] ?? $this->presetData['target_year'] ?? now()->year);

        switch ($sourceOption) {
            case 'copy_previous':
                $this->templates = $this->loadFromPreviousYear($calendarId, $targetYear);
                break;

            case 'template':
                $this->templates = $this->loadFromCountryTemplate($calendarId, $targetYear);
                break;

            case 'import':
                $this->templates = $this->loadFromCsv();
                break;

            case 'empty':
            default:
                $this->templates = $this->loadCommonTemplates($targetYear);
                break;
        }
    }

    protected function loadFromPreviousYear(?int $calendarId, int $targetYear): array
    {
        $sourceYear = $targetYear - 1;

        $holidays = Holiday::withoutCompanyScope()
            ->where('year', $sourceYear)
            ->where('is_active', true)
            ->when($calendarId, fn ($q) => $q->where('calendar_id', $calendarId))
            ->orderBy('date')
            ->get();

        return $holidays->map(fn ($h) => [
            'id'          => $h->id,
            'name'        => $h->name,
            'date'        => $h->date->toDateString(),
            'target_date' => $this->shiftYear($h->date, $targetYear)->toDateString(),
            'type'        => $h->holiday_type,
            'pay_rate'    => (float) ($h->holiday_pay_rate ?? 1.0),
            'is_paid'     => (bool) ($h->is_paid_holiday),
            'is_recurring'=> (bool) ($h->is_recurring),
            'description' => $h->description,
        ])->toArray();
    }

    protected function loadFromCountryTemplate(?int $calendarId, int $targetYear): array
    {
        // Get the calendar's country code
        $countryCode = 'US';
        if ($calendarId) {
            $calendar = HolidayCalendar::find($calendarId);
            $countryCode = $calendar?->country_code ?? 'US';
        }

        // Load from the curated HolidayTemplate table
        $templates = \App\Modules\Holiday\Models\HolidayTemplate::where('country_code', $countryCode)
            ->where('is_active', true)
            ->orderBy('month')
            ->orderBy('day')
            ->get();

        if ($templates->isEmpty()) {
            // Fallback: load common templates
            return $this->loadCommonTemplates($targetYear);
        }

        return $templates->map(function ($t) use ($targetYear) {
            $date = $t->computeDateForYear($targetYear);
            $dateStr = $date ? $date->toDateString() : '';

            return [
                'id'          => 'tpl_' . $t->id,
                'name'        => $t->name,
                'date'        => $dateStr,
                'target_date' => $dateStr,
                'type'        => $t->holiday_type,
                'pay_rate'    => (float) ($t->default_pay_rate ?? 1.0),
                'is_paid'     => (bool) ($t->is_paid),
                'is_recurring'=> true,
                'description' => null,
            ];
        })->toArray();
    }

    protected function loadCommonTemplates(int $targetYear): array
    {
        // Return a set of common holiday templates
        $common = [
            ['name' => "New Year's Day",      'month' => 1,  'day' => 1,  'type' => 'public', 'pay_rate' => 2.0],
            ['name' => 'Valentine\'s Day',     'month' => 2,  'day' => 14, 'type' => 'observance', 'pay_rate' => 1.0],
            ['name' => 'Easter Monday',        'month' => 4,  'day' => 1,  'type' => 'public', 'pay_rate' => 2.0],
            ['name' => 'Labor Day',            'month' => 5,  'day' => 1,  'type' => 'public', 'pay_rate' => 2.0],
            ['name' => 'Independence Day',     'month' => 7,  'day' => 4,  'type' => 'public', 'pay_rate' => 2.0],
            ['name' => 'Christmas Day',        'month' => 12, 'day' => 25, 'type' => 'public', 'pay_rate' => 2.5],
            ['name' => 'Boxing Day',           'month' => 12, 'day' => 26, 'type' => 'public', 'pay_rate' => 2.0],
            ['name' => "New Year's Eve",       'month' => 12, 'day' => 31, 'type' => 'observance', 'pay_rate' => 1.5],
        ];

        return array_map(fn ($t) => [
            'id'          => 'tpl_' . ($t['month'] . '_' . $t['day']),
            'name'        => $t['name'],
            'date'        => sprintf('%d-%02d-%02d', $targetYear, $t['month'], $t['day']),
            'target_date' => sprintf('%d-%02d-%02d', $targetYear, $t['month'], $t['day']),
            'type'        => $t['type'],
            'pay_rate'    => $t['pay_rate'],
            'is_paid'     => true,
            'is_recurring'=> true,
            'description' => null,
        ], $common);
    }

    /**
     * Load CSV rows (name + date) as 1:1 templates for CSV import.
     */
    protected function loadFromCsv(): array
    {
        $rows = HolidayBatchDateSelector::getCsvRowsForWizard($this->wizardId);

        $templates = [];
        foreach ($rows as $i => $row) {
            $templates[] = [
                'id'          => 'csv_' . $i,
                'name'        => $row['name'] ?? 'Holiday',
                'date'        => $row['date'] ?? '',
                'target_date' => $row['date'] ?? '',
                'type'        => 'company',
                'pay_rate'    => 1.0,
                'is_paid'     => true,
                'is_recurring'=> false,
                'description' => null,
            ];
        }

        return $templates;
    }

    protected function shiftYear(\Carbon\Carbon $date, int $targetYear): \Carbon\Carbon
    {
        $month = $date->month;
        $day   = $date->day;

        if ($month === 2 && $day === 29 && !\Carbon\Carbon::create($targetYear, 1, 1)->isLeapYear()) {
            $day = 28;
        }

        return \Carbon\Carbon::create($targetYear, $month, $day);
    }

    // -----------------------------------------------------------------
    // Selection
    // -----------------------------------------------------------------

    public function toggleSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selectedIds = array_column($this->filteredTemplates(), 'id');
        } else {
            $this->selectedIds = [];
        }
        $this->saveToSession();
    }

    public function updatedSelectAll(): void
    {
        $this->toggleSelectAll();
    }

    public function updatedSelectedIds(): void
    {
        $this->saveToSession();
    }

    public function updatedFilterType(): void
    {
        // no-op, just re-renders
    }

    // -----------------------------------------------------------------
    // Computed
    // -----------------------------------------------------------------

    public function filteredTemplates(): array
    {
        if ($this->filterType === 'all') {
            return $this->templates;
        }

        return array_filter($this->templates, fn ($t) => $t['type'] === $this->filterType);
    }

    public function selectedCount(): int
    {
        return count($this->selectedIds);
    }

    public function totalCount(): int
    {
        return count($this->templates);
    }

    public function getTypeBadgeClass(string $type): string
    {
        return match ($type) {
            'public'     => 'bg-success',
            'company'    => 'bg-primary',
            'regional'   => 'bg-info',
            'observance' => 'bg-secondary',
            'optional'   => 'bg-warning text-dark',
            default      => 'bg-light text-dark',
        };
    }

    public function getPayRateBadgeClass(float $rate): string
    {
        return match (true) {
            $rate >= 2.5 => 'bg-danger',
            $rate >= 2.0 => 'bg-warning text-dark',
            $rate > 1.0  => 'bg-info',
            default      => 'bg-light text-dark',
        };
    }

    // -----------------------------------------------------------------
    // Session persistence
    // -----------------------------------------------------------------

    protected function getSessionKey(): string
    {
        return $this->wizardId . '-template-selector';
    }

    protected function saveToSession(): void
    {
        session()->put($this->getSessionKey(), [
            'selectedIds' => $this->selectedIds,
            'templates'   => $this->templates,
        ]);
    }

    /**
     * Hook used by the AdvancesWizardStep trait.
     */
    protected function saveStepData(): void
    {
        $this->saveToSession();
    }

    /**
     * Called by the preview step to get selected templates.
     */
    public static function getSelectedTemplates(string $wizardId): array
    {
        $key = $wizardId . '-template-selector';
        $data = session()->get($key, []);
        $selectedIds = $data['selectedIds'] ?? [];
        $templates   = $data['templates'] ?? [];

        if (empty($selectedIds) || empty($templates)) {
            return [];
        }

        $idMap = [];
        foreach ($templates as $t) {
            $idMap[$t['id']] = $t;
        }

        $selected = [];
        foreach ($selectedIds as $id) {
            if (isset($idMap[$id])) {
                $selected[] = $idMap[$id];
            }
        }

        return $selected;
    }

    public function render()
    {
        return view('holiday::livewire.holiday-template-selector');
    }
}
