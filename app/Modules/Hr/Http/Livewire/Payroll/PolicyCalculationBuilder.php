<?php

namespace App\Modules\Hr\Http\Livewire\Payroll;

use Livewire\Component;

class PolicyCalculationBuilder extends Component
{
    public string $policyType = 'benefit';
    public ?string $existingJson = null;

    // For tax – three‑column bands
    public array $bands = [];
    public array $bandErrors = [];

    // For non‑tax
    public string $calculationType = 'percentage';
    public float $employeeValue = 0;
    public float $employerValue = 0;
    public string $calculationBase = 'base_salary'; // NEW: base_salary or gross_pay

    protected $listeners = [
        'parentPolicyTypeChanged' => 'setPolicyType',
    ];

    public function mount(string $policyType, ?string $existingJson = null)
    {
        $this->policyType = $policyType;
        $this->existingJson = $existingJson;
        $this->loadFromJson();
    }

    public function setPolicyType(string $newType): void
    {
        $this->policyType = $newType;
        $this->resetToDefault();
    }

    // ---------- Tax Bands Methods ----------
    public function addBand(): void
    {
        $this->bands[] = ['start' => '', 'end' => '', 'rate' => ''];
        $this->validateBands();
        $this->updatedBands();
    }

    public function removeBand(int $index): void
    {
        unset($this->bands[$index]);
        $this->bands = array_values($this->bands);
        if (empty($this->bands)) {
            $this->bands = [['start' => '', 'end' => '', 'rate' => '']];
        }
        $this->validateBands();
        $this->updatedBands();
    }

    public function updatedBands(): void
    {
        $this->validateBands();
        $this->updateParent();
    }

    protected function validateBands(): void
    {
        $this->bandErrors = [];

        $nonEmptyBands = array_filter($this->bands, function ($band) {
            return ($band['start'] !== '' && $band['start'] !== null) ||
                   ($band['end'] !== '' && $band['end'] !== null) ||
                   ($band['rate'] !== '' && $band['rate'] !== null);
        });

        if (empty($nonEmptyBands)) {
            return;
        }

        $parsed = [];
        foreach ($nonEmptyBands as $idx => $band) {
            $start = $band['start'] === '' ? null : (float) $band['start'];
            $end = $band['end'] === '' ? null : (float) $band['end'];
            $rate = $band['rate'] === '' ? null : (float) $band['rate'];

            if ($rate === null) {
                $this->bandErrors[$idx] = 'Rate is required.';
                continue;
            }

            if ($idx === 0 && $start !== null && $start != 0) {
                $this->bandErrors[$idx] = 'First bracket must start at 0.';
            }

            $parsed[] = ['start' => $start, 'end' => $end, 'rate' => $rate, 'index' => $idx];
        }

        for ($i = 0; $i < count($parsed); $i++) {
            $current = $parsed[$i];
            $prev = $i > 0 ? $parsed[$i-1] : null;
            $next = $i < count($parsed)-1 ? $parsed[$i+1] : null;

            $currentStart = $current['start'] ?? 0;
            $currentEnd = $current['end'];

            if ($prev && $prev['end'] !== null && $currentStart < $prev['end']) {
                $this->bandErrors[$current['index']] = 'Overlap: bracket starts before previous bracket ends.';
            }
            if ($prev && $prev['end'] !== null && $currentStart != $prev['end']) {
                if (!isset($this->bandErrors[$current['index']])) {
                    $this->bandErrors[$current['index']] = 'Gap: bracket does not start immediately after previous bracket.';
                }
            }
            if ($next && $currentEnd !== null && $currentEnd > $next['start']) {
                $this->bandErrors[$current['index']] = 'Overlap: bracket ends after next bracket starts.';
            }
        }
    }

    // ---------- Non‑Tax Methods ----------

    protected function showEmployeeField(): bool
    {
        return $this->policyType !== 'tax';
    }

    protected function showEmployerField(): bool
    {
        return in_array($this->policyType, ['pension', 'insurance', 'benefit']);
    }

    public function updatedCalculationType(): void
    {
        $this->updateParent();
    }

    public function updatedEmployeeValue(): void
    {
        $this->employeeValue = $this->employeeValue ?? 0;
        $this->updateParent();
    }

    public function updatedEmployerValue(): void
    {
        $this->employerValue = $this->employerValue ?? 0;
        $this->updateParent();
    }

    // NEW: trigger update when base changes
    public function updatedCalculationBase(): void
    {
        $this->updateParent();
    }

    // ---------- JSON Handling ----------
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

        // Load base (new field) – default to base_salary if missing
        $this->calculationBase = $data['base'] ?? 'base_salary';

        if ($this->policyType === 'tax') {
            $rawBands = $data['bands'] ?? [];
            if (empty($rawBands)) {
                $this->bands = [['start' => '', 'end' => '', 'rate' => '']];
            } else {
                $this->bands = array_map(function ($band) {
                    if (isset($band['start'], $band['rate'])) {
                        return [
                            'start' => $band['start'] ?? '',
                            'end' => $band['end'] ?? '',
                            'rate' => $band['rate'],
                        ];
                    }
                    if (isset($band[0], $band[1])) {
                        return [
                            'start' => $band[0] ?? '',
                            'end' => $band[1] ?? '',
                            'rate' => $band[2] ?? 0,
                        ];
                    }
                    return ['start' => '', 'end' => '', 'rate' => ''];
                }, $rawBands);
            }
        } else {
            $this->calculationType = $data['calculation_type'] ?? 'percentage';
            $this->employeeValue = $data['employee_value'] ?? 0;
            $this->employerValue = $data['employer_value'] ?? 0;
        }
    }

    protected function resetToDefault(): void
    {
        // Preserve base if already set, else default
        $this->calculationBase = $this->calculationBase ?? 'base_salary';

        if ($this->policyType === 'tax') {
            $this->bands = [['start' => '', 'end' => '', 'rate' => '']];
            $this->bandErrors = [];
        } else {
            $this->calculationType = 'percentage';
            $this->employeeValue = 0;
            $this->employerValue = 0;
        }
        $this->updateParent();
    }

    protected function buildJson(): string
    {
        $data = ['base' => $this->calculationBase];

        if ($this->policyType === 'tax') {
            $cleanBands = [];
            foreach ($this->bands as $band) {
                $start = isset($band['start']) && $band['start'] !== '' ? (float) $band['start'] : null;
                $end = isset($band['end']) && $band['end'] !== '' ? (float) $band['end'] : null;
                $rate = isset($band['rate']) && $band['rate'] !== '' ? (float) $band['rate'] : null;

                if (($start === null || $start == 0) && ($end === null || $end == 0) && ($rate === null || $rate == 0)) {
                    continue;
                }

                $cleanBands[] = [
                    'start' => $start,
                    'end' => $end,
                    'rate' => $rate,
                ];
            }
            if (empty($cleanBands)) {
                $cleanBands = [['start' => 0, 'end' => null, 'rate' => 0]];
            }
            $data['bands'] = $cleanBands;
        } else {
            $data['calculation_type'] = $this->calculationType;
            $data['employee_value'] = (float) $this->employeeValue;
            $data['employer_value'] = (float) $this->employerValue;
        }

        return json_encode($data);
    }

    protected function updateParent(): void
    {
        $json = $this->buildJson();
        $this->dispatch('calculationLogicUpdated', $json);
    }

    public function render()
    {
        return view('hr::livewire.payroll.policy-calculation-builder');
    }
}
