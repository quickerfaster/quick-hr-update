<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use QuickerFaster\UILibrary\Services\AccessControl\AuthorizationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \QuickerFaster\UILibrary\Contracts\Notifications\TemplateVariableRegistry::class,
            \App\Services\NotificationVariableRegistry::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerAdminGateBypass();
        $this->registerEmployeeOwnershipResolver();
    }

    /**
     * Register a Gate::before callback that grants all permissions to
     * admin roles (super_admin, admin, company_admin).
     *
     * This ensures that Laravel's native `can:` middleware and Gate
     * checks respect the same admin bypass used by AuthorizationService
     * throughout the rest of the system. Without this, admin roles
     * would get 403 errors on routes protected by `can:permission`
     * middleware because those permissions aren't explicitly assigned
     * to admin roles in the database.
     */
    protected function registerAdminGateBypass(): void
    {
        Gate::before(function ($user, $ability) {
            if (AuthorizationService::isBypassAllowed($user)) {
                return true;
            }

            return null; // fall through to normal permission checks
        });
    }

    /**
     * Register the callback that resolves a User to their Employee ID.
     *
     * This enables the record-ownership bypass in AuthorizationService::authorizeView(),
     * allowing ESS (Employee Self-Service) users to view their own records
     * (leave requests, payslips, attendance, etc.) without needing the
     * `view_{resource}` Spatie permission.
     */
    protected function registerEmployeeOwnershipResolver(): void
    {
        AuthorizationService::$resolveUserEmployeeId = function (\Illuminate\Contracts\Auth\Authenticatable $user): ?int {
            $employeeId = \App\Modules\Hr\Models\Employee::where('user_id', $user->id)->value('id');

            return $employeeId ? (int) $employeeId : null;
        };
    }
}
