<?php

namespace App\Modules\Hr\Conditions;

use QuickerFaster\UILibrary\Contracts\OnboardingCondition;

/**
 * Checks if the employee has at least one emergency contact configured.
 *
 * Emergency contact fields live on the EmployeeProfile model:
 * emergency_contact_name, emergency_contact_phone, emergency_contact_relationship.
 */
class EmergencyContactAdded implements OnboardingCondition
{
    public function __invoke($user): bool
    {
        $employee = \App\Modules\Hr\Models\Employee::where('user_id', $user->id)->first();

        if (! $employee) {
            return false;
        }

        $profile = $employee->employeeProfile;

        if (! $profile) {
            return false;
        }

        return ! empty($profile->emergency_contact_name)
            && ! empty($profile->emergency_contact_phone)
            && ! empty($profile->emergency_contact_relationship);
    }
}
