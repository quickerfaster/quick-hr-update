<?php

namespace App\Modules\Hr\Http\Livewire\Onboarding\Steps;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\EmployeeProfile;

/**
 * Onboarding Step 2: Employee Profile (OPTIONAL, skippable).
 *
 * Merges former Personal Details and Emergency Contact steps.
 * Creates or updates the EmployeeProfile with address and
 * emergency contact fields.
 */
class Step2EmployeeProfile extends Component
{
    // -- Section A: Personal Information --
    public string $date_of_birth = '';
    public string $gender = '';
    public string $nationality = '';
    public string $marital_status = '';
    public string $personal_email = '';
    public string $personal_phone = '';
    public string $address_street = '';
    public string $address_city = '';
    public string $address_state = '';
    public string $address_postal_code = '';
    public string $address_country = '';

    // -- Section B: Emergency Contact --
    public string $emergency_contact_name = '';
    public string $emergency_contact_phone = '';
    public string $emergency_contact_relationship = '';

    public function mount(): void
    {
        $employee = Employee::withoutCompanyScope()->where('user_id', Auth::id())->first();

        if ($employee) {
            $profile = EmployeeProfile::withoutCompanyScope()->where('employee_id', $employee->id)->first();

            if ($profile) {
                $this->date_of_birth = $profile->date_of_birth ? $profile->date_of_birth->format('Y-m-d') : '';
                $this->gender = $profile->gender ?? '';
                $this->nationality = $profile->nationality ?? '';
                $this->marital_status = $profile->marital_status ?? '';
                $this->personal_email = $profile->personal_email ?? '';
                $this->personal_phone = $profile->personal_phone ?? '';
                $this->address_street = $profile->address_street ?? '';
                $this->address_city = $profile->address_city ?? '';
                $this->address_state = $profile->address_state ?? '';
                $this->address_postal_code = $profile->address_postal_code ?? '';
                $this->address_country = $profile->address_country ?? '';
                $this->emergency_contact_name = $profile->emergency_contact_name ?? '';
                $this->emergency_contact_phone = $profile->emergency_contact_phone ?? '';
                $this->emergency_contact_relationship = $profile->emergency_contact_relationship ?? '';
            }

            // Default NOT NULL fields from employee record when profile
            // has no value, preventing NOT NULL constraint violations
            // when array_filter strips nulls in save().
            if (! $profile || ! $profile->personal_email) {
                $this->personal_email = $employee->email ?? '';
            }
            if (! $profile || ! $profile->personal_phone) {
                $this->personal_phone = $employee->phone ?? '';
            }
        }
    }

    public function rules(): array
    {
        return [
            'date_of_birth'              => 'nullable|date',
            'gender'                     => 'nullable|string|max:50',
            'nationality'                => 'nullable|string|max:100',
            'marital_status'             => 'nullable|string|max:50',
            'personal_email'             => 'nullable|email|max:255',
            'personal_phone'             => 'nullable|string|max:50',
            'address_street'             => 'nullable|string|max:255',
            'address_city'               => 'nullable|string|max:255',
            'address_state'              => 'nullable|string|max:255',
            'address_postal_code'        => 'nullable|string|max:20',
            'address_country'            => 'nullable|string|max:255',
            'emergency_contact_name'     => 'nullable|string|max:255',
            'emergency_contact_phone'    => 'nullable|string|max:50',
            'emergency_contact_relationship' => 'nullable|string|max:100',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $employee = Employee::withoutCompanyScope()->where('user_id', Auth::id())->first();

        if (! $employee) {
            return;
        }

        $attributes = [
            'date_of_birth'                  => $this->date_of_birth ?: null,
            'gender'                         => $this->gender ?: null,
            'nationality'                    => $this->nationality ?: null,
            'marital_status'                 => $this->marital_status ?: null,
            'personal_email'                 => $this->personal_email ?: null,
            'personal_phone'                 => $this->personal_phone ?: null,
            'address_street'                 => $this->address_street ?: null,
            'address_city'                   => $this->address_city ?: null,
            'address_state'                  => $this->address_state ?: null,
            'address_postal_code'            => $this->address_postal_code ?: null,
            'address_country'                => $this->address_country ?: null,
            'emergency_contact_name'         => $this->emergency_contact_name ?: null,
            'emergency_contact_phone'        => $this->emergency_contact_phone ?: null,
            'emergency_contact_relationship' => $this->emergency_contact_relationship ?: null,
        ];

        // Strip null values so NOT NULL columns (e.g. personal_email) are
        // never passed as null to updateOrCreate. On update, existing values
        // are preserved; on create, the DB default or a follow-up migration
        // should provide a sensible fallback.
        $attributes = array_filter($attributes, fn ($value) => ! is_null($value));

        EmployeeProfile::withoutCompanyScope()->updateOrCreate(
            ['employee_id' => $employee->id],
            $attributes,
        );

        $this->dispatch('stepComplete', step: 2);
    }

    public function skip(): void
    {
        $this->dispatch('skipStep');
    }

    public function render()
    {
        return view('hr::onboarding.steps.step2-employee-profile');
    }
}
