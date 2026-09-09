<?php

namespace App\Modules\Hr\Http\Livewire\Onboarding\Steps;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Modules\Hr\Models\Employee;
use QuickerFaster\UILibrary\Services\ValueGenerator;

/**
 * Onboarding Step 1: Employee Record (REQUIRED).
 *
 * Creates or updates the Employee model record for the authenticated user.
 * If the employee was pre-linked (e.g. from invitation), the form is
 * pre-filled and the user only needs to confirm.
 */
class Step1EmployeeRecord extends Component
{
    public string $first_name = '';

    public string $last_name = '';

    public string $email= '';

    public string $phone = '';

    public string $hire_date = '';

    public bool $isPreLinked = false;

    public function mount(bool $isPreLinked = false, $employee = null): void
    {
        $user = Auth::user();
        $this->email = $user->email ?? '';
        $this->hire_date = date('Y-m-d');
        $this->isPreLinked = $isPreLinked;

        if ($employee && is_object($employee)) {
            $this->first_name = $employee->first_name ?? '';
            $this->last_name = $employee->last_name ?? '';
            $this->phone = $employee->phone ?? '';

            if ($employee->hire_date) {
                $this->hire_date = $employee->hire_date instanceof \Carbon\Carbon
                    ? $employee->hire_date->format('Y-m-d')
                    : $employee->hire_date;
            }
        } else {
            // Load existing employee data on remount (e.g., when navigating Back from Step 2)
            $existingEmployee = Employee::withoutCompanyScope()->where('user_id', $user->id)->first();

            if ($existingEmployee) {
                $this->first_name = $existingEmployee->first_name ?? '';
                $this->last_name = $existingEmployee->last_name ?? '';
                $this->phone = $existingEmployee->phone ?? '';

                if ($existingEmployee->hire_date) {
                    $this->hire_date = $existingEmployee->hire_date instanceof \Carbon\Carbon
                        ? $existingEmployee->hire_date->format('Y-m-d')
                        : $existingEmployee->hire_date;
                }
            }
        }
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'phone'      => 'nullable|string|max:50',
            'hire_date'  => 'required|date',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();

        // Resolve employee_number before updateOrCreate so NOT NULL constraint is satisfied.
        // Preserve existing number for updates; auto-generate only for new records.
        $existingEmployee = Employee::withoutCompanyScope()->where('user_id', $user->id)->first();
        $employeeNumber = $existingEmployee?->employee_number;

        if (empty($employeeNumber)) {
            $generator = app(ValueGenerator::class);
            $employeeNumber = $generator->generate(
                Employee::class,
                'employee_number',
                ['autoGenerate' => true],
            );
        }

        $employee = Employee::withoutCompanyScope()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'employee_number' => $employeeNumber,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'hire_date' => $this->hire_date,
            ]
        );

        $this->dispatch('stepComplete', employeeId: $employee->id, step: 1);
        $this->dispatch('stepSaved', employeeId: $employee->id);
    }

    public function render()
    {
        return view('hr::onboarding.steps.step1-employee-record');
    }
}
