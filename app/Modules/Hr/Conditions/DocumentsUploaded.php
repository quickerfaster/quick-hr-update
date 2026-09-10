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
    public function __invoke($model): bool
    {
        $employee = \App\Modules\Hr\Models\Employee::withoutCompanyScope()
            ->where('user_id', $model->id)
            ->first();

        if (! $employee) {
            return false;
        }

        // Uses the HasDocuments trait's MorphMany (library Document),
        // not the legacy HR Document model's hasMany.
        return $employee->documents()->count() > 0;
    }
}
