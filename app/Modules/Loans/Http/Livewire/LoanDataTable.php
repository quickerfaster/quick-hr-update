<?php

namespace App\Modules\Loans\Http\Livewire;

use QuickerFaster\UILibrary\Http\Livewire\DataTables\DataTable;
use QuickerFaster\UILibrary\Services\Workflow\WorkflowEngine;
use App\Modules\Loans\Models\Loan;

class LoanDataTable extends DataTable
{
    /**
     * Guard sensitive actions. The EmployeeDataTableAuthorizationProvider
     * bypasses canPerformAction for any user linked to an employee record,
     * so we must enforce permission checks inside the handlers themselves.
     */
    protected function requirePermission(string $permission): bool
    {
        if (!auth()->user()?->can($permission)) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'You do not have permission to perform this action.',
            ]);
            return false;
        }
        return true;
    }
    protected $listeners = [
        'performDelete' => 'performDelete',
        'refreshDataTable' => '$refresh',
        'executeBulkAction' => 'executeBulkAction',
        'filtersUpdated' => 'updateFilters',
        'executeRowAction' => 'executeRowAction',
        'searchApplied' => 'applySearchPanel',
        'columnsUpdated' => 'handleColumnsUpdated',
        'submitForApproval' => 'submitForApproval',
        'approveLoan' => 'approveLoan',
        'rejectLoan' => 'rejectLoan',
        'activateLoan' => 'activateLoan',
    ];

    protected ?WorkflowEngine $workflowEngine = null;

    protected function getWorkflowEngine(): WorkflowEngine
    {
        if (!$this->workflowEngine) {
            $this->workflowEngine = app(WorkflowEngine::class);
        }
        return $this->workflowEngine;
    }

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
     * Submit a draft loan for approval via the WorkflowEngine.
     */
    public function submitForApproval($recordId): void
    {
        if (!$this->requirePermission('edit_loan')) { return; }
        $loan = Loan::find($recordId);

        if (!$loan) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'Loan not found.',
            ]);
            return;
        }

        if ($loan->status !== 'draft') {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'message' => 'Only draft loans can be submitted for approval.',
            ]);
            return;
        }

        try {
            $engine = $this->getWorkflowEngine();
            $engine->start($loan);

            $loan->update([
                'status' => 'pending_approval',
                'request_date' => now()->toDateString(),
            ]);

            $this->dispatch('showAlert', [
                'type' => 'success',
                'message' => "Loan '{$loan->loan_number}' has been submitted for approval.",
                'autoClose' => true,
            ]);
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'Failed to submit loan: ' . $e->getMessage(),
            ]);
        }

        $this->dispatch('$refresh');
    }

    /**
     * Approve a pending loan via the WorkflowEngine.
     */
    public function approveLoan($recordId): void
    {
        if (!$this->requirePermission('edit_loan')) { return; }
        $loan = Loan::with('activeWorkflow')->find($recordId);

        if (!$loan) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'Loan not found.',
            ]);
            return;
        }

        if ($loan->status !== 'pending_approval') {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'message' => 'Only pending loans can be approved.',
            ]);
            return;
        }

        try {
            $engine = $this->getWorkflowEngine();

            // If there's an active workflow, approve through it
            if ($loan->isUnderApproval()) {
                $workflow = $loan->activeWorkflow()->first();
                $engine->approve($workflow);

                // Sync loan status from workflow
                $loan->syncStatusFromWorkflow($workflow, [
                    'pending' => 'pending_approval',
                    'approved' => 'approved',
                ]);

                if ($workflow->status === 'approved') {
                    $loan->update([
                        'approval_date' => now()->toDateString(),
                        'approved_by' => auth()->id(),
                    ]);
                }
            } else {
                // Fallback: direct approval (no workflow)
                $loan->update([
                    'status' => 'approved',
                    'approval_date' => now()->toDateString(),
                    'approved_by' => auth()->id(),
                ]);
            }

            $this->dispatch('showAlert', [
                'type' => 'success',
                'message' => "Loan '{$loan->loan_number}' has been approved.",
                'autoClose' => true,
            ]);
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'Failed to approve loan: ' . $e->getMessage(),
            ]);
        }

        $this->dispatch('$refresh');
    }

    /**
     * Reject a pending loan via the WorkflowEngine.
     */
    public function rejectLoan($recordId): void
    {
        if (!$this->requirePermission('edit_loan')) { return; }
        $loan = Loan::with('activeWorkflow')->find($recordId);

        if (!$loan) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'Loan not found.',
            ]);
            return;
        }

        if ($loan->status !== 'pending_approval') {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'message' => 'Only pending loans can be rejected.',
            ]);
            return;
        }

        try {
            $engine = $this->getWorkflowEngine();

            if ($loan->isUnderApproval()) {
                $workflow = $loan->activeWorkflow()->first();
                $engine->reject($workflow);

                $loan->syncStatusFromWorkflow($workflow, [
                    'rejected' => 'rejected',
                ]);
            } else {
                $loan->update(['status' => 'rejected']);
            }

            $this->dispatch('showAlert', [
                'type' => 'success',
                'message' => "Loan '{$loan->loan_number}' has been rejected.",
                'autoClose' => true,
            ]);
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'Failed to reject loan: ' . $e->getMessage(),
            ]);
        }

        $this->dispatch('$refresh');
    }

    /**
     * Activate an approved loan (mark as active, disbursed) and generate
     * the repayment schedule.
     */
    public function activateLoan($recordId): void
    {
        if (!$this->requirePermission('edit_loan')) { return; }
        $loan = Loan::find($recordId);

        if (!$loan) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'Loan not found.',
            ]);
            return;
        }

        if ($loan->status !== 'approved') {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'message' => 'Only approved loans can be activated.',
            ]);
            return;
        }

        // Activate the loan
        $loan->update([
            'status' => 'active',
            'start_date' => $loan->start_date ?? now()->addMonth()->toDateString(),
        ]);

        // Generate the repayment schedule
        try {
            $repaymentService = app(\App\Modules\Loans\Services\LoanRepaymentService::class);
            $schedule = $repaymentService->generateSchedule($loan);
            $installmentCount = count($schedule);

            $this->dispatch('showAlert', [
                'type' => 'success',
                'message' => "Loan '{$loan->loan_number}' is now active with {$installmentCount} repayment installments generated.",
                'autoClose' => true,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to generate loan repayment schedule', [
                'loan_id' => $loan->id,
                'error' => $e->getMessage(),
            ]);

            $this->dispatch('showAlert', [
                'type' => 'warning',
                'message' => "Loan '{$loan->loan_number}' activated but repayment schedule generation failed: " . $e->getMessage(),
            ]);
        }

        $this->dispatch('$refresh');
    }
}
