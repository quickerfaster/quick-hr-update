<?php

namespace App\Modules\Hr\Http\Livewire\Onboarding\Steps;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Modules\Hr\Models\Employee;

/**
 * Onboarding Step 3: Payroll & Banking (OPTIONAL, skippable).
 *
 * Creates or updates the EmployeePayrollProfile with bank details.
 * Only rendered when the Payroll module is installed.
 */
class Step3PayrollBanking extends Component
{
    public string $bank_name = '';

    public string $account_number = '';

    public string $account_name = '';

    public string $bank_code = '';

    public bool $payrollAvailable = false;

    public function mount(): void
    {
        $this->payrollAvailable = class_exists(\App\Modules\Payroll\Models\EmployeePayrollProfile::class);

        if (! $this->payrollAvailable) {
            return;
        }

        $employee = Employee::withoutCompanyScope()->where('user_id', Auth::id())->first();

        if ($employee) {
            $payrollProfile = \App\Modules\Payroll\Models\EmployeePayrollProfile::withoutCompanyScope()
                ->where('employee_id', $employee->id)
                ->first();

            if ($payrollProfile) {
                $this->bank_name = $payrollProfile->bank_name ?? '';
                $this->account_number = $payrollProfile->bank_account_number ?? '';
                $this->account_name = $payrollProfile->bank_account_name ?? '';
                $this->bank_code = $payrollProfile->bank_code ?? '';
            }
        }
    }

    public function rules(): array
    {
        return [
            'bank_name'       => 'nullable|string|max:255',
            'account_number'  => 'nullable|string|max:50',
            'account_name'    => 'nullable|string|max:255',
            'bank_code'       => 'nullable|string|max:50',
        ];
    }

    public function save(): void
    {
        if (! $this->payrollAvailable) {
            $this->dispatch('stepComplete', step: 3);

            return;
        }

        $this->validate();

        $employee = Employee::withoutCompanyScope()->where('user_id', Auth::id())->first();

        if (! $employee) {
            return;
        }

        if (class_exists(\App\Modules\Payroll\Models\EmployeePayrollProfile::class)) {
            $defaultSchedule = \App\Modules\Payroll\Models\PaySchedule::where('is_default', true)->first();
            $companyId = $employee->company_id;

            \App\Modules\Payroll\Models\EmployeePayrollProfile::withoutCompanyScope()->updateOrCreate(
                ['employee_id' => $employee->id],
                [
                    'company_id'          => $companyId,
                    'pay_schedule_id'     => $defaultSchedule?->id,
                    'effective_date'      => now(),
                    'bank_name'           => $this->bank_name ?: null,
                    'bank_account_number' => $this->account_number ?: null,
                    'bank_account_name'   => $this->account_name ?: null,
                    'bank_code'           => $this->bank_code ?: null,
                ]
            );
        }

        $this->dispatch('stepComplete', step: 3);
    }

    public function skip(): void
    {
        $this->dispatch('skipStep');
    }

    public function render()
    {
        return view('hr::onboarding.steps.step3-payroll-banking');
    }
}
