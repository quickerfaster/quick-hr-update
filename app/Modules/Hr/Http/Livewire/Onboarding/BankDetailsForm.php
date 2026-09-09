<?php

namespace App\Modules\Hr\Http\Livewire\Onboarding;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Modules\Hr\Models\Employee;

/**
 * Onboarding Step 4: Add bank details for salary payments.
 *
 * Creates or updates the EmployeePayrollProfile with bank information.
 * If the Payroll module is not available, this step redirects forward.
 */
class BankDetailsForm extends Component
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

        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if ($employee) {
            $payrollProfile = $employee->employeePayrollProfile;
            if ($payrollProfile) {
                $this->bank_name = $payrollProfile->bank_name ?? '';
                $this->account_number = $payrollProfile->account_number ?? '';
                $this->account_name = $payrollProfile->account_name ?? '';
                $this->bank_code = $payrollProfile->bank_code ?? '';
            }
        }
    }

    public function rules(): array
    {
        return [
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'account_name' => 'required|string|max:255',
            'bank_code' => 'nullable|string|max:50',
        ];
    }

    public function save(): void
    {
        if (! $this->payrollAvailable) {
            $this->redirectToNextStep();

            return;
        }

        $this->validate();

        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if ($employee && class_exists(\App\Modules\Payroll\Models\EmployeePayrollProfile::class)) {
            $payrollProfile = $employee->employeePayrollProfile ?? new \App\Modules\Payroll\Models\EmployeePayrollProfile();
            $payrollProfile->employee_id = $employee->id;
            $payrollProfile->bank_name = $this->bank_name;
            $payrollProfile->account_number = $this->account_number;
            $payrollProfile->account_name = $this->account_name;
            $payrollProfile->bank_code = $this->bank_code;
            $payrollProfile->save();
        }

        $this->redirectToNextStep();
    }

    public function skip(): void
    {
        $this->redirectToNextStep();
    }

    protected function redirectToNextStep(): void
    {
        $this->redirect(route('hr.onboarding.documents'));
    }

    public function render()
    {
        return view('hr::onboarding.bank-details');
    }
}
