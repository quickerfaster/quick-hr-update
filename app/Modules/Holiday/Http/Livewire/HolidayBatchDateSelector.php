<?php

namespace App\Modules\Holiday\Http\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Modules\Holiday\Traits\AdvancesWizardStep;

/**
 * Wizard step component for selecting multiple dates in the
 * Holiday Batch Creation wizard.
 *
 * Provides three modes:
 *  - Quick Picker:  select weekdays × date range
 *  - Multi-Date:    pick individual dates
 *  - CSV Upload:    upload a CSV file with dates
 *
 * Stores selected dates in the session keyed by wizardId so
 * subsequent steps can access them.
 */
class HolidayBatchDateSelector extends Component
{
    use AdvancesWizardStep;

    public string $configKey;
    public int $stepIndex;
    public ?int $recordId = null;
    public ?int $primaryModelId = null;
    public array $presetData = [];
    public string $wizardId;

    // Mode
    public string $mode = 'quick'; // 'quick' | 'multi' | 'csv'

    // Quick Picker
    public string $rangeStart;
    public string $rangeEnd;
    public array $selectedWeekdays = []; // ['monday', 'tuesday', ...]

    // Multi-Date
    public array $pickedDates = []; // ['2026-12-25', '2026-12-26', ...]
    public string $newDate = '';

    // CSV
    public ?string $csvPreview = null;
    public array $csvDates = [];
    public array $csvErrors = [];
    public array $csvRows = []; // [{name, date}, ...] for 1:1 CSV import

    // Computed
    public array $allDates = [];
    public int $dateCount = 0;

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

        // Default range: target year, full year
        $stepOne = HolidayBatchStepOne::getStepOneData($this->wizardId);
        $targetYear = $stepOne['targetYear'] ?? $presetData['target_year'] ?? now()->year;
        $this->rangeStart = "{$targetYear}-01-01";
        $this->rangeEnd   = "{$targetYear}-12-31";

        // Restore from session if available
        $sessionKey = $this->getSessionKey();
        if (session()->has($sessionKey)) {
            $saved = session()->get($sessionKey);
            $this->mode             = $saved['mode'] ?? 'quick';
            $this->rangeStart       = $saved['rangeStart'] ?? $this->rangeStart;
            $this->rangeEnd         = $saved['rangeEnd'] ?? $this->rangeEnd;
            $this->selectedWeekdays = $saved['selectedWeekdays'] ?? [];
            $this->pickedDates      = $saved['pickedDates'] ?? [];
            $this->csvRows          = $saved['csvRows'] ?? [];
            $this->allDates         = $saved['allDates'] ?? [];
            $this->dateCount        = $this->mode === 'csv' ? count($this->csvRows) : count($this->allDates);
        }
    }

    // -----------------------------------------------------------------
    // Mode switching
    // -----------------------------------------------------------------

    public function setMode(string $mode): void
    {
        $this->mode = $mode;
        $this->recompute();
    }

    // -----------------------------------------------------------------
    // Quick Picker
    // -----------------------------------------------------------------

    public function updatedRangeStart(): void { $this->recompute(); }
    public function updatedRangeEnd(): void { $this->recompute(); }
    public function updatedSelectedWeekdays(): void { $this->recompute(); }

    public function toggleWeekday(string $day): void
    {
        if (in_array($day, $this->selectedWeekdays)) {
            $this->selectedWeekdays = array_values(array_diff($this->selectedWeekdays, [$day]));
        } else {
            $this->selectedWeekdays[] = $day;
        }
        $this->recompute();
    }

    public function selectAllWeekdays(): void
    {
        $this->selectedWeekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $this->recompute();
    }

    public function selectWeekends(): void
    {
        $this->selectedWeekdays = ['saturday', 'sunday'];
        $this->recompute();
    }

    public function clearWeekdays(): void
    {
        $this->selectedWeekdays = [];
        $this->recompute();
    }

    // -----------------------------------------------------------------
    // Multi-Date Picker
    // -----------------------------------------------------------------

    public function addDate(): void
    {
        if (empty($this->newDate)) {
            return;
        }

        if (!in_array($this->newDate, $this->pickedDates)) {
            $this->pickedDates[] = $this->newDate;
            sort($this->pickedDates);
        }

        $this->newDate = '';
        $this->recompute();
    }

    public function removeDate(int $index): void
    {
        unset($this->pickedDates[$index]);
        $this->pickedDates = array_values($this->pickedDates);
        $this->recompute();
    }

    // -----------------------------------------------------------------
    // CSV Upload
    // -----------------------------------------------------------------

    public function processCsv(string $content): void
    {
        $this->csvErrors = [];
        $this->csvDates  = [];
        $this->csvRows   = [];

        $lines = explode("\n", trim($content));
        if (count($lines) < 1) {
            $this->csvErrors[] = 'CSV file is empty.';
            return;
        }

        // Skip header row if it looks like a header
        $startIndex = 0;
        $firstLine = strtolower(trim($lines[0]));
        if (str_contains($firstLine, 'date') || str_contains($firstLine, 'name')) {
            $startIndex = 1;
        }

        for ($i = $startIndex; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) {
                continue;
            }

            $cols = str_getcsv($line);
            $dateStr = trim($cols[0] ?? '');
            $name = trim($cols[1] ?? '');

            if (empty($dateStr)) {
                $this->csvErrors[] = "Row " . ($i + 1) . ": empty date.";
                continue;
            }

            try {
                $date = Carbon::parse($dateStr);
                $normalized = $date->toDateString();
                $this->csvDates[] = $normalized;
                $this->csvRows[] = [
                    'name' => $name !== '' ? $name : 'Holiday',
                    'date' => $normalized,
                ];
            } catch (\Exception $e) {
                $this->csvErrors[] = "Row " . ($i + 1) . ": invalid date '{$dateStr}'.";
            }
        }

        $this->csvDates = array_values(array_unique($this->csvDates));
        sort($this->csvDates);
        $this->recompute();
    }

    // -----------------------------------------------------------------
    // Recompute allDates from the active mode
    // -----------------------------------------------------------------

    protected function recompute(): void
    {
        switch ($this->mode) {
            case 'quick':
                $this->allDates = $this->computeQuickDates();
                $this->dateCount = count($this->allDates);
                break;
            case 'multi':
                $this->allDates = $this->pickedDates;
                $this->dateCount = count($this->allDates);
                break;
            case 'csv':
                // CSV rows are paired name+date. Keep allDates empty so the
                // preview step does NOT cross-join; the 1:1 rows flow through
                // the TemplateSelector instead.
                $this->allDates = [];
                $this->dateCount = count($this->csvRows);
                break;
        }

        $this->saveToSession();
    }

    protected function computeQuickDates(): array
    {
        if (empty($this->selectedWeekdays) || empty($this->rangeStart) || empty($this->rangeEnd)) {
            return [];
        }

        try {
            $start = Carbon::parse($this->rangeStart);
            $end   = Carbon::parse($this->rangeEnd);
        } catch (\Exception $e) {
            return [];
        }

        $dayMap = [
            'monday'    => Carbon::MONDAY,
            'tuesday'   => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday'  => Carbon::THURSDAY,
            'friday'    => Carbon::FRIDAY,
            'saturday'  => Carbon::SATURDAY,
            'sunday'    => Carbon::SUNDAY,
        ];

        $allowedDays = [];
        foreach ($this->selectedWeekdays as $day) {
            if (isset($dayMap[$day])) {
                $allowedDays[] = $dayMap[$day];
            }
        }

        if (empty($allowedDays)) {
            return [];
        }

        $dates = [];
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            if (in_array($date->dayOfWeek, $allowedDays)) {
                $dates[] = $date->toDateString();
            }
        }

        return $dates;
    }

    // -----------------------------------------------------------------
    // Session persistence
    // -----------------------------------------------------------------

    protected function getSessionKey(): string
    {
        return $this->wizardId . '-date-selector';
    }

    protected function saveToSession(): void
    {
        session()->put($this->getSessionKey(), [
            'mode'             => $this->mode,
            'rangeStart'       => $this->rangeStart,
            'rangeEnd'         => $this->rangeEnd,
            'selectedWeekdays' => $this->selectedWeekdays,
            'pickedDates'      => $this->pickedDates,
            'csvRows'          => $this->csvRows,
            'allDates'         => $this->allDates,
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
     * Called by the parent wizard or next step to retrieve selected dates.
     */
    public static function getDatesForWizard(string $wizardId): array
    {
        $key = $wizardId . '-date-selector';
        $data = session()->get($key, []);
        return $data['allDates'] ?? [];
    }

    /**
     * Retrieve CSV name+date rows for 1:1 CSV import.
     */
    public static function getCsvRowsForWizard(string $wizardId): array
    {
        $key = $wizardId . '-date-selector';
        $data = session()->get($key, []);
        return $data['csvRows'] ?? [];
    }

    // -----------------------------------------------------------------
    // Helpers for the blade
    // -----------------------------------------------------------------

    public function getSourceOption(): string
    {
        $stepOne = HolidayBatchStepOne::getStepOneData($this->wizardId);
        return $stepOne['sourceOption'] ?? $this->presetData['source_option'] ?? 'copy_previous';
    }

    public function getTargetYear(): int
    {
        $stepOne = HolidayBatchStepOne::getStepOneData($this->wizardId);
        return (int) ($stepOne['targetYear'] ?? $this->presetData['target_year'] ?? now()->year);
    }

    public function shouldShow(): bool
    {
        $source = $this->getSourceOption();
        return in_array($source, ['empty', 'template', 'import']);
    }

    public function render()
    {
        return view('holiday::livewire.holiday-batch-date-selector');
    }
}
