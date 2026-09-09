<?php

namespace App\Modules\Hr\Http\Livewire\Onboarding;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\EmployeeProfile;

/**
 * Onboarding Step 2: Complete personal details.
 *
 * Updates the employee's name/phone and creates or updates the
 * EmployeeProfile with address information.
 */
class PersonalDetailsForm extends Component
{
    public string $first_name = '';

    public string $last_name = '';

    public string $phone = '';

    public string $address_street = '';

    public string $address_city = '';

    public string $address_state = '';

    public string $address_postal_code = '';

    public string $address_country = '';

    public string $date_of_birth = '';

    public string $gender = '';

    public function mount(): void
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if ($employee) {
            $this->first_name = $employee->first_name ?? '';
            $this->last_name = $employee->last_name ?? '';
            $this->phone = $employee->phone ?? '';

            $profile = $employee->employeeProfile;
            if ($profile) {
                $this->address_street = $profile->address_street ?? '';
                $this->address_city = $profile->address_city ?? '';
                $this->address_state = $profile->address_state ?? '';
                $this->address_postal_code = $profile->address_postal_code ?? '';
                $this->address_country = $profile->address_country ?? '';
                $this->date_of_birth = $profile->date_of_birth ? $profile->date_of_birth->format('Y-m-d') : '';
                $this->gender = $profile->gender ?? '';
            }
        }
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'address_street' => 'required|string|max:255',
            'address_city' => 'required|string|max:255',
            'address_state' => 'nullable|string|max:255',
            'address_postal_code' => 'nullable|string|max:20',
            'address_country' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:50',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if ($employee) {
            $employee->first_name = $this->first_name;
            $employee->last_name = $this->last_name;
            $employee->phone = $this->phone;
            $employee->save();

            $profile = $employee->employeeProfile ?? new EmployeeProfile();
            $profile->employee_id = $employee->id;
            $profile->address_street = $this->address_street;
            $profile->address_city = $this->address_city;
            $profile->address_state = $this->address_state;
            $profile->address_postal_code = $this->address_postal_code;
            $profile->address_country = $this->address_country;
            $profile->date_of_birth = $this->date_of_birth ?: null;
            $profile->gender = $this->gender;
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
        $this->redirect(route('hr.onboarding.emergency-contact'));
    }

    public function render()
    {
        return view('hr::onboarding.personal-details');
    }
}
