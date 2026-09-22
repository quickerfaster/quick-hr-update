<?php

namespace App\Modules\Hr\Observers;

use App\Modules\Hr\Models\Employee;

class EmployeeOnboardingObserver
{
    /**
     * Auto-compute onboarding_status before saving.
     */
    public function saving(Employee $employee): void
    {
        // Not yet accepted invitation — no status
        if (! $employee->user_id) {
            $employee->onboarding_status = null;
            return;
        }

        // Has user but no company — critical gap
        if (! $employee->company_id) {
            $employee->onboarding_status = 'company_pending';
            return;
        }

        // Company assigned — position check happens in saved()
        // because the position relationship may not be persisted yet
    }

    /**
     * After save, check position status (relationship now available).
     */
    public function saved(Employee $employee): void
    {
        // Only compute if user is present
        if (! $employee->user_id) {
            return;
        }

        // Resolve company: prefer direct employee.company_id, fall back
        // to user's assigned companies (set via qf.user-company-assignment).
        $hasCompany = (bool) $employee->company_id;

        if (! $hasCompany && $employee->relationLoaded('user')) {
            $hasCompany = $employee->user->companies()->count() > 0;
        } elseif (! $hasCompany) {
            $hasCompany = \App\Models\User::find($employee->user_id)
                ?->companies()->count() > 0;
        }

        if (! $hasCompany) {
            if ($employee->onboarding_status !== 'company_pending') {
                Employee::withoutEvents(fn () =>
                    $employee->update(['onboarding_status' => 'company_pending'])
                );
            }
            return;
        }

        $hasPosition = $employee->employeePosition()->exists();

        $status = $hasPosition ? 'complete' : 'position_pending';

        if ($employee->onboarding_status !== $status) {
            Employee::withoutEvents(fn () =>
                $employee->update(['onboarding_status' => $status])
            );
        }
    }
}
