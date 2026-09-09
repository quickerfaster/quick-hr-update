<?php

namespace App\Modules\Hr\Providers;

use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Spatie\Onboard\Facades\Onboard;

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

        $this->app->singleton(\App\Modules\Hr\Services\HrInvitationService::class);

        // Phase 8: Department-scoped invitation service
        $this->app->singleton(\App\Modules\Hr\Services\DepartmentScopedInvitationService::class);

        // Phase 7: Merge onboarding config
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/onboarding.php',
            'hr_onboarding'
        );

        // Phase 8: Merge invitation templates config
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/invitation_templates.php',
            'hr.invitation_templates'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load HR module routes (must happen before registerOnboardingSteps
        // so that route() resolution can find the onboarding route names).
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        // Register HR-specific Livewire components
        Livewire::component('qf.employee-detail', \App\Modules\Hr\Http\Livewire\EmployeeDetail::class);
        Livewire::component('qf.searchable-employee-dropdown', \App\Modules\Hr\Http\Livewire\SearchableEmployeeDropdown::class);
        Livewire::component('qf.leave-hub', \App\Modules\Hr\Http\Livewire\LeaveHub::class);

        // Phase 6: HR Invitation integration components
        Livewire::component('qf.hr-invitation-form', \App\Modules\Hr\Http\Livewire\HrInvitationForm::class);
        Livewire::component('qf.hr-employee-form', \App\Modules\Hr\Http\Livewire\HrEmployeeForm::class);
        Livewire::component('qf.employee-invitation-status', \App\Modules\Hr\Http\Livewire\EmployeeInvitationStatus::class);

        // Phase 7: Consolidated Onboarding Wizard
        Livewire::component('qf.onboarding.wizard', \App\Modules\Hr\Http\Livewire\Onboarding\EmployeeOnboardingWizard::class);
        Livewire::component('qf.onboarding.step1-employee-record', \App\Modules\Hr\Http\Livewire\Onboarding\Steps\Step1EmployeeRecord::class);
        Livewire::component('qf.onboarding.step2-employee-profile', \App\Modules\Hr\Http\Livewire\Onboarding\Steps\Step2EmployeeProfile::class);
        Livewire::component('qf.onboarding.step3-payroll-banking', \App\Modules\Hr\Http\Livewire\Onboarding\Steps\Step3PayrollBanking::class);
        Livewire::component('qf.onboarding.step4-documents', \App\Modules\Hr\Http\Livewire\Onboarding\Steps\Step4Documents::class);
        Livewire::component('qf.onboarding.step5-preferences', \App\Modules\Hr\Http\Livewire\Onboarding\Steps\Step5Preferences::class);

        // Phase 7: Register Spatie Onboard steps from HR onboarding config.
        // Skip during console commands (e.g. migrate) because the route
        // collection is not fully assembled in CLI context.
        //
        // IMPORTANT: Must be a direct call in boot() (not deferred via
        // $this->app->booted()). ModuleServiceProvider::registerOnboardingConfig()
        // registers library defaults during its own boot(), which runs before
        // this provider. The HR step carries User::class as its model so
        // Spatie Onboard places it in the model-specific bucket, which the
        // framework merges *before* the 'default' bucket, guaranteeing
        // the HR wizard step is returned by nextUnfinishedStep() first.
        if (! $this->app->runningInConsole()) {
            $this->registerOnboardingSteps();
        }

        // Register invitation auto-linking listener (Phase 5)
        // Listens for InvitationAccepted (fired by InvitationService::accept())
        // rather than DataTableRecordSaved, because the accept flow uses direct
        // Eloquent updates and never dispatches DataTableRecordSaved.
        \Illuminate\Support\Facades\Event::listen(
            \QuickerFaster\UILibrary\Events\Invitations\InvitationAccepted::class,
            \App\Modules\Hr\Listeners\LinkInvitationToEmployee::class
        );

        // Phase 6.1: Pre-link invitation to employee on creation
        \Illuminate\Support\Facades\Event::listen(
            \QuickerFaster\UILibrary\Events\DataTableRecordSaved::class,
            \App\Modules\Hr\Listeners\PreLinkInvitationToEmployee::class
        );

        // Phase 6.3: Auto-invite on employee creation
        \Illuminate\Support\Facades\Event::listen(
            \QuickerFaster\UILibrary\Events\DataTableRecordSaved::class,
            \App\Modules\Hr\Listeners\AutoInviteOnEmployeeCreate::class
        );
    }

    /**
     * Register Spatie Onboard step for the consolidated HR onboarding wizard.
     *
     * Registers a single "Employee Onboarding" step that points to /onboarding.
     * The wizard component manages its own internal sub-step state and skip
     * tracking. The Spatie Onboard condition checks only the required
     * Employee Record step — optional steps are managed within the wizard.
     *
     * This replaces the old 6 separate step registrations (Phase 7 original).
     */
    private function registerOnboardingSteps(): void
    {
        $wizardRoute = config('hr_onboarding.employee_onboarding.wizard_route', '/onboarding');

        // Pass User::class as the model so Spatie Onboard places this step
        // in the model-specific bucket. The framework merges model-specific
        // steps *before* 'default' steps, guaranteeing the HR wizard
        // appears before library defaults (Complete Your Profile, etc.).
        Onboard::addStep('Employee Onboarding', User::class)
            ->link($wizardRoute)
            ->cta('Complete Setup');
    }
}
