<?php

namespace App\Modules\Policies\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class PoliciesServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/permissions.php',
            'policies.permissions'
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
            $this->loadViewsFrom($viewsPath, 'policies');
        }

        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        // Custom DataTable for policy row actions (Publish, Archive)
        Livewire::component('qf.policies.data-table', \App\Modules\Policies\Http\Livewire\PolicyDataTable::class);

        // Phase 2: ESS Policy Hub
        Livewire::component('qf.policies.my-hub', \App\Modules\Policies\Http\Livewire\MyPoliciesHub::class);

        // Phase 4: Onboarding Integration
        Livewire::component('qf.onboarding.step6-company-policies', \App\Modules\Policies\Http\Livewire\Onboarding\Step6CompanyPolicies::class);

        // Phase 5: Console Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Modules\Policies\Console\Commands\SendPolicyReminders::class,
            ]);
        }

        // Register policy event listener for notifications
        \Illuminate\Support\Facades\Event::listen(
            \QuickerFaster\UILibrary\Events\DataTableRecordSaved::class,
            \App\Modules\Policies\Listeners\PolicyEventListener::class
        );
    }
}
