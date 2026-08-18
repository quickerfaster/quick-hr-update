<?php

namespace App\Modules\Organization\Providers;

use Illuminate\Support\ServiceProvider;

class OrganizationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Unset the 'core' flag inherited from the library's default config so
        // that ModuleServiceProvider and DiscoveryRegistrar discover this
        // app-level Organization module under app/Modules/Organization/.
        config()->set('ui-library.modules.organization.core', false);

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/settings.php',
            'organization.settings'
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
            $this->loadViewsFrom($viewsPath, 'organization');
        }

        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
    }
}
