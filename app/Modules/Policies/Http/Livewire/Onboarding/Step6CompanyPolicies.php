<?php

namespace App\Modules\Policies\Http\Livewire\Onboarding;

use Livewire\Component;
use App\Modules\Hr\Models\Employee;
use App\Modules\Policies\Models\CompanyPolicy;
use App\Modules\Policies\Models\PolicyAcknowledgment;
use App\Modules\Policies\Services\PolicyDistributionService;

/**
 * Onboarding Step 6: Company Policies (OPTIONAL, skippable).
 *
 * Lists mandatory company policies that the new hire must read and
 * acknowledge. Each policy is shown as a card with a "Read & Acknowledge"
 * button. The step is complete when all mandatory policies are acknowledged.
 *
 * State persistence: Acknowledgments are stored in the database and
 * re-queried on mount(). The wizard blade destroys/recreates the
 * component on step navigation, so mount() always loads fresh.
 */
class Step6CompanyPolicies extends Component
{
    /** @var Employee */
    public $employee;

    /** @var \Illuminate\Database\Eloquent\Collection */
    public $policies;

    /** @var array Track which policies have been acknowledged in this session */
    public array $acknowledgedIds = [];

    /** @var int|null Policy ID currently being viewed in the modal */
    public $viewingPolicyId = null;

    /** @var string Typed signature for acknowledgment */
    public string $signature = '';

    /** @var bool True when all mandatory policies are acknowledged */
    public bool $allAcknowledged = false;

    protected $listeners = [
        'saveStep' => 'save',
        'skipStep' => 'skip',
    ];

    public function mount(?Employee $employee = null): void
    {
        $this->employee = $employee;

        if ($this->employee) {
            $this->loadPolicies();
        }
    }

    /**
     * Load mandatory policies for this employee and check acknowledgment status.
     */
    public function loadPolicies(): void
    {
        if (!$this->employee) {
            $this->policies = collect();
            return;
        }

        // Get policies that apply to this employee and require acknowledgment
        $distributionService = app(PolicyDistributionService::class);
        $allPolicies = $distributionService->getPoliciesForEmployee($this->employee);

        $this->policies = $allPolicies->filter(function ($policy) {
            return $policy->requires_acknowledgment;
        })->values();

        // Check which policies are already acknowledged
        $this->acknowledgedIds = PolicyAcknowledgment::where('employee_id', $this->employee->id)
            ->where('status', 'acknowledged')
            ->pluck('company_policy_id')
            ->toArray();

        $this->allAcknowledged = $this->policies->every(function ($policy) {
            return in_array($policy->id, $this->acknowledgedIds);
        });
    }

    /**
     * Open the policy reading modal.
     */
    public function viewPolicy(int $policyId): void
    {
        $this->viewingPolicyId = $policyId;
        $this->signature = '';
    }

    /**
     * Close the policy reading modal.
     */
    public function closeModal(): void
    {
        $this->viewingPolicyId = null;
        $this->signature = '';
    }

    /**
     * Acknowledge the currently viewed policy.
     */
    public function acknowledge(): void
    {
        if (!$this->viewingPolicyId || !$this->employee) {
            return;
        }

        PolicyAcknowledgment::updateOrCreate(
            [
                'company_policy_id' => $this->viewingPolicyId,
                'employee_id' => $this->employee->id,
            ],
            [
                'user_id' => auth()->id(),
                'status' => 'acknowledged',
                'acknowledged_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'signature' => $this->signature ?: null,
            ]
        );

        $this->closeModal();
        $this->loadPolicies();
    }

    /**
     * Save handler — dispatches stepComplete to advance the wizard.
     */
    public function save(): void
    {
        $this->dispatch('stepComplete', step: 6);
    }

    /**
     * Get the step index from the wizard's step configuration.
     */
    protected function getStepIndex(): int
    {
        // The wizard passes step index context; default to 5 (0-indexed step 6)
        return 5;
    }

    public function render()
    {
        return view('policies::livewire.onboarding.step6-company-policies');
    }
}
