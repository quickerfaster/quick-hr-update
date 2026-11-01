<?php

namespace App\Modules\Policies\Http\Livewire;

use QuickerFaster\UILibrary\Http\Livewire\DataTables\DataTable;
use QuickerFaster\UILibrary\Services\Notifications\NotificationService;
use App\Modules\Hr\Models\Employee;
use App\Modules\Policies\Models\CompanyPolicy;
use App\Modules\Policies\Services\PolicyDistributionService;

class PolicyDataTable extends DataTable
{
    protected $listeners = [
        'performDelete' => 'performDelete',
        'refreshDataTable' => '$refresh',
        'executeBulkAction' => 'executeBulkAction',
        'filtersUpdated' => 'updateFilters',
        'executeRowAction' => 'executeRowAction',
        'searchApplied' => 'applySearchPanel',
        'columnsUpdated' => 'handleColumnsUpdated',
        'publishPolicy' => 'publishPolicy',
        'archivePolicy' => 'archivePolicy',
    ];

    /**
     * Override to handle the 'event' key in moreActions.
     */
    public function executeRowAction($params): void
    {
        if (empty($params) || !is_array($params)) {
            return;
        }

        if (!isset($params['actionIndex']) || !isset($params['recordId'])) {
            return;
        }

        $action = $this->moreActions[$params['actionIndex']] ?? null;
        if (!$action) {
            return;
        }

        if (!empty($action['event'])) {
            $this->dispatch($action['event'], $params['recordId']);
            return;
        }

        parent::executeRowAction($params);
    }

    /**
     * Publish a draft policy.
     */
    public function publishPolicy($recordId): void
    {
        $policy = CompanyPolicy::find($recordId);

        if (!$policy) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'Policy not found.',
            ]);
            return;
        }

        if ($policy->status !== 'draft') {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'message' => 'Only draft policies can be published.',
            ]);
            return;
        }

        $policy->update(['status' => 'published']);

        // Send notifications to affected employees
        $this->notifyEmployees($policy, 'policy_published');

        $this->dispatch('showAlert', [
            'type' => 'success',
            'message' => "'{$policy->title}' has been published.",
            'autoClose' => true,
        ]);

        $this->dispatch('$refresh');
    }

    /**
     * Archive a published policy.
     */
    public function archivePolicy($recordId): void
    {
        $policy = CompanyPolicy::find($recordId);

        if (!$policy) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'Policy not found.',
            ]);
            return;
        }

        if ($policy->status !== 'published') {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'message' => 'Only published policies can be archived.',
            ]);
            return;
        }

        $policy->update(['status' => 'archived']);

        $this->dispatch('showAlert', [
            'type' => 'success',
            'message' => "'{$policy->title}' has been archived.",
            'autoClose' => true,
        ]);

        $this->dispatch('$refresh');
    }

    /**
     * Send policy notifications to affected employees.
     */
    protected function notifyEmployees(CompanyPolicy $policy, string $type): void
    {
        if (!$policy->requires_acknowledgment) {
            return;
        }

        $distributionService = app(PolicyDistributionService::class);
        $notificationService = app(NotificationService::class);

        $employees = Employee::where('company_id', $policy->company_id)->get();

        foreach ($employees as $employee) {
            if (!$distributionService->policyAppliesToEmployee($policy, $employee)) {
                continue;
            }

            if (!$employee->user_id) {
                continue;
            }

            $variables = [
                'employee_name' => trim($employee->first_name . ' ' . $employee->last_name),
                'policy_title' => $policy->title,
                'policy_version' => $policy->version,
                'policy_category' => $policy->category,
                'effective_date' => $policy->effective_date?->format('M d, Y') ?? 'N/A',
                'deadline' => $policy->acknowledgment_required_by?->toDateString() ?? 'N/A',
                'policy_url' => url('/hr/my-policies'),
                'url' => url('/hr/my-policies'),
            ];

            // Use dispatchAsync to avoid blocking the UI
            $notificationService->dispatchAsync(
                notifiable: $employee->user,
                type: $type,
                data: $variables,
            );
        }
    }
}
