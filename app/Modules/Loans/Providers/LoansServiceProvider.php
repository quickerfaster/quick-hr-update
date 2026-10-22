<?php

namespace App\Modules\Loans\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class LoansServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/permissions.php',
            'loans.permissions'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * The library's ModuleServiceProvider also auto-discovers modules under
     * app/Modules/* and loads their views/routes/migrations by convention.
     * These explicit registrations make the module self-contained so it can
     * boot independently of that convention.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $viewsPath = __DIR__ . '/../Resources/views';
        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'loans');
        }

        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        // Custom DataTable for loan row actions (Submit, Approve, Reject, Activate)
        Livewire::component('qf.loans.data-table', \App\Modules\Loans\Http\Livewire\LoanDataTable::class);

        // Custom DataTable for loan repayment row actions (Mark as Paid, Waive)
        Livewire::component('qf.loans.repayment-data-table', \App\Modules\Loans\Http\Livewire\LoanRepaymentDataTable::class);

        // Phase 2: ESS My Loans Hub
        Livewire::component('qf.loans.my-hub', \App\Modules\Loans\Http\Livewire\MyLoansHub::class);

        // Phase 2: Loan Request Form (multi-step wizard)
        Livewire::component('qf.loans.request-form', \App\Modules\Loans\Http\Livewire\LoanRequestForm::class);

        // Phase 3: Loan Detail (info + workflow + repayments)
        Livewire::component('qf.loans.detail', \App\Modules\Loans\Http\Livewire\LoanDetail::class);

        // Phase 4: Console Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Modules\Loans\Console\Commands\ProcessLoanRepayments::class,
            ]);
        }

        // Register loan event listener for notifications
        \Illuminate\Support\Facades\Event::listen(
            \QuickerFaster\UILibrary\Events\DataTableRecordSaved::class,
            \App\Modules\Loans\Listeners\LoanEventListener::class
        );

        // Phase 5: Payroll integration — sync loan repayments with payroll runs
        \Illuminate\Support\Facades\Event::listen(
            \QuickerFaster\UILibrary\Events\DataTableRecordSaved::class,
            \App\Modules\Loans\Listeners\PayrollRunLoanSyncListener::class
        );

        // Sync loan status with workflow events (approved/rejected/recalled)
        \Illuminate\Support\Facades\Event::listen(
            \QuickerFaster\UILibrary\Events\Workflows\WorkflowApproved::class,
            [\App\Modules\Loans\Listeners\SyncLoanStatus::class, 'handleWorkflowApproved']
        );
        \Illuminate\Support\Facades\Event::listen(
            \QuickerFaster\UILibrary\Events\Workflows\WorkflowRejected::class,
            [\App\Modules\Loans\Listeners\SyncLoanStatus::class, 'handleWorkflowRejected']
        );
        \Illuminate\Support\Facades\Event::listen(
            \QuickerFaster\UILibrary\Events\Workflows\WorkflowRecalled::class,
            [\App\Modules\Loans\Listeners\SyncLoanStatus::class, 'handleWorkflowRecalled']
        );
    }
}
