<?php

namespace App\Modules\Hr\Http\Livewire\Onboarding;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Modules\Hr\Models\Employee;

/**
 * Onboarding Step 1: Create or link an employee record.
 *
 * If the user already has a linked employee record, this step is
 * automatically marked complete and the user is redirected forward.
 */
class EmployeeProfileForm extends Component
{
    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $phone = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->email = $user->email ?? '';

        // Pre-fill if employee already exists
        $employee = Employee::where('user_id', $user->id)->first();
        if ($employee) {
            $this->first_name = $employee->first_name ?? '';
            $this->last_name = $employee->last_name ?? '';
            $this->phone = $employee->phone ?? '';
        }
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();

        $employee = Employee::where('user_id', $user->id)->first();

        if (! $employee) {
            $employee = new Employee();
            $employee->user_id = $user->id;
        }

        $employee->first_name = $this->first_name;
        $employee->last_name = $this->last_name;
        $employee->email = $this->email;
        $employee->phone = $this->phone;
        $employee->save();

        $this->redirectToNextStep();
    }

    public function skip(): void
    {
        $this->redirectToNextStep();
    }

    protected function redirectToNextStep(): void
    {
        $this->redirect(route('hr.onboarding.personal-details'));
    }

    public function render()
    {
        return view('hr::onboarding.employee-profile');
    }
}
