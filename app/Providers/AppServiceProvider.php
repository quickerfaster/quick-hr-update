<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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
        $this->registerEmployeeOwnershipResolver();
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
