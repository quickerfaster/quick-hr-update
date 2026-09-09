<?php

namespace App\Modules\Hr\Conditions;

use QuickerFaster\UILibrary\Contracts\OnboardingCondition;

/**
 * Checks if the employee has completed their profile.
 *
 * Merges former PersonalDetailsComplete (address fields) and
 * EmergencyContactAdded (emergency contact fields) into one condition.
 * Both sets of fields live on the EmployeeProfile model.
 *
 * Step 2 of the consolidated onboarding wizard.
 */
class EmployeeProfileComplete implements OnboardingCondition
{
    public function __invoke($model): bool
    {
        $employee = \App\Modules\Hr\Models\Employee::where('user_id', $model->id)->first();

        if (! $employee) {
            return false;
        }

        $profile = $employee->employeeProfile;

        if (! $profile) {
            return false;
        }

        // Address fields (formerly PersonalDetailsComplete)
        $hasAddress = ! empty($profile->address_street)
            && ! empty($profile->address_city)
            && ! empty($profile->address_country);

        // Emergency contact fields (formerly EmergencyContactAdded)
        $hasEmergencyContact = ! empty($profile->emergency_contact_name)
            && ! empty($profile->emergency_contact_phone)
            && ! empty($profile->emergency_contact_relationship);

        // Both sections must be filled for the profile to be considered complete
        return $hasAddress && $hasEmergencyContact;
    }
}
