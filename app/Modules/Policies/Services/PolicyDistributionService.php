<?php

namespace App\Modules\Policies\Services;

use App\Modules\Hr\Models\Employee;
use App\Modules\Policies\Models\CompanyPolicy;
use Illuminate\Database\Eloquent\Collection;

class PolicyDistributionService
{
    /**
     * Get all published policies that apply to a given employee.
     *
     * A policy applies to an employee if:
     * 1. It is published and belongs to the employee's company.
     * 2. Its distribution_scope is null (applies to all), OR
     *    the employee matches at least one scope criterion:
     *    - Department: employee's department_id is in scope.departments
     *    - Role: employee's user has a role in scope.roles
     *    - Location: employee's location_id is in scope.locations
     *
     * @param  Employee  $employee
     * @return Collection<int, CompanyPolicy>
     */
    public function getPoliciesForEmployee(Employee $employee): Collection
    {
        // Get all published policies for the company, then filter in PHP.
        // This avoids complex nested OR conditions with JSON queries that
        // are error-prone across different MySQL versions.
        $allPolicies = CompanyPolicy::published()
            ->where('company_id', $employee->company_id)
            ->orderBy('effective_date', 'desc')
            ->get();

        return $allPolicies->filter(function ($policy) use ($employee) {
            return $this->policyAppliesToEmployee($policy, $employee);
        })->values();
    }

    /**
     * Check whether a specific policy applies to an employee.
     */
    public function policyAppliesToEmployee(CompanyPolicy $policy, Employee $employee): bool
    {
        if ($policy->company_id !== $employee->company_id) {
            return false;
        }

        if ($policy->status !== 'published') {
            return false;
        }

        $scope = $policy->distribution_scope;

        // Null scope = applies to all employees in the company
        if (empty($scope)) {
            return true;
        }

        // Check department match
        if (!empty($scope['departments'])) {
            $position = $employee->employeePosition;
            if ($position && in_array($position->department_id, $scope['departments'])) {
                return true;
            }
        }

        // Check role match
        if (!empty($scope['roles'])) {
            $user = $employee->user;
            if ($user && $user->hasAnyRole((array) $scope['roles'])) {
                return true;
            }
        }

        // Check location match
        if (!empty($scope['locations'])) {
            $position = $employee->employeePosition;
            if ($position && in_array($position->location_id, $scope['locations'])) {
                return true;
            }
        }

        // If scopes are defined but none matched, policy does not apply
        if (!empty($scope['departments']) || !empty($scope['roles']) || !empty($scope['locations'])) {
            return false;
        }

        // Empty scope array (not null, but all keys empty) = applies to all
        return true;
    }

    /**
     * Apply distribution scope filters to the query as OR conditions.
     *
     * Uses a subquery approach: the policy applies if ANY of the scope
     * criteria match the employee.
     */
    protected function applyDistributionScopeFilter($query, Employee $employee): void
    {
        $position = $employee->employeePosition;
        $user = $employee->user;

        // Collect all scope match conditions
        $hasDepartment = $position && $position->department_id;
        $hasLocation = $position && $position->location_id;
        $userRoles = $user ? $user->getRoleNames()->toArray() : [];
        $hasRoles = !empty($userRoles);

        // If no scope criteria to match, don't add any conditions
        // (policies with non-null scope won't match this employee)
        if (!$hasDepartment && !$hasRoles && !$hasLocation) {
            // Add a condition that never matches so scoped policies are excluded
            $query->whereRaw('1 = 0');
            return;
        }

        $query->orWhere(function ($q) use ($position, $userRoles, $hasDepartment, $hasRoles, $hasLocation) {
            // Must have a distribution_scope set
            $q->whereNotNull('distribution_scope');

            // At least one scope criterion must match
            $q->where(function ($scope) use ($position, $userRoles, $hasDepartment, $hasRoles, $hasLocation) {
                // Department match
                if ($hasDepartment) {
                    $scope->orWhereJsonContains('distribution_scope->departments', $position->department_id);
                }

                // Role match
                if ($hasRoles) {
                    foreach ($userRoles as $role) {
                        $scope->orWhereJsonContains('distribution_scope->roles', $role);
                    }
                }

                // Location match
                if ($hasLocation) {
                    $scope->orWhereJsonContains('distribution_scope->locations', $position->location_id);
                }
            });
        });
    }
}
