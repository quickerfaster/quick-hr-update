<?php

namespace App\Modules\Policies\Listeners;

use Illuminate\Support\Facades\Log;
use QuickerFaster\UILibrary\Events\DataTableRecordSaved;
use QuickerFaster\UILibrary\Services\Notifications\NotificationService;
use App\Modules\Hr\Models\Employee;
use App\Modules\Policies\Models\CompanyPolicy;
use App\Modules\Policies\Services\PolicyDistributionService;

class PolicyEventListener
{
    /**
     * Handle DataTable record save events for CompanyPolicy.
     */
    public function handle(DataTableRecordSaved $event): void
    {
        if ($event->model !== CompanyPolicy::class) {
            return;
        }

        // Get the record ID from the component (most reliable source)
        $recordId = $event->component?->recordId
            ?? $event->newRecord['id']
            ?? $event->oldRecord['id']
            ?? null;

        if (!$recordId) {
            return;
        }

        $policy = CompanyPolicy::find($recordId);
        if (!$policy) {
            return;
        }

        if ($event->action === DataTableRecordSaved::ACTION_CREATED) {
            $this->handleCreated($policy);
        } elseif ($event->action === DataTableRecordSaved::ACTION_UPDATED) {
            $this->handleUpdated($policy, $event);
        }
    }

    /**
     * New policy created — notify if published with acknowledgment required.
     */
    protected function handleCreated(CompanyPolicy $policy): void
    {
        if ($policy->status !== 'published' || !$policy->requires_acknowledgment) {
            return;
        }

        $this->notifyEmployees($policy, 'policy_published');
    }

    /**
     * Existing policy updated — notify on publish or content change.
     *
     * Note: $event->oldRecord and $event->newRecord only contain CHANGED
     * fields (via array_intersect_key). We query the model directly for
     * current state and compare old values from the event where available.
     */
    protected function handleUpdated(CompanyPolicy $policy, DataTableRecordSaved $event): void
    {
        $oldRecord = $event->oldRecord ?? [];
        $newRecord = $event->newRecord ?? [];

        Log::info('PolicyEventListener: Update detected', [
            'policy_id' => $policy->id,
            'policy_status' => $policy->status,
            'requires_ack' => $policy->requires_acknowledgment,
            'old_keys' => array_keys($oldRecord),
            'new_keys' => array_keys($newRecord),
        ]);

        // Only care about published policies that require acknowledgment
        if ($policy->status !== 'published' || !$policy->requires_acknowledgment) {
            return;
        }

        // Check if content changed (content field is in the changed keys)
        $contentChanged = array_key_exists('content', $newRecord);

        // Check if status was just changed to published
        $wasJustPublished = array_key_exists('status', $newRecord)
            && ($oldRecord['status'] ?? null) !== 'published';

        Log::info('PolicyEventListener: Update conditions', [
            'contentChanged' => $contentChanged,
            'wasJustPublished' => $wasJustPublished,
        ]);

        if ($wasJustPublished) {
            $this->notifyEmployees($policy, 'policy_published');
        } elseif ($contentChanged) {
            $this->notifyEmployees($policy, 'policy_updated');
        }
    }

    /**
     * Send notification to all employees this policy applies to.
     */
    protected function notifyEmployees(CompanyPolicy $policy, string $type): void
    {
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

            $notificationService->dispatchAsync(
                notifiable: $employee->user,
                type: $type,
                data: $variables,
            );
        }
    }
}
