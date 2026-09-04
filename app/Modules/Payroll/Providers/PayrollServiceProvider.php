<?php

namespace App\Modules\Payroll\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Modules\Payroll\Listeners\SyncPayrollRunStatus;
use QuickerFaster\UILibrary\Events\Workflows\WorkflowApproved;
use QuickerFaster\UILibrary\Events\Workflows\WorkflowRejected;
use QuickerFaster\UILibrary\Events\Workflows\WorkflowRecalled;
use Illuminate\Support\Facades\Event;

class PayrollServiceProvider extends ServiceProvider
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
            $this->loadViewsFrom($viewsPath, 'payroll');
        }

        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        // Register Payroll Livewire components
        Livewire::component('qf.payroll-run-detail', \App\Modules\Payroll\Http\Livewire\Payroll\PayrollRunDetail::class);
        Livewire::component('qf.payroll-run-wizard', \App\Modules\Payroll\Http\Livewire\Payroll\PayrollRunWizard::class);
        Livewire::component('qf.payroll-wizard-adjustments', \App\Modules\Payroll\Http\Livewire\Payroll\PayrollWizardAdjustments::class);
        Livewire::component('qf.payroll-wizard-preview', \App\Modules\Payroll\Http\Livewire\Payroll\PayrollWizardPreview::class);
        Livewire::component('qf.payslip-items', \App\Modules\Payroll\Http\Livewire\Payroll\PayslipItems::class);
        Livewire::component('qf.policy-calculation-builder', \App\Modules\Payroll\Http\Livewire\Payroll\PolicyCalculationBuilder::class);
        Livewire::component('payroll.executive-summary', \App\Modules\Payroll\Http\Livewire\Payroll\PayrollExecutiveSummary::class);

        Event::listen(
            WorkflowApproved::class,
            [SyncPayrollRunStatus::class, 'handleWorkflowApproved']
        );
        Event::listen(
            WorkflowRejected::class,
            [SyncPayrollRunStatus::class, 'handleWorkflowRejected']
        );
        Event::listen(
            WorkflowRecalled::class,
            [SyncPayrollRunStatus::class, 'handleWorkflowRecalled']
        );
    }
}