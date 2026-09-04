<?php

namespace App\Modules\Leave\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Leave\Listeners\SyncLeaveRequestStatus;
use QuickerFaster\UILibrary\Events\Workflows\WorkflowApproved;
use QuickerFaster\UILibrary\Events\Workflows\WorkflowRejected;
use QuickerFaster\UILibrary\Events\Workflows\WorkflowRecalled;
use Illuminate\Support\Facades\Event;

class LeaveServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \QuickerFaster\UILibrary\Contracts\FieldTypes\CalendarEnhancementProvider::class,
            \App\Modules\Leave\Services\LeaveCalendarEnhancementProvider::class
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
            $this->loadViewsFrom($viewsPath, 'leave');
        }

        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        // Register Leave-specific Livewire components
        \Livewire\Livewire::component('leave-wizard-form', \App\Modules\Leave\Http\Livewire\LeaveWizardForm::class);
        \Livewire\Livewire::component('leave-document-upload', \App\Modules\Leave\Http\Livewire\LeaveDocumentUpload::class);

        Event::listen(
            WorkflowApproved::class,
            [SyncLeaveRequestStatus::class, 'handleWorkflowApproved']
        );
        Event::listen(
            WorkflowRejected::class,
            [SyncLeaveRequestStatus::class, 'handleWorkflowRejected']
        );
        Event::listen(
            WorkflowRecalled::class,
            [SyncLeaveRequestStatus::class, 'handleWorkflowRecalled']
        );
    }
}