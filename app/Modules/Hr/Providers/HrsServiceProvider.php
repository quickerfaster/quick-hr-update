<?php

namespace App\Modules\Hr\Providers;

use Illuminate\Support\ServiceProvider;

class HrsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/quick_hr_payroll.php',
            'quick_hr_payroll'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        // TODO (Phase 2 — deep integration):
        // - Bind QuickerFaster\UILibrary\Contracts\ApproverResolver
        //   + ApproverLabelResolver (leave approvers).
        // - Merge ui-library workflow definitions for 'leave_request'
        //   and 'payroll_run'.
        // - Register HR notification templates / channels.
        // - Add Spatie permission seeder for HR permissions.
        // - Register Reportable implementations in
        //   ui-library.reports.report_types.
        // - Bind WorkspaceResolver + CompanyProvider for
        //   multi-tenant scoping.
        //
    }
}
