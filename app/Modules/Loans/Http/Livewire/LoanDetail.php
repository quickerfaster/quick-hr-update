<?php

namespace App\Modules\Loans\Http\Livewire;

use Livewire\Component;
use App\Modules\Loans\Models\Loan;
use App\Modules\Loans\Models\LoanRepayment;
use QuickerFaster\UILibrary\Traits\HasCurrencySymbol;

class LoanDetail extends Component
{
    use HasCurrencySymbol;

    /** @var int */
    public $loanId;

    /** @var Loan|null */
    public ?Loan $loan = null;

    /** @var string */
    public string $activeTab = 'info';

    /** @var string Currency symbol resolved from the loan's company (matches Payroll pattern) */
    public string $currencySymbol = '$';

    /** @var \Illuminate\Database\Eloquent\Collection */
    public $repayments;

    protected $listeners = [
        'refreshDetail' => '$refresh',
    ];

    public function mount(int $recordId): void
    {
        $this->loanId = $recordId;
        $this->loadLoan();
    }

    /**
     * Reload the loan on every Livewire hydration cycle.
     * Public Eloquent model properties are not included in Livewire's
     * state snapshot, so they must be re-fetched from the database.
     */
    public function hydrate(): void
    {
        $this->loadLoan();
    }

    /**
     * Load the loan with all relationships.
     */
    public function loadLoan(): void
    {
        $this->loan = Loan::withoutCompanyScope()
            ->with([
                'employee',
                'loanType',
                'approvedBy',
                'company',
                'repayments' => function ($query) {
                    $query->orderBy('due_date', 'asc');
                },
                'workflow',
                'documents' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
            ])->find($this->loanId);

        if ($this->loan) {
            $this->repayments = $this->loan->repayments;

            // Resolve currency symbol via the model's currency_code accessor
            // (matches Payroll pattern, bypasses relationship scoping)
            $this->currencySymbol = $this->getCurrencySymbol($this->loan->currency_code);
        }
    }

    /**
     * Switch between tabs.
     */
    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClass(string $status): string
    {
        return match ($status) {
            'draft' => 'bg-secondary',
            'pending_approval' => 'bg-warning text-dark',
            'approved' => 'bg-info',
            'active' => 'bg-success',
            'completed' => 'bg-primary',
            'rejected' => 'bg-danger',
            'cancelled' => 'bg-dark',
            default => 'bg-secondary',
        };
    }

    /**
     * Get repayment status badge class.
     */
    public function getRepaymentStatusBadgeClass(string $status): string
    {
        return match ($status) {
            'paid' => 'bg-success',
            'pending' => 'bg-warning text-dark',
            'overdue' => 'bg-danger',
            'waived' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    public function render()
    {
        return view('loans::livewire.loan-detail');
    }
}
