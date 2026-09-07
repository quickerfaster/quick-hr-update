<?php

namespace App\Modules\Hr\Providers;

use App\Models\User;
use App\Modules\Hr\Models\Company;
use App\Modules\Hr\Models\Employee;
use Illuminate\Support\Collection;
use QuickerFaster\UILibrary\Contracts\Navigation\CompanyProvider;

class HrsCompanyProvider implements CompanyProvider
{
    /**
     * Get all companies available to the given user.
     *
     * @param \Illuminate\Foundation\Auth\User|null $user
     * @return \Illuminate\Support\Collection
     */
    public function getCompanies($user): Collection
    {
        if (!$user instanceof User) {
            return collect();
        }

        // Super admins, company admins, and users with cross-company permission see all companies
        if ($this->canViewAllCompanies($user)) {
            return Company::all();
        }

        // Multi-company users: return companies from the company_user pivot table
        if (method_exists($user, 'companies')) {
            $companies = $user->companies;
            if ($companies->isNotEmpty()) {
                return $companies;
            }
        }

        // Fallback: single-company users via employee record
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return collect();
        }

        $company = $employee->company;

        if (!$company) {
            return collect();
        }

        return collect([$company]);
    }

    /**
     * Get the current company ID for the given user.
     *
     * @param \Illuminate\Foundation\Auth\User|null $user
     * @return int|null
     */
    public function getCurrentCompanyId($user): ?int
    {
        if (!$user instanceof User) {
            return null;
        }

        // Users with cross-company access default to "All Companies" mode
        if ($this->canViewAllCompanies($user)) {
            return 0;
        }

        // Multi-company users: default to their first assigned company
        if (method_exists($user, 'companies')) {
            $firstCompany = $user->companies->first();
            if ($firstCompany) {
                return $firstCompany->id;
            }
        }

        // Fallback: single-company users via employee record
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return null;
        }

        return $employee->company_id;
    }

    /**
     * Determine if the user can view data across all companies.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    protected function canViewAllCompanies(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasRole('company_admin')
            || $user->can('view_all_companies');
    }
}
