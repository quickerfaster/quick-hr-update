<?php

namespace App\Modules\Policies\Conditions;

use QuickerFaster\UILibrary\Contracts\OnboardingCondition;
use App\Modules\Hr\Models\Employee;
use App\Modules\Policies\Models\CompanyPolicy;
use App\Modules\Policies\Models\PolicyAcknowledgment;
use App\Modules\Policies\Services\PolicyDistributionService;

/**
 * Checks if the employee has acknowledged all mandatory company policies.
 *
 * An employee is considered to have completed policy acknowledgment when
 * all published policies that require acknowledgment and apply to them
 * have been acknowledged.
 */
class PoliciesAcknowledged implements OnboardingCondition
{
    public function __invoke($model): bool
    {
        $employee = Employee::withoutCompanyScope()
            ->where('user_id', $model->id)
            ->first();

        if (!$employee) {
            // No employee record yet — can't check policies
            return true;
        }

        // Get mandatory policies for this employee
        $distributionService = app(PolicyDistributionService::class);
        $mandatoryPolicies = $distributionService->getPoliciesForEmployee($employee)
            ->filter(function ($policy) {
                return $policy->requires_acknowledgment;
            });

        if ($mandatoryPolicies->isEmpty()) {
            // No mandatory policies — condition is satisfied
            return true;
        }

        // Check that all mandatory policies are acknowledged
        $acknowledgedIds = PolicyAcknowledgment::where('employee_id', $employee->id)
            ->where('status', 'acknowledged')
            ->pluck('company_policy_id')
            ->toArray();

        return $mandatoryPolicies->every(function ($policy) use ($acknowledgedIds) {
            return in_array($policy->id, $acknowledgedIds);
        });
    }
}
