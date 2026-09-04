<?php

namespace App\Modules\Attendance\Providers;

use Illuminate\Support\ServiceProvider;

class AttendanceServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the library's ClockEventRecorder contract to our implementation
        $this->app->bind(
            \QuickerFaster\UILibrary\Contracts\Attendance\ClockEventRecorder::class,
            \App\Modules\Attendance\Services\ClockEventRecorderService::class
        );

        // Livewire components under app/Modules/Attendance/Http/Livewire/ are
        // auto-discovered by the library's ModuleServiceProvider. No explicit
        // Livewire::component() registrations are needed here.
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
            $this->loadViewsFrom($viewsPath, 'attendance');
        }

        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
    }
}
