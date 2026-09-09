<?php

namespace App\Modules\Hr\Providers;

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
        // Register HR-specific Livewire components
        Livewire::component('qf.employee-detail', \App\Modules\Hr\Http\Livewire\EmployeeDetail::class);
        Livewire::component('qf.searchable-employee-dropdown', \App\Modules\Hr\Http\Livewire\SearchableEmployeeDropdown::class);
        Livewire::component('qf.leave-hub', \App\Modules\Hr\Http\Livewire\LeaveHub::class);

        // Phase 6: HR Invitation integration components
        Livewire::component('qf.hr-invitation-form', \App\Modules\Hr\Http\Livewire\HrInvitationForm::class);
        Livewire::component('qf.hr-employee-form', \App\Modules\Hr\Http\Livewire\HrEmployeeForm::class);
        Livewire::component('qf.employee-invitation-status', \App\Modules\Hr\Http\Livewire\EmployeeInvitationStatus::class);

        // Phase 7: Onboarding Livewire components
        Livewire::component('qf.onboarding.employee-profile', \App\Modules\Hr\Http\Livewire\Onboarding\EmployeeProfileForm::class);
        Livewire::component('qf.onboarding.personal-details', \App\Modules\Hr\Http\Livewire\Onboarding\PersonalDetailsForm::class);
        Livewire::component('qf.onboarding.emergency-contact', \App\Modules\Hr\Http\Livewire\Onboarding\EmergencyContactForm::class);
        Livewire::component('qf.onboarding.bank-details', \App\Modules\Hr\Http\Livewire\Onboarding\BankDetailsForm::class);
        Livewire::component('qf.onboarding.documents', \App\Modules\Hr\Http\Livewire\Onboarding\DocumentsForm::class);
        Livewire::component('qf.onboarding.notification-preferences', \App\Modules\Hr\Http\Livewire\Onboarding\NotificationPreferencesForm::class);

        // Phase 7: Register Spatie Onboard steps from HR onboarding config
        // Skip during console commands (e.g. migrate) — route() resolution
        // requires the full HTTP route collection which isn't available in CLI.
        if (! $this->app->runningInConsole()) {
            $this->registerOnboardingSteps();
        }

        // Register invitation auto-linking listener (Phase 5)
        \Illuminate\Support\Facades\Event::listen(
            \QuickerFaster\UILibrary\Events\DataTableRecordSaved::class,
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
     * Register Spatie Onboard steps from the HR onboarding config.
     *
     * Each step defines a condition class (implementing OnboardingCondition),
     * a route for the step's form view, and display metadata. Steps are
     * registered in ascending order so the first incomplete step is
     * presented to the user after invitation acceptance.
     */
    private function registerOnboardingSteps(): void
    {
        $steps = config('hr_onboarding.employee_onboarding.steps', []);

        // Sort steps by order
        $sorted = collect($steps)->sortBy('order');

        foreach ($sorted as $step) {
            Onboard::addStep($step['label'])
                ->link(route($step['route']))
                ->cta('Continue')
                ->completeIf(function ($user) use ($step) {
                    if (isset($step['condition'])) {
                        $condition = app($step['condition']);

                        return $condition($user);
                    }

                    return false;
                });
        }
    }
}
