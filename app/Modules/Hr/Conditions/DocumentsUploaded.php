<?php

namespace App\Modules\Hr\Conditions;

use QuickerFaster\UILibrary\Contracts\OnboardingCondition;

/**
 * Checks if the employee has uploaded required documents.
 *
 * An employee is considered to have uploaded documents if they have
 * at least one document record associated with their employee record.
 */
class DocumentsUploaded implements OnboardingCondition
{
    public function __invoke($user): bool
    {
        $employee = \App\Modules\Hr\Models\Employee::where('user_id', $user->id)->first();

        if (! $employee) {
            return false;
        }

        return $employee->documents()->count() > 0;
    }
}
