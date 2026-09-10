<?php

namespace App\Modules\Hr\Http\Livewire\Onboarding;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Auth;
use App\Modules\Hr\Models\Employee;

/**
 * Consolidated Employee Onboarding Wizard.
 *
 * Single-page wizard at /onboarding that replaces the old 6 separate
 * onboarding pages. Each step saves independently — there is no
 * multi-step form submission. The wizard manages its own internal
 * step state and skip tracking.
 */
#[Layout('qf::layouts.app')]
class EmployeeOnboardingWizard extends Component
{
    /** @var int Current step (1-indexed) */
    #[Url(as: 'step')]
    public int $currentStep = 1;

    /** @var Employee|null The authenticated user's linked employee record */
    public $employee = null;

    /** @var int|null Employee ID (set after Step 1 saves) */
    public $employeeId = null;

    /** @var bool True when employee was pre-linked to user before onboarding */
    public bool $isPreLinked = false;

    /** @var bool True when Payroll module is available */
    public bool $payrollAvailable = false;

    /** @var array Internal skip tracking: step keys explicitly skipped */
    public array $skippedSteps = [];

    /** @var array Internal completion tracking: step keys confirmed complete */
    public array $completedSteps = [];

    public function mount(): void
    {
        $user = Auth::user();

        // Step 1: Check for pre-linked employee (e.g. from invitation)
        $employee = Employee::withoutCompanyScope()->where('user_id', $user->id)->first();

        if ($employee) {
            $this->employee = $employee;
            $this->employeeId = $employee->id;
            $this->isPreLinked = true;

            // Step 1 is pre-completed when employee already exists
            $this->completedSteps['employee_record'] = true;
        }

        // Step 2: Check if EmployeeProfile exists
        if ($this->employeeId) {
            $profile = \App\Modules\Hr\Models\EmployeeProfile::withoutCompanyScope()
                ->where('employee_id', $this->employeeId)->first();
            if ($profile) {
                $this->completedSteps['employee_profile'] = true;
            }
        }

        // Step 3: Check if Payroll profile exists (only if module available)
        $this->payrollAvailable = class_exists(\App\Modules\Payroll\Models\EmployeePayrollProfile::class);
        if ($this->payrollAvailable && $this->employeeId) {
            $payroll = \App\Modules\Payroll\Models\EmployeePayrollProfile::withoutCompanyScope()
                ->where('employee_id', $this->employeeId)->first();
            if ($payroll) {
                $this->completedSteps['payroll_banking'] = true;
            }
        }

        // Step 4: Check if notification preferences are set
        if (method_exists($user, 'getSetting')) {
            $prefs = $user->getSetting('notification_preferences');
            if (!empty($prefs)) {
                $this->completedSteps['notification_preferences'] = true;
            }
        }
    }

    /**
     * Get the consolidated step definitions.
     *
     * Dynamically excludes the payroll step when the Payroll module
     * is not installed.
     */
    public function getStepsProperty(): array
    {
        $steps = config('hr_onboarding.employee_onboarding.steps', []);

        // Sort by order
        usort($steps, fn ($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

        // Filter out payroll step when module unavailable
        if (! $this->payrollAvailable) {
            $steps = array_values(array_filter($steps, fn ($s) => $s['key'] !== 'payroll_banking'));
        }

        return $steps;
    }

    /**
     * Navigate to a specific step.
     */
    public function goToStep(int $step): void
    {
        $steps = $this->steps;

        if ($step < 1 || $step > count($steps)) {
            return;
        }

        // Only block FORWARD navigation past incomplete step 1.
        // Backward navigation (to earlier steps) is always allowed.
        if ($step > 1 && $step > $this->currentStep) {
            $stepOneKey = $steps[0]['key'] ?? 'employee_record';
            if (! isset($this->completedSteps[$stepOneKey])) {
                return;
            }
        }

        $this->currentStep = $step;
    }

    /**
     * Advance to the next step.
     */
    public function nextStep(): void
    {
        $steps = $this->steps;
        $total = count($steps);

        if ($this->currentStep >= $total) {
            $this->finish();

            return;
        }

        $this->currentStep++;
    }

    /**
     * Called by child step components via event when a step is saved/skipped.
     */
    public function onStepComplete(int $step, int $employeeId = null): void
    {
        if ($employeeId) {
            $this->employeeId = $employeeId;
            $this->employee = Employee::withoutCompanyScope()->find($employeeId);
        }

        $steps = $this->steps;
        $index = $step - 1;

        if (isset($steps[$index])) {
            $this->completedSteps[$steps[$index]['key']] = true;
        }
    }

    /**
     * Captures the employeeId dispatched by Step 1 via the stepSaved event.
     */
    #[On('stepSaved')]
    public function onStepSaved(int $employeeId): void
    {
        $this->employeeId = $employeeId;
        $this->employee = Employee::withoutCompanyScope()->find($employeeId);
    }

    /**
     * Skip the current optional step.
     */
    public function skipStep(): void
    {
        $steps = $this->steps;
        $index = $this->currentStep - 1;

        if (! isset($steps[$index])) {
            return;
        }

        $stepConfig = $steps[$index];

        // Only optional steps can be skipped
        if (! empty($stepConfig['required'])) {
            return;
        }

        $this->skippedSteps[$stepConfig['key']] = true;
        $this->completedSteps[$stepConfig['key']] = true;

        $this->nextStep();
    }

    /**
     * Mark all remaining steps as skipped and redirect to dashboard.
     */
    public function finish(): void
    {
        $this->redirect(route(config('ui-library.home_route', 'admin.dashboard')));
    }

    /**
     * Get the step config for the current step.
     */
    public function getCurrentStepConfigProperty(): ?array
    {
        $steps = $this->steps;
        $index = $this->currentStep - 1;

        return $steps[$index] ?? null;
    }

    /**
     * Determine if the current step has been completed.
     */
    public function isCurrentStepComplete(): bool
    {
        $config = $this->currentStepConfig;

        if (! $config) {
            return false;
        }

        return isset($this->completedSteps[$config['key']]);
    }

    /**
     * Determine if the current step has been skipped.
     */
    public function isCurrentStepSkipped(): bool
    {
        $config = $this->currentStepConfig;

        if (! $config) {
            return false;
        }

        return isset($this->skippedSteps[$config['key']]);
    }

    /**
     * Get the Livewire component name for the current step.
     */
    public function getCurrentStepComponentProperty(): string
    {
        $config = $this->currentStepConfig;

        return $config['component'] ?? 'qf.onboarding.step1-employee-record';
    }

    /**
     * Check if current step is the last step.
     */
    public function getIsLastStepProperty(): bool
    {
        return $this->currentStep >= count($this->steps);
    }

    /**
     * Check if current step is required.
     */
    public function getCurrentStepRequiredProperty(): bool
    {
        $config = $this->currentStepConfig;

        return ! empty($config['required']);
    }

    /**
     * Get step status for the sidebar indicator.
     */
    public function getStepStatus(string $stepKey): string
    {
        if (isset($this->skippedSteps[$stepKey])) {
            return 'skipped';
        }

        if (isset($this->completedSteps[$stepKey])) {
            return 'complete';
        }

        $currentKey = $this->currentStepConfig['key'] ?? null;
        if ($stepKey === $currentKey) {
            return 'current';
        }

        return 'pending';
    }

    /**
     * Get the current step number's key.
     */
    public function getStepKeyFor(int $stepNumber): ?string
    {
        $steps = $this->steps;
        $index = $stepNumber - 1;

        return $steps[$index]['key'] ?? null;
    }

    /**
     * Get progress percentage (completed / total).
     */
    public function getProgressPercentProperty(): int
    {
        $steps = $this->steps;
        $total = count($steps);

        if ($total === 0) {
            return 100;
        }

        $completed = 0;
        foreach ($steps as $step) {
            if (isset($this->completedSteps[$step['key']]) || isset($this->skippedSteps[$step['key']])) {
                $completed++;
            }
        }

        return (int) round(($completed / $total) * 100);
    }

    public function render()
    {
        return view('hr::onboarding.wizard', [
            'steps' => $this->steps,
        ]);
    }
}
