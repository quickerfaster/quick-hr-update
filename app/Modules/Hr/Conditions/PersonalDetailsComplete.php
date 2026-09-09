<?php

namespace App\Modules\Hr\Conditions;

use QuickerFaster\UILibrary\Contracts\OnboardingCondition;

/**
 * Checks if the employee has completed personal details:
 * first name, last name, phone, and address fields on the employee profile.
 */
class PersonalDetailsComplete implements OnboardingCondition
{
    public function __invoke($model): bool
    {
        $employee = \App\Modules\Hr\Models\Employee::where('user_id', $model->id)->first();

        if (! $employee) {
            return false;
        }

        // Basic employee fields must be filled
        if (empty($employee->first_name) || empty($employee->last_name) || empty($employee->phone)) {
            return false;
        }

        // Check profile for address details
        $profile = $employee->employeeProfile;

        if (! $profile) {
            return false;
        }

        return ! empty($profile->address_street)
            && ! empty($profile->address_city)
            && ! empty($profile->address_country);
    }
}
