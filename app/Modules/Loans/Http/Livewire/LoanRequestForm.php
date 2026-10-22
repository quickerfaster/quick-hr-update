<?php

namespace App\Modules\Loans\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Modules\Hr\Models\Employee;
use App\Modules\Loans\Models\Loan;
use App\Modules\Loans\Models\LoanType;
use App\Modules\Loans\Services\LoanCalculator;
use QuickerFaster\UILibrary\Models\Document;
use QuickerFaster\UILibrary\Traits\HasCurrencySymbol;

class LoanRequestForm extends Component
{
    use HasCurrencySymbol;
    use WithFileUploads;

    /** @var int */
    public $employeeId;

    /** @var array */
    public array $availableLoanTypes = [];

    /** @var int Current step (1-5) */
    public int $step = 1;

    /** @var int Total steps */
    public int $totalSteps = 5;

    // Step 1: Loan Type
    public ?int $loanTypeId = null;
    public ?array $selectedLoanType = null;

    // Step 2: Amount & Reason
    public ?float $amount = null;
    public string $reason = '';

    // Step 3: Repayment Terms
    public int $repaymentMonths = 1;
    public ?float $monthlyInstallment = null;
    public ?float $totalRepayable = null;
    public ?float $totalInterest = null;

    // Step 4: Documents (onboarding pattern)
    /** @var \Illuminate\Http\UploadedFile|null */
    public $newFile = null;

    /** @var \Illuminate\Database\Eloquent\Collection */
    public $uploadedDocuments;

    /** @var bool True while a file upload is in progress */
    public bool $uploading = false;

    /** @var int Maximum file size in KB (10240 = 10MB) */
    public int $maxFileSize = 10240;

    /** @var int Maximum files allowed */
    public int $maxFiles = 10;

    // Step 5: Review
    public bool $termsAgreed = false;

    /** @var string */
    public string $errorMessage = '';

    /** @var string */
    public string $successMessage = '';

    /** @var int|null Temporary loan ID for document attachment before submission */
    public ?int $draftLoanId = null;

    /** @var string Currency symbol resolved from the employee's company (matches Payroll pattern) */
    public string $currencySymbol = '$';

    protected $listeners = [
        "cancelKeep" => "cancelKeep",
        "cancelDelete" => "cancelDelete",
    ];

    protected ?LoanCalculator $calculator = null;

    protected function getWizardId(): string
    {
        return 'loan_wizard_' . $this->employeeId;
    }

    protected function persistState(): void
    {
        session()->put($this->getWizardId(), [
            'step' => $this->step,
            'loanTypeId' => $this->loanTypeId,
            'amount' => $this->amount,
            'reason' => $this->reason,
            'repaymentMonths' => $this->repaymentMonths,
            'draftLoanId' => $this->draftLoanId,
            'documentIds' => $this->uploadedDocuments ? $this->uploadedDocuments->pluck('id')->toArray() : [],
        ]);
    }

    protected function rules(): array
    {
        return [
            'newFile' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx',
                'max:' . $this->maxFileSize,
            ],
        ];
    }

    protected $messages = [
        'newFile.mimes' => 'Only PDF, JPG, PNG, DOC, and DOCX files are allowed.',
        'newFile.max'   => 'File must not exceed :max KB.',
    ];

    protected function getCalculator(): LoanCalculator
    {
        if (!$this->calculator) {
            $this->calculator = app(LoanCalculator::class);
        }
        return $this->calculator;
    }

    public function mount(int $employeeId, array $availableLoanTypes = [], ?int $loanId = null): void
    {
        $this->employeeId = $employeeId;
        $this->availableLoanTypes = $availableLoanTypes;
        $this->uploadedDocuments = collect();

        // Resolve currency symbol from the employee's company.
        // Use a direct query to avoid relationship scoping issues
        // (matches Payroll pattern where currency comes from paySchedule).
        $currencyCode = \App\Modules\Hr\Models\Company::where('id', function ($query) use ($employeeId) {
            $query->select('company_id')
                ->from('employees')
                ->where('id', $employeeId)
                ->limit(1);
        })->value('currency_code') ?? 'USD';
        $this->currencySymbol = $this->getCurrencySymbol($currencyCode);

        // Restore wizard state from session (survives page refresh)
        $wizardId = $this->getWizardId();
        if (session()->has($wizardId)) {
            $data = session()->get($wizardId);
            $this->step = $data['step'] ?? 1;
            $this->loanTypeId = $data['loanTypeId'] ?? null;
            $this->amount = $data['amount'] ?? null;
            $this->reason = $data['reason'] ?? '';
            $this->repaymentMonths = $data['repaymentMonths'] ?? 1;
            $this->draftLoanId = $data['draftLoanId'] ?? null;
            if ($this->loanTypeId) {
                $lt = LoanType::find($this->loanTypeId);
                $this->selectedLoanType = $lt ? $lt->toArray() : null;
                if ($this->amount && $this->repaymentMonths) { $this->recalculateEMI(); }
            }
            // Reload documents from stored IDs OR from draft loan
            $docIds = $data['documentIds'] ?? [];
            if (!empty($docIds)) {
                $this->uploadedDocuments = Document::whereIn('id', $docIds)
                    ->orderBy('created_at', 'desc')
                    ->get();
            } elseif ($this->draftLoanId) {
                $this->uploadedDocuments = Document::where('documentable_type', Loan::class)
                    ->where('documentable_id', $this->draftLoanId)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        }

        // Resume existing draft — pre-fill all fields and jump to saved step
        if ($loanId && !session()->has($wizardId)) {
            $loan = Loan::with('repayments')->find($loanId);
            if ($loan && $loan->status === 'draft' && $loan->employee_id === $employeeId) {
                $this->loanTypeId = $loan->loan_type_id;
                $this->selectedLoanType = $loan->loanType?->toArray();
                $this->amount = (float) $loan->amount;
                $this->reason = $loan->reason ?? '';
                $this->repaymentMonths = (int) $loan->repayment_months;
                $this->monthlyInstallment = (float) $loan->monthly_installment;
                $this->totalRepayable = (float) $loan->total_repayable;
                $this->draftLoanId = $loan->id;

                // Resume at the step where the user left off
                $savedStep = (int) ($loan->current_step ?? 1);
                $this->step = max(1, min($savedStep, $this->totalSteps));
                if ($this->amount && $this->repaymentMonths) { $this->recalculateEMI(); }

                // Load existing documents
                $this->uploadedDocuments = Document::where('documentable_type', Loan::class)
                    ->where('documentable_id', $loan->id)
                    ->orderBy('created_at', 'desc')
                    ->get();

                // Persist initial state
                $this->persistState();
            }
        }

        // Create an early draft if none exists, so document uploads have a real model ID
        if (!$this->draftLoanId) {
            $employee = Employee::find($this->employeeId);
            if ($employee) {
                $loan = Loan::create([
                    'company_id' => $employee->company_id,
                    'employee_id' => $this->employeeId,
                    'loan_number' => $this->generateLoanNumber($employee->company_id),
                    'amount' => 0,
                    'interest_rate' => 0,
                    'repayment_months' => 1,
                    'monthly_installment' => 0,
                    'total_repayable' => 0,
                    'paid_amount' => 0,
                    'balance' => 0,
                    'status' => 'draft',
                    'request_date' => now()->toDateString(),
                ]);
                $this->draftLoanId = $loan->id;
            }
        }

        // Persist initial state so page refresh keeps the current step
        if (!session()->has($this->getWizardId())) {
            $this->persistState();
        }
    }

    /**
     * Persist wizard state whenever any property changes.
     * Matches the Payroll wizard session persistence pattern.
     */
    public function updated($name): void
    {
        // Skip persisting on file uploads and internal properties
        if (!in_array($name, ['newFile', 'uploading', 'errorMessage', 'successMessage', 'step'])) {
            $this->persistState();
        }
    }

    /**
     * Livewire hook: called automatically when a file is uploaded to
     * $this->newFile via component.upload('newFile', file, ...).
     * Matches the onboarding Step4Documents pattern exactly.
     */
    public function updatedNewFile(): void
    {
        $this->uploadDocument();
    }

    /**
     * Upload a single file and create a Document record.
     * If no loan exists yet (pre-submission), store with a temporary
     * polymorphic association that gets updated on submission.
     */
    public function uploadDocument(): void
    {
        $this->uploading = true;

        $this->validate();

        if (!$this->newFile) {
            $this->uploading = false;
            return;
        }

        // Enforce file count limit
        if ($this->uploadedDocuments->count() >= $this->maxFiles) {
            $this->uploading = false;
            $this->errorMessage = 'You can upload a maximum of ' . $this->maxFiles . ' files.';
            return;
        }

        // Detect duplicate
        $existing = $this->uploadedDocuments->first(function ($doc) {
            return $doc->file_name === $this->newFile->getClientOriginalName()
                && $doc->size === $this->newFile->getSize();
        });

        if ($existing) {
            $this->newFile = null;
            $this->uploading = false;
            return;
        }

        try {
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $this->newFile->getClientOriginalName());
            $path = 'loans/temp/' . $filename;

            $storedPath = $this->newFile->storeAs('loans/temp', $filename, 'public');

            $document = Document::create([
                'documentable_type' => Loan::class,
                'documentable_id'   => $this->draftLoanId ?? 0,
                'name'              => $this->newFile->getClientOriginalName(),
                'file_path'         => $storedPath,
                'file_name'         => $this->newFile->getClientOriginalName(),
                'mime_type'         => $this->newFile->getMimeType(),
                'size'              => $this->newFile->getSize(),
                'document_type'     => 'supporting_document',
                'disk'              => 'public',
            ]);

            // Add to local collection for display
            $this->uploadedDocuments->push($document);

            // Persist document IDs to session so they survive page refresh.
            // Matches LeaveDocumentUpload pattern where documents are always
            // loaded fresh from DB on mount/hydrate.
            $this->persistState();

            $this->newFile = null;
            $this->uploading = false;
        } catch (\Exception $e) {
            $this->uploading = false;
            $this->errorMessage = 'Upload failed: ' . $e->getMessage();
            \Illuminate\Support\Facades\Log::error('Loan document upload failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove an uploaded document.
     */
    public function remove(int $documentId): void
    {
        $document = Document::find($documentId);
        if ($document) {
            // Delete file from storage
            \Illuminate\Support\Facades\Storage::disk($document->disk ?? 'public')->delete($document->file_path);
            $document->delete();

            // Remove from local collection
            $this->uploadedDocuments = $this->uploadedDocuments->filter(fn($doc) => $doc->id !== $documentId);

            // Persist updated document IDs to session so removal survives page refresh
            $this->persistState();
        }
    }

    /**
     * Re-associate uploaded documents with the final loan after submission.
     *
     * Documents are created with the draft loan ID during the wizard. When the
     * user submits, a NEW loan record is created (the draft is discarded), so
     * we must re-point all uploaded documents to the new loan ID.
     */
    protected function attachDocumentsToLoan(Loan $loan): void
    {
        $docIds = $this->uploadedDocuments->pluck('id')->toArray();

        if (empty($docIds)) {
            return;
        }

        Document::whereIn('id', $docIds)
            ->update(['documentable_id' => $loan->id]);
    }

    /**
     * When loan type is selected, load its details.
     */
    public function updatedLoanTypeId(int $value): void
    {
        if ($value) {
            $loanType = LoanType::find($value);
            $this->selectedLoanType = $loanType ? $loanType->toArray() : null;
            $this->monthlyInstallment = null;
            $this->totalRepayable = null;
            $this->totalInterest = null;
        } else {
            $this->selectedLoanType = null;
        }
    }

    public function updatedAmount(): void
    {
        $this->recalculateEMI();
    }

    public function updatedRepaymentMonths(): void
    {
        $this->recalculateEMI();
    }

    protected function recalculateEMI(): void
    {
        if (!$this->amount || $this->amount <= 0 || !$this->repaymentMonths) {
            $this->monthlyInstallment = null;
            $this->totalRepayable = null;
            $this->totalInterest = null;
            return;
        }

        $rate = $this->selectedLoanType['default_interest_rate'] ?? 0;
        $calc = $this->getCalculator();

        $this->monthlyInstallment = $calc->calculateEMI($this->amount, $rate, $this->repaymentMonths);
        $this->totalRepayable = $calc->calculateTotalRepayable($this->monthlyInstallment, $this->repaymentMonths);
        $this->totalInterest = $calc->calculateTotalInterest($this->totalRepayable, $this->amount);
    }

    public function nextStep(): void
    {
        $this->errorMessage = '';
        if (!$this->validateCurrentStep()) { return; }
        if ($this->step < $this->totalSteps) { $this->step++; $this->persistState(); }
    }

    public function previousStep(): void
    {
        $this->errorMessage = '';
        if ($this->step > 1) { $this->step--; $this->persistState(); }
    }

    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step <= $this->step) { $this->step = $step; $this->persistState(); }
    }

    protected function validateCurrentStep(): bool
    {
        return match ($this->step) {
            1 => $this->validateStep1(),
            2 => $this->validateStep2(),
            3 => $this->validateStep3(),
            4 => true, // Documents are optional
            5 => $this->validateStep5(),
            default => true,
        };
    }

    protected function validateStep1(): bool
    {
        if (!$this->loanTypeId) { $this->errorMessage = 'Please select a loan type.'; return false; }
        $loanType = LoanType::find($this->loanTypeId);
        if (!$loanType || !$loanType->is_active) { $this->errorMessage = 'The selected loan type is no longer available.'; return false; }
        $this->selectedLoanType = $loanType->toArray();
        $eligibilityError = $this->checkEligibility($loanType);
        if ($eligibilityError) { $this->errorMessage = $eligibilityError; return false; }
        return true;
    }

    protected function checkEligibility(LoanType $loanType): ?string
    {
        $employee = Employee::with('employeePosition')->find($this->employeeId);
        if (!$employee) { return 'Employee record not found.'; }
        $rules = $loanType->eligibility_rules ?? [];
        if (!empty($rules['min_tenure_months']) && $employee->hire_date) {
            $tenureMonths = $employee->hire_date->diffInMonths(now());
            if ($tenureMonths < (int) $rules['min_tenure_months']) {
                return "You must have been employed for at least {$rules['min_tenure_months']} months. You have {$tenureMonths} months.";
            }
        }
        if (!empty($rules['max_active_loans'])) {
            $activeLoanCount = Loan::forEmployee($this->employeeId)->whereIn('status', ['active', 'pending_approval', 'approved'])->count();
            if ($activeLoanCount >= (int) $rules['max_active_loans']) {
                return "You already have {$activeLoanCount} active/pending loan(s). Maximum: {$rules['max_active_loans']}.";
            }
        }
        if (!empty($rules['max_salary_percent'])) {
            $monthlySalary = $employee->employeePosition?->salary ?? 0;
            if ($monthlySalary > 0) {
                session()->put('loan_eligibility_max_amount', $monthlySalary * ((float) $rules['max_salary_percent'] / 100));
                session()->put('loan_eligibility_max_percent', (float) $rules['max_salary_percent']);
            }
        }
        return null;
    }

    protected function validateStep2(): bool
    {
        if (!$this->amount || $this->amount <= 0) { $this->errorMessage = 'Please enter a valid loan amount.'; return false; }
        if (empty(trim($this->reason))) { $this->errorMessage = 'Please provide a reason.'; return false; }
        if ($this->selectedLoanType) {
            $validation = $this->getCalculator()->validateAmount($this->amount, (float)($this->selectedLoanType['min_amount'] ?? 0), (float)($this->selectedLoanType['max_amount'] ?? 0));
            if (!$validation['valid']) { $this->errorMessage = $validation['message']; return false; }
        }
        $maxAmount = session()->get('loan_eligibility_max_amount');
        $maxPercent = session()->get('loan_eligibility_max_percent');
        if ($maxAmount && $this->amount > $maxAmount) {
            $this->errorMessage = "Amount cannot exceed {$maxPercent}% of your monthly salary (max: " . number_format($maxAmount, 2) . ").";
            return false;
        }
        return true;
    }

    protected function validateStep3(): bool
    {
        if ($this->repaymentMonths < 1) { $this->errorMessage = 'Repayment period must be at least 1 month.'; return false; }
        $maxMonths = $this->selectedLoanType['max_repayment_months'] ?? 12;
        if ($this->repaymentMonths > $maxMonths) { $this->errorMessage = "Maximum repayment period is {$maxMonths} months."; return false; }
        return true;
    }

    protected function validateStep5(): bool
    {
        if (!$this->termsAgreed) { $this->errorMessage = 'You must agree to the loan terms.'; return false; }
        return true;
    }

    public function submit(): void
    {
        $this->errorMessage = '';
        if (!$this->validateCurrentStep()) { return; }

        $employee = Employee::find($this->employeeId);
        if (!$employee) { $this->errorMessage = 'Employee record not found.'; return; }

        $loanType = LoanType::find($this->loanTypeId);
        $rate = $loanType?->default_interest_rate ?? 0;
        $loanNumber = $this->generateLoanNumber($employee->company_id);
        $calc = $this->getCalculator();
        $emi = $calc->calculateEMI($this->amount, $rate, $this->repaymentMonths);
        $totalRepayable = $calc->calculateTotalRepayable($emi, $this->repaymentMonths);
        $requiresApproval = $loanType?->requires_approval ?? true;
        $initialStatus = $requiresApproval ? 'pending_approval' : 'approved';

        $loan = Loan::create([
            'company_id' => $employee->company_id,
            'employee_id' => $this->employeeId,
            'loan_type_id' => $this->loanTypeId,
            'loan_number' => $loanNumber,
            'amount' => $this->amount,
            'interest_rate' => $rate,
            'repayment_months' => $this->repaymentMonths,
            'monthly_installment' => $emi,
            'total_repayable' => $totalRepayable,
            'paid_amount' => 0,
            'balance' => $totalRepayable,
            'status' => $initialStatus,
            'reason' => $this->reason,
            'request_date' => now()->toDateString(),
        ]);

        // Re-associate uploaded documents with the actual loan
        $this->attachDocumentsToLoan($loan);

        // Clean up the draft loan — documents have been moved to the final loan,
        // so the empty draft is no longer needed. Only delete if the draft is
        // a different record from the final loan (safety check).
        if ($this->draftLoanId && $this->draftLoanId !== $loan->id) {
            $draft = Loan::find($this->draftLoanId);
            if ($draft && $draft->status === 'draft') {
                $draft->delete();
            }
        }

        // Clear wizard session now that the request is submitted
        session()->forget($this->getWizardId());

        // Start workflow if required
        if ($requiresApproval) {
            try {
                app(\QuickerFaster\UILibrary\Services\Workflow\WorkflowEngine::class)->start($loan);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to start loan workflow', ['loan_id' => $loan->id, 'error' => $e->getMessage()]);
            }
        }

        $this->successMessage = $requiresApproval
            ? 'Your loan request has been submitted and is pending approval.'
            : 'Your loan request has been approved.';

        $this->dispatch('loanRequestSubmitted');
    }


    /**
     * Save the current form state as a draft loan.
     */
    public function confirmCancel(): void
    {
        $this->dispatch("showAlert", [
            "type" => "confirm",
            "title" => "Cancel Loan Request?",
            "message" => "Any data you have already saved will remain in the system. Do you want to keep it or delete all progress?",
            "icon" => "fas fa-question-circle",
            "confirmText" => "Keep Data",
            "cancelText" => "Delete Progress",
            "confirmEvent" => "cancelKeep",
            "cancelEvent" => "cancelDelete",
        ]);
    }

    public function cancelKeep(): void
    {
        $this->saveDraft();
    }

        public function saveDraft(): void
    {
        $employee = Employee::find($this->employeeId);
        if (!$employee) { $this->errorMessage = "Employee record not found."; return; }
        $loanType = $this->loanTypeId ? LoanType::find($this->loanTypeId) : null;
        $rate = $loanType?->default_interest_rate ?? 0;

        if ($this->draftLoanId) {
            // Update existing draft
            $loan = Loan::find($this->draftLoanId);
            if ($loan) {
                $loan->update([
                    "loan_type_id" => $this->loanTypeId, "amount" => $this->amount ?? 0,
                    "interest_rate" => $rate, "repayment_months" => $this->repaymentMonths,
                    "monthly_installment" => $this->monthlyInstallment ?? 0,
                    "total_repayable" => $this->totalRepayable ?? 0,
                    "balance" => $this->totalRepayable ?? 0,
                    "reason" => $this->reason,
                    "current_step" => $this->step,
                ]);
                $this->attachDocumentsToLoan($loan);
            }
        } else {
            // Check for an existing draft (e.g., session lost but loan exists)
            $existingDraft = Loan::where('employee_id', $this->employeeId)
                ->where('status', 'draft')
                ->first();

            if ($existingDraft) {
                $this->draftLoanId = $existingDraft->id;
                $existingDraft->update([
                    "loan_type_id" => $this->loanTypeId, "amount" => $this->amount ?? 0,
                    "interest_rate" => $rate, "repayment_months" => $this->repaymentMonths,
                    "monthly_installment" => $this->monthlyInstallment ?? 0,
                    "total_repayable" => $this->totalRepayable ?? 0,
                    "balance" => $this->totalRepayable ?? 0,
                    "reason" => $this->reason,
                    "current_step" => $this->step,
                ]);
                $this->attachDocumentsToLoan($existingDraft);
                $loan = $existingDraft;
            } else {
                $loanNumber = $this->generateLoanNumber($employee->company_id);
                $loan = Loan::create([
                "company_id" => $employee->company_id, "employee_id" => $this->employeeId,
                "loan_type_id" => $this->loanTypeId, "loan_number" => $loanNumber,
                "amount" => $this->amount ?? 0, "interest_rate" => $rate,
                "repayment_months" => $this->repaymentMonths,
                "monthly_installment" => $this->monthlyInstallment ?? 0,
                "total_repayable" => $this->totalRepayable ?? 0,
                "paid_amount" => 0, "balance" => $this->totalRepayable ?? 0,
                "status" => "draft", "reason" => $this->reason,
                "request_date" => now()->toDateString(),
                ]);
                $this->draftLoanId = $loan->id;
                $this->attachDocumentsToLoan($loan);
            }
        }
        // Store current step on loan for resume
        if (isset($loan) && $loan->exists) {
            $loan->update(['current_step' => $this->step]);
        }
        session()->forget($this->getWizardId());
        $this->dispatch("loanDraftSaved");
    }
    public function cancelDelete(): void
    {
        // Delete draft loan and its documents if one was created
        if ($this->draftLoanId) {
            $loan = Loan::find($this->draftLoanId);
            if ($loan && $loan->status === 'draft') {
                // Delete uploaded documents
                $docs = Document::where('documentable_type', Loan::class)
                    ->where('documentable_id', $loan->id)
                    ->get();
                foreach ($docs as $doc) {
                    \Illuminate\Support\Facades\Storage::disk($doc->disk ?? 'public')->delete($doc->file_path);
                    $doc->delete();
                }
                $loan->delete();
            }
        }
        // Also clean up any documents attached to documentable_id = 0 (pre-submission)
        Document::where('documentable_type', Loan::class)
            ->where('documentable_id', 0)
            ->whereIn('id', $this->uploadedDocuments->pluck('id'))
            ->each(function ($doc) {
                \Illuminate\Support\Facades\Storage::disk($doc->disk ?? 'public')->delete($doc->file_path);
                $doc->delete();
            });
        session()->forget($this->getWizardId());
        $this->dispatch('loanRequestCancelled');
    }

    protected function generateLoanNumber(int $companyId): string
    {
        $year = date('Y');
        $prefix = "LN-{$year}-";
        $lastLoan = Loan::withTrashed()->where('loan_number', 'like', $prefix . '%')->orderBy('loan_number', 'desc')->first();
        $nextNumber = $lastLoan ? ((int) substr($lastLoan->loan_number, -5)) + 1 : 1;
        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function getStepLabels(): array
    {
        return [
            1 => 'Loan Type',
            2 => 'Amount & Reason',
            3 => 'Repayment Terms',
            4 => 'Documents',
            5 => 'Review & Submit',
        ];
    }

    public function render()
    {
        return view('loans::livewire.loan-request-form');
    }
}
