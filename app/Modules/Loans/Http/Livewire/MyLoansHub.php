<?php

namespace App\Modules\Loans\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Modules\Hr\Models\Employee;
use App\Modules\Loans\Models\LoanType;

class MyLoansHub extends Component
{
    /** @var string */
    public string $activeTab = 'overview';

    /** @var int|null */
    public ?int $employeeId = null;

    /** @var string|null */
    public ?string $employeeNumber = null;

    /** @var string|null */
    public ?string $employeeName = null;

    /** @var array Available loan types for the request form */
    public array $availableLoanTypes = [];

    /** @var int|null Loan ID for resuming a draft */
    public ?int $resumeLoanId = null;

    protected $listeners = [
        'loanRequestSubmitted' => 'handleLoanSubmitted',
        'loanDraftSaved' => 'handleLoanDraftSaved',
        'loanRequestCancelled' => 'handleLoanCancelled',
    ];

    public function mount(): void
    {
        $employee = Employee::where('user_id', Auth::id())->first();

        if (!$employee) {
            abort(403, 'No employee record found for your account.');
        }

        $this->employeeId = $employee->id;
        $this->employeeNumber = $employee->employee_number;
        $this->employeeName = $employee->full_name ?? $employee->first_name . ' ' . $employee->last_name;

        $this->loadAvailableLoanTypes();

        // Read tab from query string to persist active tab on refresh
        if (request()->has('tab') && in_array(request()->query('tab'), ['overview', 'my-loans', 'request'])) {
            $this->activeTab = request()->query('tab');
        }

        // Read loanId (or resumeRecordId from DataTable resume action) for resuming a draft
        $this->resumeLoanId = request()->has('resumeRecordId')
            ? (int) request()->query('resumeRecordId')
            : (request()->has('loanId') ? (int) request()->query('loanId') : null);
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->dispatch('update-url', tab: $tab);
    }

    /**
     * Load available loan types for the request form.
     */
    public function loadAvailableLoanTypes(): void
    {
        $employee = Employee::find($this->employeeId);
        if (!$employee) {
            $this->availableLoanTypes = [];
            return;
        }

        $this->availableLoanTypes = LoanType::active()
            ->where('company_id', $employee->company_id)
            ->get()
            ->toArray();
    }

    /**
     * Handle loan submission — switch back to My Loans tab.
     */
    public function handleLoanSubmitted(): void
    {
        $this->activeTab = 'my-loans';
        $this->dispatch('update-url', tab: 'my-loans');
        session()->flash('loan_message', 'Your loan request has been submitted successfully.');
    }

    /**
     * Handle draft save — return to My Loans tab with draft message.
     */
    public function handleLoanDraftSaved(): void
    {
        $this->activeTab = 'my-loans';
        $this->dispatch('update-url', tab: 'my-loans');
        session()->flash('loan_message', 'Your loan request has been saved as a draft.');
    }

    /**
     * Handle loan request cancellation/discard — return to My Loans tab.
     */
    public function handleLoanCancelled(): void
    {
        $this->activeTab = 'my-loans';
        $this->dispatch('update-url', tab: 'my-loans');
    }

    public function render()
    {
        return view('loans::livewire.my-loans-hub');
    }
}
