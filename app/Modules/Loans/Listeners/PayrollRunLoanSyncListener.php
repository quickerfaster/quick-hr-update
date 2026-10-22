<?php

namespace App\Modules\Loans\Listeners;

use App\Modules\Loans\Services\LoanRepaymentPayrollIntegration;
use App\Modules\Payroll\Models\PayrollRun;
use Illuminate\Support\Facades\Log;

/**
 * Listens for payroll run events and syncs loan repayments.
 *
 * When a payroll run is processed, this listener creates deduction
 * adjustments for due loan repayments. When finalized, it marks
 * the linked repayments as paid.
 */
class PayrollRunLoanSyncListener
{
    /**
     * Handle the payroll run event.
     *
     * Reacts to DataTableRecordSaved for PayrollRun to detect
     * status transitions.
     */
    public function handle(\QuickerFaster\UILibrary\Events\DataTableRecordSaved $event): void
    {
        if ($event->model !== PayrollRun::class) {
            return;
        }

        $recordId = $event->component?->recordId
            ?? $event->newRecord['id']
            ?? $event->oldRecord['id']
            ?? null;

        if (!$recordId) {
            return;
        }

        $run = PayrollRun::withoutCompanyScope()->find($recordId);
        if (!$run) {
            return;
        }

        $integration = app(LoanRepaymentPayrollIntegration::class);

        // When payroll run calculation is completed, create loan repayment adjustments
        if ($event->action === \QuickerFaster\UILibrary\Events\DataTableRecordSaved::ACTION_UPDATED) {
            $newRecord = $event->newRecord ?? [];

            // Detect calculation_status transition to 'completed'
            if (isset($newRecord['calculation_status']) && $newRecord['calculation_status'] === 'completed') {
                Log::info('Payroll run calculation completed — syncing loan repayments', [
                    'payroll_run_id' => $run->id,
                ]);

                $count = $integration->createAdjustmentsForRun($run);
                Log::info("Created {$count} loan repayment adjustment(s) for payroll run #{$run->id}");
            }

            // Detect finalization (finalized_at being set)
            if (isset($newRecord['finalized_at']) && $newRecord['finalized_at'] !== null) {
                Log::info('Payroll run finalized — marking loan repayments as paid', [
                    'payroll_run_id' => $run->id,
                ]);

                $count = $integration->finalizeRepaymentsForRun($run);
                Log::info("Finalized {$count} loan repayment(s) for payroll run #{$run->id}");
            }
        }
    }
}
