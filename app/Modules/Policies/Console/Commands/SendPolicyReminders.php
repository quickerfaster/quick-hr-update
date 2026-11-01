<?php

namespace App\Modules\Policies\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Hr\Models\Employee;
use App\Modules\Policies\Models\CompanyPolicy;
use App\Modules\Policies\Models\PolicyAcknowledgment;
use App\Modules\Policies\Services\PolicyDistributionService;
use QuickerFaster\UILibrary\Services\Notifications\NotificationService;

class SendPolicyReminders extends Command
{
    protected $signature = 'policies:send-reminders
                            {--days-before=3 : Days before deadline to send reminder}
                            {--dry-run : Show what would be sent without actually sending}';

    protected $description = 'Send acknowledgment reminders for company policies approaching or past their deadline';

    public function handle(NotificationService $notificationService): int
    {
        $daysBefore = (int) $this->option('days-before');
        $dryRun = $this->option('dry-run');
        $now = now();
        $reminderDate = $now->copy()->addDays($daysBefore);

        $this->info("Scanning for policies requiring acknowledgment reminders...");
        $this->info("Reminder window: policies due by {$reminderDate->toDateString()}");

        $remindersSent = 0;
        $overdueSent = 0;

        // Get all published policies that require acknowledgment
        $policies = CompanyPolicy::published()
            ->where('requires_acknowledgment', true)
            ->whereNotNull('acknowledgment_required_by')
            ->get();

        if ($policies->isEmpty()) {
            $this->info('No policies with acknowledgment deadlines found.');
            return Command::SUCCESS;
        }

        $distributionService = app(PolicyDistributionService::class);

        foreach ($policies as $policy) {
            $deadline = $policy->acknowledgment_required_by;
            $isOverdue = $deadline->isPast();
            $isApproaching = !$isOverdue && $deadline->lte($reminderDate);

            if (!$isOverdue && !$isApproaching) {
                continue;
            }

            $this->line("");
            $this->line("Policy: {$policy->title} (v{$policy->version})");
            $this->line("  Deadline: {$deadline->toDateString()}" . ($isOverdue ? ' [OVERDUE]' : ' [APPROACHING]'));

            // Find employees who should have acknowledged but haven't
            $employees = Employee::where('company_id', $policy->company_id)->get();

            foreach ($employees as $employee) {
                // Check if policy applies to this employee
                if (!$distributionService->policyAppliesToEmployee($policy, $employee)) {
                    continue;
                }

                // Check if already acknowledged
                $acknowledged = PolicyAcknowledgment::where('company_policy_id', $policy->id)
                    ->where('employee_id', $employee->id)
                    ->where('status', 'acknowledged')
                    ->exists();

                if ($acknowledged) {
                    continue;
                }

                $templateType = $isOverdue
                    ? 'policy_acknowledgment_overdue'
                    : 'policy_acknowledgment_reminder';

                $variables = [
                    'employee_name' => trim($employee->first_name . ' ' . $employee->last_name),
                    'policy_title' => $policy->title,
                    'policy_version' => $policy->version,
                    'policy_category' => $policy->category,
                    'effective_date' => $policy->effective_date?->format('M d, Y') ?? 'N/A',
                    'deadline' => $deadline->toDateString(),
                    'policy_url' => url('/hr/my-policies'),
                    'url' => url('/hr/my-policies'),
                ];

                if ($dryRun) {
                    $this->line("  [DRY RUN] Would send '{$templateType}' to {$employee->email}");
                } else {
                    // Send notification (dispatches to both database and mail channels)
                    if ($employee->user_id) {
                        $notificationService->dispatchAsync(
                            notifiable: $employee->user,
                            type: $templateType,
                            data: $variables,
                        );
                    }
                }

                if ($isOverdue) {
                    $overdueSent++;
                } else {
                    $remindersSent++;
                }
            }
        }

        $this->line("");
        if ($dryRun) {
            $this->info("[DRY RUN] Would send {$remindersSent} reminder(s) and {$overdueSent} overdue notice(s).");
        } else {
            $this->info("Sent {$remindersSent} reminder(s) and {$overdueSent} overdue notice(s).");
        }

        return Command::SUCCESS;
    }
}
