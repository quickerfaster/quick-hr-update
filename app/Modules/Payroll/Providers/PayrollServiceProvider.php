<?php

namespace App\Modules\Payroll\Providers;

use Illuminate\Support\ServiceProvider;

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
    }
}