<?php

namespace App\Modules\Hr\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class HrsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \QuickerFaster\UILibrary\Contracts\Navigation\CompanyProvider::class,
            \App\Modules\Hr\Providers\HrsCompanyProvider::class
        );

        $this->app->bind(
            \QuickerFaster\UILibrary\Contracts\Approvals\ApproverResolver::class,
            \App\Modules\Hr\Providers\HrsApproverResolver::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register HR-specific Livewire components
        Livewire::component('qf.employee-detail', \App\Modules\Hr\Http\Livewire\EmployeeDetail::class);
        Livewire::component('qf.searchable-employee-dropdown', \App\Modules\Hr\Http\Livewire\SearchableEmployeeDropdown::class);
        Livewire::component('qf.leave-hub', \App\Modules\Hr\Http\Livewire\LeaveHub::class);

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
        // - Bind WorkspaceResolver for multi-tenant scoping.
        //
    }
}
