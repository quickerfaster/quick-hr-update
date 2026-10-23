<?php

namespace App\Modules\Holiday\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use QuickerFaster\UILibrary\Factories\FieldTypes\FieldFactory;

class HolidayServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
            $this->loadViewsFrom($viewsPath, 'holiday');
        }

        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        // Register console commands
        $this->commands([
            \App\Modules\Holiday\Console\Commands\RolloverHolidays::class,
        ]);

        // Register Livewire components
        Livewire::component('holiday.recurrence-rule-builder', \App\Modules\Holiday\Http\Livewire\RecurrenceRuleBuilder::class);
        Livewire::component('holiday.batch-date-selector', \App\Modules\Holiday\Http\Livewire\HolidayBatchDateSelector::class);
        Livewire::component('holiday.template-selector', \App\Modules\Holiday\Http\Livewire\HolidayTemplateSelector::class);
        Livewire::component('holiday.batch-preview', \App\Modules\Holiday\Http\Livewire\HolidayBatchPreview::class);
        Livewire::component('holiday.batch-defaults', \App\Modules\Holiday\Http\Livewire\HolidayBatchDefaults::class);
        Livewire::component('holiday.batch-step-one', \App\Modules\Holiday\Http\Livewire\HolidayBatchStepOne::class);

        // Register custom field types with the library's FieldFactory
        FieldFactory::registerType('recurrence_rule_builder', \App\Modules\Holiday\Components\FieldTypes\RecurrenceRuleBuilderField::class);
    }
}
