<?php

namespace App\Modules\Loans\Models;

use QuickerFaster\UILibrary\Contracts\Documents\Documentable;
use QuickerFaster\UILibrary\Contracts\Workflow\Workflowable;
use QuickerFaster\UILibrary\Traits\HasCompanyScope;
use QuickerFaster\UILibrary\Traits\Documents\HasDocuments;
use QuickerFaster\UILibrary\Traits\Workflows\HasWorkflow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Hr\Models\Employee;

class Loan extends Model implements Documentable, Workflowable
{
    use HasCompanyScope;
    use HasDocuments;
    use HasFactory;
    use HasWorkflow;
    use SoftDeletes;

    protected $table = 'loans';

    public $timestamps = true;

    protected $fillable = [
        'company_id',
        'employee_id',
        'loan_type_id',
        'loan_number',
        'amount',
        'interest_rate',
        'repayment_months',
        'monthly_installment',
        'total_repayable',
        'paid_amount',
        'balance',
        'status',
        'current_step',
        'reason',
        'notes',
        'request_date',
        'approval_date',
        'start_date',
        'expected_end_date',
        'approved_by',
        'repayment_schedule',
    ];

    protected $casts = [
        'current_step' => 'integer',
        'amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_installment' => 'decimal:2',
        'total_repayable' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'request_date' => 'date',
        'approval_date' => 'date',
        'start_date' => 'date',
        'expected_end_date' => 'date',
        'repayment_schedule' => 'array',
    ];

    /**
     * Accessors appended to the model's array/JSON form.
     * 'title' is used by workflow notifications (email subjects/bodies).
     * Matches LeaveRequest which also appends 'title'.
     */
    protected $appends = ['title', 'currency_code'];

    protected $attributes = [
        'status' => 'draft',
        'interest_rate' => 0,
        'repayment_months' => 1,
        'paid_amount' => 0,
    ];

    /*
    |--------------------------------------------------------------------------
    | Workflowable Contract
    |--------------------------------------------------------------------------
    */

    public function getWorkflowDefinitionKey(): string
    {
        return 'loan_request';
    }

    public function getWorkflowContext(): array
    {
        return [
            'company_id' => $this->company_id,
            'employee_id' => $this->employee_id,
            'amount' => $this->amount,
            'loan_type_id' => $this->loan_type_id,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Documentable Contract
    |--------------------------------------------------------------------------
    */

    public function getDocumentableId(): int|string
    {
        return $this->getKey();
    }

    public function getDocumentType(): string
    {
        return 'loan_request';
    }

    public function getDocumentStoragePath(): string
    {
        return 'loans/' . $this->id;
    }

    public function getDocumentTemplateData(): array
    {
        return [
            'loan_number' => $this->loan_number,
            'loan_amount' => $this->amount,
            'employee_name' => $this->employee?->first_name . ' ' . $this->employee?->last_name,
            'loan_type' => $this->loanType?->name,
            'status' => $this->status,
        ];
    }

    /**
     * Title accessor for workflow notifications.
     * The WorkflowEngine's notifyTransition() includes 'title' in the
     * notification payload via $workflowable?->title. This provides a
     * human-readable identifier (loan number) for notification subjects/bodies.
     */
    public function getTitleAttribute(): string
    {
        return $this->loan_number;
    }

    /**
     * Currency code accessor — resolves from the loan's company.
     *
     * Used by the library's CurrencyField (DataTable) and by LoanDetail
     * for currency symbol display. Uses a direct DB query to avoid any
     * relationship scoping issues on the Company model.
     */
    public function getCurrencyCodeAttribute(): string
    {
        // The DataTable selects only visible columns, so company_id may not
        // be loaded. Fall back to a fresh query if needed.
        $companyId = $this->company_id;

        if ($companyId === null && $this->exists) {
            $companyId = static::where('id', $this->id)->value('company_id');
        }

        if (!$companyId) {
            return 'USD';
        }

        return \App\Modules\Hr\Models\Company::where('id', $companyId)
            ->value('currency_code') ?? 'USD';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Company::class, 'company_id');
    }

    public function loanType(): BelongsTo
    {
        return $this->belongsTo(LoanType::class, 'loan_type_id');
    }

    /**
     * Snake-case alias for the DataTable relationship resolver.
     * The library derives the relationship name from the field name
     * (loan_type_id → loan_type), so we provide this alias.
     */
    public function loan_type(): BelongsTo
    {
        return $this->loanType();
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class, 'loan_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /**
     * Snake-case alias for the DataTable relationship resolver.
     */
    public function approved_by(): BelongsTo
    {
        return $this->approvedBy();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }
}
