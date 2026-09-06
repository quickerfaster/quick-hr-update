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

        if ($user->hasRole('super_admin')) {
            return Company::all();
        }

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

        if ($user->hasRole('super_admin')) {
            return 0;
        }

        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return null;
        }

        return $employee->company_id;
    }
}
