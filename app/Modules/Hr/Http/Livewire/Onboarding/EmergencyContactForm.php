<?php

namespace App\Modules\Hr\Http\Livewire\Onboarding;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\EmployeeProfile;

/**
 * Onboarding Step 3: Add emergency contact information.
 *
 * Updates the emergency contact fields on the EmployeeProfile model.
 */
class EmergencyContactForm extends Component
{
    public string $emergency_contact_name = '';

    public string $emergency_contact_phone = '';

    public string $emergency_contact_relationship = '';

    public function mount(): void
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if ($employee) {
            $profile = $employee->employeeProfile;
            if ($profile) {
                $this->emergency_contact_name = $profile->emergency_contact_name ?? '';
                $this->emergency_contact_phone = $profile->emergency_contact_phone ?? '';
                $this->emergency_contact_relationship = $profile->emergency_contact_relationship ?? '';
            }
        }
    }

    public function rules(): array
    {
        return [
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:50',
            'emergency_contact_relationship' => 'required|string|max:100',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if ($employee) {
            $profile = $employee->employeeProfile ?? new EmployeeProfile();
            $profile->employee_id = $employee->id;
            $profile->emergency_contact_name = $this->emergency_contact_name;
            $profile->emergency_contact_phone = $this->emergency_contact_phone;
            $profile->emergency_contact_relationship = $this->emergency_contact_relationship;
            $profile->save();
        }

        $this->redirectToNextStep();
    }

    public function skip(): void
    {
        $this->redirectToNextStep();
    }

    protected function redirectToNextStep(): void
    {
        $this->redirect(route('hr.onboarding.bank-details'));
    }

    public function render()
    {
        return view('hr::onboarding.emergency-contact');
    }
}
