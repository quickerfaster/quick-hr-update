<?php

namespace App\Modules\Payroll\Http\Livewire\Payroll;

use Livewire\Component;
use App\Modules\Payroll\Models\EmployeeSalaryComponent;
use Illuminate\Support\Facades\Auth;
use QuickerFaster\UILibrary\Traits\HasCurrencySymbol;

/**
 * Salary components repeater editor.
 *
 * Renders a country-specific list of salary components (Basic, Housing,
 * Transport, Other Allowance) for an employee and persists them to
 * employee_salary_components.
 */
class SalaryComponentsEditor extends Component
{
    use HasCurrencySymbol;

    public ?int $employeeId = null;
    public array $rows = [];
    public string $currencySymbol = '₦';

    public function mount(?int $employeeId = null): void
    {
        $this->employeeId = $employeeId;
        $this->currencySymbol = $this->resolveCurrencySymbol();
        $this->load();
    }

    public function load(): void
    {
        $this->rows = [];

        if (!$this->employeeId) {
            $this->rows[] = ['component_key' => '', 'amount' => 0];
            return;
        }

        $existing = EmployeeSalaryComponent::withoutCompanyScope()
            ->where('employee_id', $this->employeeId)
            ->orderBy('component_key')
            ->get();

        if ($existing->isEmpty()) {
            $this->rows[] = ['component_key' => '', 'amount' => 0];
            return;
        }

        foreach ($existing as $component) {
            $this->rows[] = [
                'component_key' => $component->component_key,
                'amount'        => (float) $component->amount,
            ];
        }
    }

    public function addRow(): void
    {
        $this->rows[] = ['component_key' => '', 'amount' => 0];
    }

    public function removeRow(int $index): void
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);

        if (empty($this->rows)) {
            $this->rows[] = ['component_key' => '', 'amount' => 0];
        }
    }

    public function save(): void
    {
        if (!$this->employeeId) {
            $this->dispatch('showAlert', [
                'type'    => 'warning',
                'message' => 'Save the employee first before adding salary components.',
            ]);
            return;
        }

        // Normalise and validate
        $valid = [];
        foreach ($this->rows as $row) {
            $key = trim((string) ($row['component_key'] ?? ''));
            $amount = (float) ($row['amount'] ?? 0);

            if ($key === '') {
                continue;
            }

            $valid[] = [
                'component_key' => $key,
                'amount'        => $amount,
            ];
        }

        EmployeeSalaryComponent::withoutCompanyScope()
            ->where('employee_id', $this->employeeId)
            ->delete();

        foreach ($valid as $row) {
            EmployeeSalaryComponent::create([
                'company_id'     => Auth::user()?->current_company_id,
                'employee_id'    => $this->employeeId,
                'component_key'  => $row['component_key'],
                'amount'         => $row['amount'],
                'currency_code'  => 'NGN',
                'effective_date' => now()->toDateString(),
            ]);
        }

        $this->load();

        $this->dispatch('showAlert', [
            'type'    => 'success',
            'message' => 'Salary components saved.',
        ]);
    }

    public function getComponentTypes(): array
    {
        return config('quick_hr_payroll.salary_component_types.NG', [
            'basic' => 'Basic Salary',
            'housing' => 'Housing Allowance',
            'transport' => 'Transport Allowance',
            'other_allowance' => 'Other Allowance',
        ]);
    }

    protected function resolveCurrencySymbol(): string
    {
        $companyId = session('current_company_id');

        if ($companyId) {
            $company = \App\Modules\Hr\Models\Company::find($companyId);
            $code = $company?->currency_code ?? 'NGN';
        } else {
            $code = 'NGN';
        }

        return $this->getCurrencySymbol($code);
    }

    public function getTotalGross(): float
    {
        return array_sum(array_map(fn ($row) => (float) ($row['amount'] ?? 0), $this->rows));
    }

    public function getPensionableBht(): float
    {
        $group = config('quick_hr_payroll.component_groups.NG.pensionable', ['basic', 'housing', 'transport']);
        $sum = 0.0;

        foreach ($this->rows as $row) {
            if (in_array($row['component_key'] ?? '', $group, true)) {
                $sum += (float) ($row['amount'] ?? 0);
            }
        }

        return $sum;
    }

    public function render()
    {
        return view('payroll::livewire.payroll.salary-components-editor', [
            'componentTypes' => $this->getComponentTypes(),
            'totalGross'     => $this->getTotalGross(),
            'pensionableBht' => $this->getPensionableBht(),
            'currencySymbol' => $this->currencySymbol,
        ]);
    }
}
