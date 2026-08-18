<?php

namespace App\Modules\Hr\Http\Livewire\Payroll;

use Livewire\Component;
use App\Modules\Hr\Models\PayrollRun;
use App\Modules\Hr\Services\Payroll\PayrollCalculator;
use QuickerFaster\UILibrary\Concerns\ResolvesModels;
use Illuminate\Support\Facades\DB;
use QuickerFaster\UILibrary\Traits\HasCurrencySymbol;
use QuickerFaster\UILibrary\Services\Config\ConfigResolver;
use Barryvdh\DomPDF\Facade\Pdf;



class PayrollRunDetail extends Component
{
    use HasCurrencySymbol;
    use ResolvesModels;

    public int $recordId;
    public string $configKey;
    public PayrollRun $run;
    public array $returnParams = [];
    public array $tabs = [];
    public string $activeTab = 'overview';

    protected $listeners = [
        'refreshDetail' => '$refresh',
        'approveRun' => 'approve',
        'markPaid' => 'markPaid',
        'cancelRun' => 'cancel',
        'recalculate' => 'recalculate',
        'forceGenerateBankFile' => 'forceGenerateBankFile',
        'cancelBankFile' => 'cancelBankFile',
    ];

    public function mount(int $recordId, string $configKey, array $returnParams = []): void
    {
        $this->recordId = $recordId;
        $this->configKey = $configKey;
        $this->returnParams = $returnParams;

        $run = $this->resolveModel(PayrollRun::class, $recordId, [
            function ($query) {
                return $query->with(['paySchedule']);
            },
        ]);

        if (!$run) {
            $this->flashAndRedirect(
                'error',
                'Payroll run not found. It may have been deleted or is not accessible from your current view.',
                'payroll-runs.index'
            );
            return;
        }

        $this->run = $run;
        $this->tabs = $this->getTabs();
    }

    protected function getTabs(): array
    {
        return [
            'overview' => ['title' => 'Overview', 'icon' => 'fas fa-info-circle'],
            'payslips' => ['title' => 'Payslips', 'icon' => 'fas fa-receipt'],
            'adjustments' => ['title' => 'Adjustments', 'icon' => 'fas fa-edit'],
            'reconciliation' => ['title' => 'Reconciliation', 'icon' => 'fas fa-check-double'],
            'audit' => ['title' => 'Audit', 'icon' => 'fas fa-history'],
        ];
    }

    // Approval actions (unchanged)
    public function confirmApprove(): void
    {
        $this->dispatch('showAlert', [
            'type' => 'confirm',
            'title' => 'Approve Payroll Run?',
            'message' => 'This will lock all data and mark the run as approved. Are you sure?',
            'confirmEvent' => 'approveRun',
            'confirmParams' => [],
        ]);
    }

    public function approve(): void
    {
        if (!in_array($this->run->status, ['draft', 'verification_complete', 'adjustments_pending', 'ready_for_review'])) {
            $this->dispatch('showAlert', ['type' => 'error', 'message' => 'Cannot approve this payroll run.']);
            return;
        }

        DB::transaction(function () {
            $this->run->update([
                'status' => 'approved',
                'approved_by' => auth()->user()->name ?? auth()->id(),
                'approved_at' => now(),
            ]);
        });

        $this->dispatch('showAlert', ['type' => 'success', 'message' => 'Payroll run approved.']);
        $this->run->refresh();
    }

    public function confirmMarkPaid(): void
    {
        $this->dispatch('showAlert', [
            'type' => 'confirm',
            'title' => 'Mark as Paid?',
            'message' => 'Confirm that payment has been processed externally?',
            'confirmEvent' => 'markPaid',
            'confirmParams' => [],
        ]);
    }

    public function markPaid(): void
    {
        if ($this->run->status !== 'approved') {
            $this->dispatch('showAlert', ['type' => 'error', 'message' => 'Only approved runs can be marked as paid.']);
            return;
        }

        $this->run->update([
            'status' => 'paid',
            'processed_at' => now(),
            'processed_by' => auth()->user()->name ?? auth()->id(),
        ]);

        $this->dispatch('showAlert', ['type' => 'success', 'message' => 'Payroll run marked as paid.']);
        $this->run->refresh();
    }

    public function confirmCancel(): void
    {
        $this->dispatch('showAlert', [
            'type' => 'confirm',
            'title' => 'Cancel Payroll Run?',
            'message' => 'This action cannot be undone. Are you sure?',
            'confirmEvent' => 'cancelRun',
            'confirmParams' => [],
        ]);
    }

    public function cancel(): void
    {
        if (!in_array($this->run->status, ['draft', 'verification_complete', 'adjustments_pending', 'ready_for_review', 'approved'])) {
            $this->dispatch('showAlert', ['type' => 'error', 'message' => 'This payroll run cannot be cancelled.']);
            return;
        }

        $this->run->update(['status' => 'cancelled']);
        $this->dispatch('showAlert', ['type' => 'success', 'message' => 'Payroll run cancelled.']);
        $this->run->refresh();
    }

    public function confirmRecalculate(): void
    {
        $this->dispatch('showAlert', [
            'type' => 'confirm',
            'title' => 'Recalculate Payroll?',
            'message' => 'This will overwrite existing payslip calculations. Continue?',
            'confirmEvent' => 'recalculate',
            'confirmParams' => [],
        ]);
    }

    public function recalculate(): void
    {
        if ($this->run->status !== 'draft') {
            $this->dispatch('showAlert', ['type' => 'error', 'message' => 'Only draft runs can be recalculated.']);
            return;
        }
        app(PayrollCalculator::class)->calculate($this->run);
        $this->run->refresh();
        $this->dispatch('showAlert', ['type' => 'success', 'message' => 'Recalculation completed.']);
    }

    public function exportPayslips(): void
    {

        $configResolver = app(ConfigResolver::class, ['configKey' => 'hr.payroll_payslip']);
        $fieldDefinitions = $configResolver->getFieldDefinitions();

        $excludedColumns = [
            "payslip_number", // exclude from middle & add it to custom column first item
            "payroll_run_id",
            "employee_id",
            "paid_at",
            "payment_reference",
            "bank_account_snapshot",
            "notes",
            "created_by",
            "updated_by",
        ];


        $customColumns = [
            'payslip_number',
            'employee.employee_number',
            'employee.first_name',
            'employee.last_name',
        ];

        $columns = array_diff(array_keys($fieldDefinitions), $excludedColumns);
        $columns = array_merge($customColumns, $columns);


        // Build filters in the exact format the DataTable uses
        $filters = [
            [
                'field' => 'payroll_run_id',
                'type' => 'number',          // or 'select' – both work
                'operator' => 'equals',
                'value' => $this->run->id,
                'multi' => false,
                // 'displayValue' => $this->run->id, // optional
                // 'label'        => 'Payroll Run',  // optional
            ]
        ];

        $options = [
            'orientation' => 'landscape',
            'paper' => 'a4',
        ];

        $params = [
            'configKey' => 'hr.payroll_payslip',
            'format' => 'xls',
            'columns' => implode(',', $columns),
            'filters' => json_encode($filters),
            'options' => json_encode($options),
        ];

        $this->dispatch('openExportModal', [
            'configKey' => 'hr.payroll_payslip',
            'params' => $params,
        ]);
    }



public function generateBankFile(): void
{
    // Bank file generation requires a pay schedule (single‑company)
    if (!$this->run->paySchedule) {
        $this->dispatch('showAlert', [
            'type' => 'error',
            'title' => 'Bank File Not Available',
            'message' => 'Bank file generation is only supported for single‑company payroll runs. For multi‑company runs, please generate bank files per company individually.',
        ]);
        return;
    }

    // ... rest of the method (unchanged)
}

public function forceGenerateBankFile(): void
{
    $this->dispatch('open-url-new-tab', route('payroll.bank-file', $this->run->id));
}

public function cancelBankFile(): void
{
    // Do nothing – user cancelled
}



public function exportSummaryPdf()
{
    $this->dispatch('open-url-new-tab', route('payroll-run.summary-pdf', $this->run->id));
}


public function queueSummaryPdf()
{
    // Guard: ensure the payroll run exists and is in a valid state
    // for PDF generation (any state except draft is acceptable —
    // even processing runs can have partial payslip data).

    if (!$this->run || !$this->run->id) {
        $this->dispatch('showAlert', [
            'type' => 'error',
            'title' => 'Error',
            'message' => 'Payroll run not found.',
        ]);
        return;
    }

    // Determine currency code and company name safely
    if ($this->run->paySchedule) {
        $currencyCode = $this->run->paySchedule->currency_code ?? 'USD';
        $companyName = optional($this->run->paySchedule->company)->name ?? config('app.name', 'Quick HR');
    } else {

        // Multi-company or run without pay schedule
        $currencyCode = $this->run->base_currency ?? 'USD';
        if ($this->run->is_multi_company) {
            $companyName = 'All Companies';
        } else {
            $companyName = config('app.name', 'Quick HR');
        }
    }

    $currencySymbol = $this->getCurrencySymbol($currencyCode);

    // Create an export record
    $export = \QuickerFaster\UILibrary\Models\Export::create([
        'user_id' => auth()->id(),
        'config_key' => 'hr.payroll_payslip', // dummy
        'filters' => ['payroll_run_id' => $this->run->id],
        'columns' => [],
        'format' => 'pdf',
        'options' => [
            'custom_view' => 'hr::livewire.payroll.exports.payroll_run_summary_pdf',
            'run_id' => $this->run->id,
            'currency_symbol' => $currencySymbol,
            'company_name' => $companyName,
        ],
        'status' => 'pending',
    ]);

    if (!$export || !$export->id) {
        \Log::error('PayrollRunDetail: Failed to create export record for run #' . $this->run->id);
        $this->dispatch('showAlert', [
            'type' => 'error',
            'title' => 'Export Failed',
            'message' => 'Could not create export record. Please try again.',
        ]);
        return;
    }

    \App\Modules\Hr\Jobs\Payrolls\GeneratePayrollRunSummaryPdf::dispatch($export->id);

    \Log::info('PayrollRunDetail: Dispatched GeneratePayrollRunSummaryPdf', [
        'export_id' => $export->id,
        'run_id' => $this->run->id,
        'is_multi_company' => $this->run->is_multi_company ?? false,
    ]);

    $this->dispatch('openExportModal', [
        'configKey' => 'hr.payroll_payslip',
        'params' => [
            'export_id' => $export->id,
        ],
    ]);
}





public function markAsReconciled(): void
{
    if ($this->run->status !== 'paid') {
        $this->dispatch('showAlert', [
            'type' => 'warning',
            'message' => 'Only paid runs can be marked as reconciled.',
        ]);
        return;
    }

    $this->run->update([
        'reconciliation_status' => 'reconciled',
        'reconciled_at' => now(),
    ]);

    $this->run->refresh();
    $this->dispatch('showAlert', ['type' => 'success', 'message' => 'Payroll run marked as reconciled.']);
}




    public function render()
    {
        $canApprove = in_array($this->run->status, ['draft', 'verification_complete', 'adjustments_pending', 'ready_for_review']);
        $canMarkPaid = $this->run->status === 'approved';
        $canCancel = in_array($this->run->status, ['draft', 'verification_complete', 'adjustments_pending', 'ready_for_review', 'approved']);
        $canRecalculate = $this->run->status === 'draft';

        return view('hr::livewire.payroll.payroll-run-detail', [
            'canApprove' => $canApprove,
            'canMarkPaid' => $canMarkPaid,
            'canCancel' => $canCancel,
            'canRecalculate' => $canRecalculate,
        ]);
    }
}
