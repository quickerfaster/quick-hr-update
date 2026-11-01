<?php

namespace App\Modules\Policies\Http\Livewire;

use Livewire\Component;
use App\Modules\Hr\Models\Employee;
use App\Modules\Policies\Models\CompanyPolicy;
use App\Modules\Policies\Models\PolicyAcknowledgment;

class MyPoliciesHub extends Component
{
    /** @var int */
    public $employeeId;

    /** @var string */
    public string $search = '';

    /** @var string */
    public string $categoryFilter = '';

    /** @var int|null Policy ID to show in acknowledgment modal */
    public $acknowledgingPolicyId = null;

    /** @var string Optional typed signature */
    public string $signature = '';

    /** @var \Illuminate\Database\Eloquent\Collection */
    public $pendingPolicies;

    /** @var \Illuminate\Database\Eloquent\Collection */
    public $acknowledgedPolicies;

    /** @var array Available policy categories */
    public array $categories = [
        'handbook' => 'Handbook',
        'code_of_conduct' => 'Code of Conduct',
        'leave_policy' => 'Leave Policy',
        'expense_policy' => 'Expense Policy',
        'it_policy' => 'IT Policy',
        'health_safety' => 'Health & Safety',
        'other' => 'Other',
    ];

    protected $listeners = [
        'policyAcknowledged' => 'refreshPolicies',
    ];

    public function mount(int $employeeId): void
    {
        $this->employeeId = $employeeId;
        $this->loadPolicies();
    }

    public function updatedSearch(): void
    {
        $this->loadPolicies();
    }

    public function updatedCategoryFilter(): void
    {
        $this->loadPolicies();
    }

    /**
     * Load policies scoped to the employee, grouped by acknowledgment status.
     */
    public function loadPolicies(): void
    {
        $employee = Employee::with('employeePosition')->find($this->employeeId);

        if (!$employee) {
            $this->pendingPolicies = collect();
            $this->acknowledgedPolicies = collect();
            return;
        }

        // Base query: published policies for the employee's company
        $query = CompanyPolicy::published()
            ->where('company_id', $employee->company_id)
            ->when($this->search, function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%');
            })
            ->when($this->categoryFilter, function ($q) {
                $q->where('category', $this->categoryFilter);
            });

        // Get all matching policies
        $allPolicies = $query->orderBy('effective_date', 'desc')
            ->get();

        // Get acknowledged policy IDs for this employee
        $acknowledgedIds = PolicyAcknowledgment::where('employee_id', $this->employeeId)
            ->where('status', 'acknowledged')
            ->pluck('company_policy_id')
            ->toArray();

        // Split into pending and acknowledged
        $this->pendingPolicies = $allPolicies->filter(function ($policy) use ($acknowledgedIds) {
            // Only show policies that require acknowledgment and haven't been acknowledged
            return $policy->requires_acknowledgment && !in_array($policy->id, $acknowledgedIds);
        })->values();

        $this->acknowledgedPolicies = $allPolicies->filter(function ($policy) use ($acknowledgedIds) {
            // Show acknowledged policies + policies that don't require acknowledgment
            return !$policy->requires_acknowledgment || in_array($policy->id, $acknowledgedIds);
        })->values();
    }

    /**
     * Open the acknowledgment modal for a specific policy.
     */
    public function openAcknowledgment(int $policyId): void
    {
        $this->acknowledgingPolicyId = $policyId;
        $this->signature = '';
    }

    /**
     * Close the acknowledgment modal.
     */
    public function closeAcknowledgment(): void
    {
        $this->acknowledgingPolicyId = null;
        $this->signature = '';
    }

    /**
     * Acknowledge the currently open policy.
     */
    public function acknowledge(): void
    {
        if (!$this->acknowledgingPolicyId) {
            return;
        }

        $policy = CompanyPolicy::findOrFail($this->acknowledgingPolicyId);

        // Prevent duplicate acknowledgments
        $existing = PolicyAcknowledgment::where('company_policy_id', $policy->id)
            ->where('employee_id', $this->employeeId)
            ->first();

        if ($existing && $existing->status === 'acknowledged') {
            $this->closeAcknowledgment();
            $this->loadPolicies();
            return;
        }

        // Create or update acknowledgment
        PolicyAcknowledgment::updateOrCreate(
            [
                'company_policy_id' => $policy->id,
                'employee_id' => $this->employeeId,
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

        $this->closeAcknowledgment();
        $this->loadPolicies();

        $this->dispatch('policyAcknowledged');
    }

    /**
     * Refresh policies after external acknowledgment.
     */
    public function refreshPolicies(): void
    {
        $this->loadPolicies();
    }

    public function render()
    {
        return view('policies::livewire.my-policies-hub');
    }
}
