<?php

namespace App\Modules\Holiday\Http\Livewire;

use Livewire\Component;
use App\Modules\Holiday\Traits\AdvancesWizardStep;

/**
 * Optional wizard step that lets the user set batch-level defaults
 * for all holidays being created. These override individual template
 * values and are stored in the session for the submission service.
 */
class HolidayBatchDefaults extends Component
{
    use AdvancesWizardStep;

    public string $configKey;
    public int $stepIndex;
    public ?int $recordId = null;
    public ?int $primaryModelId = null;
    public array $presetData = [];
    public string $wizardId;

    // Default values for the batch
    public float $defaultPayRate = 1.0;
    public string $defaultBusinessImpact = 'office_closed';
    public array $defaultEligibleEmployeeTypes = ['full_time', 'part_time'];
    public bool $defaultIsPaid = true;
    public bool $defaultIsRecurring = true;
    public bool $applyDefaults = false; // toggle to enable/disable

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

        // Restore from session
        $sessionKey = $this->getSessionKey();
        if (session()->has($sessionKey)) {
            $saved = session()->get($sessionKey);
            $this->applyDefaults              = $saved['applyDefaults'] ?? false;
            $this->defaultPayRate             = $saved['defaultPayRate'] ?? 1.0;
            $this->defaultBusinessImpact      = $saved['defaultBusinessImpact'] ?? 'office_closed';
            $this->defaultEligibleEmployeeTypes = $saved['defaultEligibleEmployeeTypes'] ?? ['full_time', 'part_time'];
            $this->defaultIsPaid              = $saved['defaultIsPaid'] ?? true;
            $this->defaultIsRecurring         = $saved['defaultIsRecurring'] ?? true;
        }
    }

    public function updatedApplyDefaults(): void { $this->saveToSession(); }
    public function updatedDefaultPayRate(): void { $this->saveToSession(); }
    public function updatedDefaultBusinessImpact(): void { $this->saveToSession(); }
    public function updatedDefaultEligibleEmployeeTypes(): void { $this->saveToSession(); }
    public function updatedDefaultIsPaid(): void { $this->saveToSession(); }
    public function updatedDefaultIsRecurring(): void { $this->saveToSession(); }

    protected function getSessionKey(): string
    {
        return $this->wizardId . '-batch-defaults';
    }

    protected function saveToSession(): void
    {
        session()->put($this->getSessionKey(), [
            'applyDefaults'              => $this->applyDefaults,
            'defaultPayRate'             => $this->defaultPayRate,
            'defaultBusinessImpact'      => $this->defaultBusinessImpact,
            'defaultEligibleEmployeeTypes' => $this->defaultEligibleEmployeeTypes,
            'defaultIsPaid'              => $this->defaultIsPaid,
            'defaultIsRecurring'         => $this->defaultIsRecurring,
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
     * Called by the submission service to get batch defaults.
     */
    public static function getDefaults(string $wizardId): ?array
    {
        $key = $wizardId . '-batch-defaults';
        $data = session()->get($key, []);

        if (empty($data) || !($data['applyDefaults'] ?? false)) {
            return null;
        }

        return $data;
    }

    public function render()
    {
        return view('holiday::livewire.holiday-batch-defaults');
    }
}
