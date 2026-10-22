<?php

namespace App\Modules\Loans\Http\Livewire;

use QuickerFaster\UILibrary\Http\Livewire\DataTables\DataTable;
use App\Modules\Loans\Models\LoanRepayment;
use App\Modules\Loans\Services\LoanRepaymentService;

/**
 * Custom DataTable for Loan Repayments with row actions.
 *
 * Handles 'Mark as Paid' and 'Waive' moreActions defined in
 * the loan_repayment.php data config.
 */
class LoanRepaymentDataTable extends DataTable
{
    protected $listeners = [
        'performDelete' => 'performDelete',
        'refreshDataTable' => '$refresh',
        'executeBulkAction' => 'executeBulkAction',
        'filtersUpdated' => 'updateFilters',
        'executeRowAction' => 'executeRowAction',
        'searchApplied' => 'applySearchPanel',
        'columnsUpdated' => 'handleColumnsUpdated',
        'markRepaymentPaid' => 'markRepaymentPaid',
        'waiveRepayment' => 'waiveRepayment',
    ];

    /**
     * Mark a repayment as paid via manual payment.
     */
    public function markRepaymentPaid($recordId): void
    {
        $repayment = LoanRepayment::find($recordId);

        if (!$repayment) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'Repayment not found.',
            ]);
            return;
        }

        if (!in_array($repayment->status, ['pending', 'overdue'])) {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'message' => 'Only pending or overdue repayments can be marked as paid.',
            ]);
            return;
        }

        $service = app(LoanRepaymentService::class);
        $service->markAsPaid($repayment, 'manual');

        $this->dispatch('showAlert', [
            'type' => 'success',
            'message' => 'Repayment marked as paid.',
            'autoClose' => true,
        ]);

        $this->dispatch('$refresh');
    }

    /**
     * Waive a repayment (no payment required).
     */
    public function waiveRepayment($recordId): void
    {
        $repayment = LoanRepayment::find($recordId);

        if (!$repayment) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'Repayment not found.',
            ]);
            return;
        }

        if ($repayment->status !== 'pending') {
            $this->dispatch('showAlert', [
                'type' => 'warning',
                'message' => 'Only pending repayments can be waived.',
            ]);
            return;
        }

        $service = app(LoanRepaymentService::class);
        $service->waive($repayment);

        $this->dispatch('showAlert', [
            'type' => 'success',
            'message' => 'Repayment waived.',
            'autoClose' => true,
        ]);

        $this->dispatch('$refresh');
    }
}
