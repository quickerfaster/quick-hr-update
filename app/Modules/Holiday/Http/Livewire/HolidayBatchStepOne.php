<?php

namespace App\Modules\Holiday\Http\Livewire;

use Livewire\Component;
use App\Modules\Holiday\Models\HolidayCalendar;
use App\Modules\Holiday\Traits\AdvancesWizardStep;
use App\Modules\Organization\Models\Company;

/**
 * Step 1 of the Holiday Batch Creation wizard.
 *
 * Renders calendar selector, target year, and creation method.
 * In multi-company mode, shows a company filter above the
 * calendar dropdown so users can scope calendars by company.
 */
class HolidayBatchStepOne extends Component
{
    use AdvancesWizardStep;

    public string $configKey;
    public int $stepIndex;
    public ?int $recordId = null;
    public ?int $primaryModelId = null;
    public array $presetData = [];
    public string $wizardId;

    public ?int $companyId = null;
    public ?int $calendarId = null;
    public int $targetYear;
    public string $sourceOption = 'copy_previous';

    public array $companies = [];
    public array $calendars = [];
    public bool $isMultiCompany = false;

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

        $this->targetYear = (int) ($presetData['target_year'] ?? now()->year + 1);
        $this->sourceOption = $presetData['source_option'] ?? 'copy_previous';

        // Load active companies
        $this->companies = Company::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        // Multi-company mode only when NO specific company is active in the
        // session. If a current company is selected, the user is scoped to
        // that company and the selector should be hidden.
        $this->isMultiCompany = empty(session('current_company_id'));

        // Restore from session
        $sessionKey = $this->getSessionKey();
        if (session()->has($sessionKey)) {
            $saved = session()->get($sessionKey);
            $this->companyId    = $saved['companyId'] ?? null;
            $this->calendarId   = $saved['calendarId'] ?? null;
            $this->targetYear   = $saved['targetYear'] ?? $this->targetYear;
            $this->sourceOption = $saved['sourceOption'] ?? $this->sourceOption;
        } else {
            // Pre-select current session company (single-company mode)
            $this->companyId = session('current_company_id') ?: null;
        }

        // Load calendars (filtered by company if selected)
        $this->loadCalendars();
    }

    public function updatedCompanyId(): void
    {
        $this->calendarId = null; // reset calendar when company changes
        $this->loadCalendars();
        $this->saveToSession();
    }

    public function updatedCalendarId(): void { $this->saveToSession(); }
    public function updatedTargetYear(): void { $this->saveToSession(); }
    public function updatedSourceOption(): void { $this->saveToSession(); }

    protected function loadCalendars(): void
    {
        $query = HolidayCalendar::withoutCompanyScope()
            ->where('is_active', true)
            ->orderBy('name');

        if ($this->companyId) {
            $query->where('company_id', $this->companyId);
        }

        $this->calendars = $query->get(['id', 'name', 'year', 'country_code', 'company_id'])->toArray();
    }

    protected function getSessionKey(): string
    {
        return $this->wizardId . '-step-one';
    }

    protected function saveToSession(): void
    {
        session()->put($this->getSessionKey(), [
            'companyId'    => $this->companyId,
            'calendarId'   => $this->calendarId,
            'targetYear'   => $this->targetYear,
            'sourceOption' => $this->sourceOption,
        ]);
    }

    /**
     * Hook used by the AdvancesWizardStep trait to persist state
     * before advancing the wizard.
     */
    protected function saveStepData(): void
    {
        $this->saveToSession();
    }

    /**
     * Called by subsequent steps to get Step 1 data.
     */
    public static function getStepOneData(string $wizardId): array
    {
        $key = $wizardId . '-step-one';
        return session()->get($key, []);
    }

    public function render()
    {
        return view('holiday::livewire.holiday-batch-step-one');
    }
}
