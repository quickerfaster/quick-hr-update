<?php

namespace App\Modules\Hr\Conditions;

use QuickerFaster\UILibrary\Contracts\OnboardingCondition;

/**
 * Checks if the employee has bank details configured.
 *
 * Bank details are stored on the EmployeePayrollProfile model
 * (from the Payroll module). If the Payroll module is not installed,
 * this condition always returns true (step is skipped).
 */
class BankDetailsAdded implements OnboardingCondition
{
    public function __invoke($user): bool
    {
        $employee = \App\Modules\Hr\Models\Employee::where('user_id', $user->id)->first();

        if (! $employee) {
            return false;
        }

        // If Payroll module is not available, consider this step complete
        if (! class_exists(\App\Modules\Payroll\Models\EmployeePayrollProfile::class)) {
            return true;
        }

        $payrollProfile = $employee->employeePayrollProfile;

        if (! $payrollProfile) {
            return false;
        }

        return ! empty($payrollProfile->bank_name)
            && ! empty($payrollProfile->account_number);
    }
}
