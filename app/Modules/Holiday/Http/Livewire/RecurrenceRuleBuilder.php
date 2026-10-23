<?php

namespace App\Modules\Holiday\Http\Livewire;

use Livewire\Component;

/**
 * Livewire component that provides a user-friendly UI for building
 * holiday recurrence rules. Converts form inputs into structured JSON
 * stored in the `recurrence_rule` column.
 *
 * Pattern mirrors Payroll's PolicyCalculationBuilder.
 */
class RecurrenceRuleBuilder extends Component
{
    public ?string $existingJson = null;

    // Frequency
    public string $frequency = 'yearly';

    // Yearly — fixed date
    public int $yearlyMonth = 1;
    public int $yearlyDay = 1;

    // Yearly — floating (e.g. "3rd Monday of January")
    public string $yearlyFloatingWeek = 'first';
    public string $yearlyFloatingDay = 'monday';
    public int $yearlyFloatingMonth = 1;

    // Yearly mode toggle
    public string $yearlyMode = 'fixed'; // 'fixed' | 'floating'

    // Monthly
    public string $monthlyMode = 'day_of_month'; // 'day_of_month' | 'day_of_week'
    public int $monthlyDayOfMonth = 1;
    public string $monthlyWeekOfMonth = 'first';
    public string $monthlyDayOfWeek = 'monday';
    public int $monthlyInterval = 1;

    // Weekly
    public array $weeklyDays = [];
    public int $weeklyInterval = 1;

    // Daily
    public int $dailyInterval = 1;

    // End condition
    public string $endType = 'never'; // 'never' | 'after' | 'date'
    public int $endAfterCount = 12;
    public ?string $endDate = null;

    // Preview
    public string $generatedRule = '';

    protected $listeners = [
        'parentRecurrencePatternChanged' => 'setRecurrencePattern',
    ];

    public function mount(?string $existingJson = null): void
    {
        $this->existingJson = $existingJson;
        $this->loadFromJson();
    }

    // -----------------------------------------------------------------
    // Lifecycle hooks — update parent on any change
    // -----------------------------------------------------------------

    public function updatedFrequency(): void
    {
        $this->updateParent();
    }

    public function updatedYearlyMode(): void
    {
        $this->updateParent();
    }

    public function updatedYearlyMonth(): void { $this->updateParent(); }
    public function updatedYearlyDay(): void { $this->updateParent(); }
    public function updatedYearlyFloatingWeek(): void { $this->updateParent(); }
    public function updatedYearlyFloatingDay(): void { $this->updateParent(); }
    public function updatedYearlyFloatingMonth(): void { $this->updateParent(); }

    public function updatedMonthlyMode(): void { $this->updateParent(); }
    public function updatedMonthlyDayOfMonth(): void { $this->updateParent(); }
    public function updatedMonthlyWeekOfMonth(): void { $this->updateParent(); }
    public function updatedMonthlyDayOfWeek(): void { $this->updateParent(); }
    public function updatedMonthlyInterval(): void { $this->updateParent(); }

    public function updatedWeeklyDays(): void { $this->updateParent(); }
    public function updatedWeeklyInterval(): void { $this->updateParent(); }

    public function updatedDailyInterval(): void { $this->updateParent(); }

    public function updatedEndType(): void { $this->updateParent(); }
    public function updatedEndAfterCount(): void { $this->updateParent(); }
    public function updatedEndDate(): void { $this->updateParent(); }

    // -----------------------------------------------------------------
    // JSON ↔ State
    // -----------------------------------------------------------------

    protected function loadFromJson(): void
    {
        if (empty($this->existingJson)) {
            $this->resetToDefault();
            return;
        }

        $data = json_decode($this->existingJson, true);
        if (!is_array($data)) {
            $this->resetToDefault();
            return;
        }

        $this->frequency = $data['frequency'] ?? 'yearly';

        // Yearly — fixed
        $this->yearlyMonth = (int) ($data['month'] ?? 1);
        $this->yearlyDay   = (int) ($data['day'] ?? 1);

        // Yearly — floating
        $this->yearlyMode = $data['yearly_mode'] ?? 'fixed';
        $this->yearlyFloatingWeek  = $data['floating_week'] ?? 'first';
        $this->yearlyFloatingDay   = $data['floating_day'] ?? 'monday';
        $this->yearlyFloatingMonth = (int) ($data['floating_month'] ?? 1);

        // Monthly
        $this->monthlyMode         = $data['monthly_mode'] ?? 'day_of_month';
        $this->monthlyDayOfMonth   = (int) ($data['day_of_month'] ?? 1);
        $this->monthlyWeekOfMonth  = $data['week_of_month'] ?? 'first';
        $this->monthlyDayOfWeek    = $data['day_of_week'] ?? 'monday';
        $this->monthlyInterval     = (int) ($data['interval'] ?? 1);

        // Weekly
        $this->weeklyDays    = $data['weekly_days'] ?? [];
        $this->weeklyInterval = (int) ($data['interval'] ?? 1);

        // Daily
        $this->dailyInterval = (int) ($data['interval'] ?? 1);

        // End
        $this->endType       = $data['end_type'] ?? 'never';
        $this->endAfterCount = (int) ($data['end_after_count'] ?? 12);
        $this->endDate       = $data['end_date'] ?? null;

        $this->updateParent();
    }

    protected function resetToDefault(): void
    {
        $this->frequency = 'yearly';
        $this->yearlyMode = 'fixed';
        $this->yearlyMonth = 1;
        $this->yearlyDay = 1;
        $this->yearlyFloatingWeek = 'first';
        $this->yearlyFloatingDay = 'monday';
        $this->yearlyFloatingMonth = 1;
        $this->monthlyMode = 'day_of_month';
        $this->monthlyDayOfMonth = 1;
        $this->monthlyWeekOfMonth = 'first';
        $this->monthlyDayOfWeek = 'monday';
        $this->monthlyInterval = 1;
        $this->weeklyDays = [];
        $this->weeklyInterval = 1;
        $this->dailyInterval = 1;
        $this->endType = 'never';
        $this->endAfterCount = 12;
        $this->endDate = null;
        $this->updateParent();
    }

    // -----------------------------------------------------------------
    // JSON generation
    // -----------------------------------------------------------------

    protected function buildJson(): string
    {
        $data = [
            'frequency' => $this->frequency,
            'interval'  => 1,
            'end_type'  => $this->endType,
        ];

        switch ($this->frequency) {
            case 'yearly':
                $data['yearly_mode'] = $this->yearlyMode;
                if ($this->yearlyMode === 'floating') {
                    $data['floating_week']  = $this->yearlyFloatingWeek;
                    $data['floating_day']   = $this->yearlyFloatingDay;
                    $data['floating_month'] = $this->yearlyFloatingMonth;
                } else {
                    $data['month'] = $this->yearlyMonth;
                    $data['day']   = $this->yearlyDay;
                }
                break;

            case 'monthly':
                $data['monthly_mode'] = $this->monthlyMode;
                $data['interval']     = $this->monthlyInterval;
                if ($this->monthlyMode === 'day_of_week') {
                    $data['week_of_month'] = $this->monthlyWeekOfMonth;
                    $data['day_of_week']   = $this->monthlyDayOfWeek;
                } else {
                    $data['day_of_month'] = $this->monthlyDayOfMonth;
                }
                break;

            case 'weekly':
                $data['weekly_days'] = $this->weeklyDays;
                $data['interval']    = $this->weeklyInterval;
                break;

            case 'daily':
                $data['interval'] = $this->dailyInterval;
                break;
        }

        if ($this->endType === 'after') {
            $data['end_after_count'] = $this->endAfterCount;
        } elseif ($this->endType === 'date') {
            $data['end_date'] = $this->endDate;
        }

        return json_encode($data);
    }

    protected function buildHumanReadable(): string
    {
        $parts = [];

        switch ($this->frequency) {
            case 'yearly':
                if ($this->yearlyMode === 'floating') {
                    $parts[] = 'The ' . ucfirst($this->yearlyFloatingWeek) . ' ' . ucfirst($this->yearlyFloatingDay) . ' of ' . date('F', mktime(0, 0, 0, $this->yearlyFloatingMonth, 1));
                } else {
                    $parts[] = date('F', mktime(0, 0, 0, $this->yearlyMonth, 1)) . ' ' . $this->yearlyDay;
                }
                break;

            case 'monthly':
                if ($this->monthlyMode === 'day_of_week') {
                    $parts[] = 'The ' . ucfirst($this->monthlyWeekOfMonth) . ' ' . ucfirst($this->monthlyDayOfWeek);
                } else {
                    $parts[] = 'Day ' . $this->monthlyDayOfMonth;
                }
                if ($this->monthlyInterval > 1) {
                    $parts[] = 'every ' . $this->monthlyInterval . ' months';
                } else {
                    $parts[] = 'every month';
                }
                break;

            case 'weekly':
                if (!empty($this->weeklyDays)) {
                    $dayNames = array_map(fn ($d) => ucfirst(substr($d, 0, 3)), $this->weeklyDays);
                    $parts[] = 'Every ' . implode(', ', $dayNames);
                } else {
                    $parts[] = 'Weekly';
                }
                if ($this->weeklyInterval > 1) {
                    $parts[] = 'every ' . $this->weeklyInterval . ' weeks';
                }
                break;

            case 'daily':
                if ($this->dailyInterval > 1) {
                    $parts[] = 'Every ' . $this->dailyInterval . ' days';
                } else {
                    $parts[] = 'Every day';
                }
                break;
        }

        if ($this->endType === 'after') {
            $parts[] = 'for ' . $this->endAfterCount . ' occurrences';
        } elseif ($this->endType === 'date' && $this->endDate) {
            $parts[] = 'until ' . $this->endDate;
        }

        return implode('; ', $parts);
    }

    protected function updateParent(): void
    {
        $json = $this->buildJson();
        $this->generatedRule = $this->buildHumanReadable();
        $this->dispatch('recurrenceRuleUpdated', $json);
    }

    // -----------------------------------------------------------------
    // Presets
    // -----------------------------------------------------------------

    public function applyPreset(string $preset): void
    {
        switch ($preset) {
            case 'thanksgiving':
                $this->frequency = 'yearly';
                $this->yearlyMode = 'floating';
                $this->yearlyFloatingWeek = 'fourth';
                $this->yearlyFloatingDay = 'thursday';
                $this->yearlyFloatingMonth = 11;
                break;

            case 'memorial_day':
                $this->frequency = 'yearly';
                $this->yearlyMode = 'floating';
                $this->yearlyFloatingWeek = 'last';
                $this->yearlyFloatingDay = 'monday';
                $this->yearlyFloatingMonth = 5;
                break;

            case 'labor_day':
                $this->frequency = 'yearly';
                $this->yearlyMode = 'floating';
                $this->yearlyFloatingWeek = 'first';
                $this->yearlyFloatingDay = 'monday';
                $this->yearlyFloatingMonth = 9;
                break;

            case 'monthly_first_monday':
                $this->frequency = 'monthly';
                $this->monthlyMode = 'day_of_week';
                $this->monthlyWeekOfMonth = 'first';
                $this->monthlyDayOfWeek = 'monday';
                $this->monthlyInterval = 1;
                break;

            case 'monthly_15th':
                $this->frequency = 'monthly';
                $this->monthlyMode = 'day_of_month';
                $this->monthlyDayOfMonth = 15;
                $this->monthlyInterval = 1;
                break;

            case 'biweekly_friday':
                $this->frequency = 'weekly';
                $this->weeklyDays = ['friday'];
                $this->weeklyInterval = 2;
                break;
        }

        $this->updateParent();
    }

    public function render()
    {
        return view('holiday::livewire.recurrence-rule-builder');
    }
}
