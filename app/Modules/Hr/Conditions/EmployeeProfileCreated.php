<?php

namespace App\Modules\Hr\Conditions;

use QuickerFaster\UILibrary\Contracts\OnboardingCondition;

/**
 * Checks if the authenticated user has a linked employee record.
 *
 * Step 1 of the post-acceptance onboarding flow. If no employee record
 * exists yet, the user is directed to create one.
 */
class EmployeeProfileCreated implements OnboardingCondition
{
    public function __invoke($model): bool
    {
        return \App\Modules\Hr\Models\Employee::where('user_id', $model->id)->exists();
    }
}
