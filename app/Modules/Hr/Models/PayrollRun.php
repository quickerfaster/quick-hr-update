<?php

namespace App\Modules\Hr\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Hr\Models\PaySchedule;
use App\Modules\Hr\Models\PayrollPayslip;
use App\Modules\Hr\Models\PayrollRunAdjustment;
use App\Models\User;
use QuickerFaster\UILibrary\Contracts\Workflow\Workflowable;
use QuickerFaster\UILibrary\Traits\Workflows\HasWorkflow;
use Illuminate\Database\Eloquent\Model;


class PayrollRun extends Model implements Workflowable
{
    use HasCompanyScope;
    use HasFactory;
    use HasWorkflow;





    protected $table = 'payroll_runs';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'title', 'pay_schedule_id', 'period_start', 'period_end', 'status', 'current_step', 'calculation_status', 'total_gross_pay', 'total_deductions', 'total_taxes', 'total_employer_contributions', 'total_cash_required', 'processed_by', 'processed_at', 'base_currency', 'payment_date', 'reconciliation_status', 'reconciled_at', 'payment_batch_id', 'total_employee_contributions', 'total_income_tax', 'total_bonus', 'total_commission', 'total_reimbursement', 'approved_by_user_id', 'approved_at', 'total_employees', 'processed_employees', 'notes', 'is_multi_company', 'per_company_summaries'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'current_step' => 'integer',
        'total_gross_pay' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_taxes' => 'decimal:2',
        'total_employer_contributions' => 'decimal:2',
        'total_cash_required' => 'decimal:2',
        'processed_at' => 'datetime',
        'payment_date' => 'date',
        'reconciled_at' => 'datetime',
        'total_employee_contributions' => 'decimal:2',
        'total_income_tax' => 'decimal:2',
        'total_bonus' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'total_reimbursement' => 'decimal:2',
        'approved_at' => 'datetime',
        'total_employees' => 'integer',
        'processed_employees' => 'integer',
        'failed_at' => 'datetime',
        'finalized_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'is_multi_company' => 'boolean',
        'per_company_summaries' => 'array',
    ];

    protected $attributes = [
        'status' => 'draft',
        'current_step' => 1,
        'calculation_status' => 'pending',
        'total_gross_pay' => 0,
        'total_deductions' => 0,
        'total_taxes' => 0,
        'total_employer_contributions' => 0,
        'total_cash_required' => 0,
        'base_currency' => 'USD',
        'reconciliation_status' => 'pending',
        'total_employee_contributions' => 0,
        'total_income_tax' => 0,
        'total_bonus' => 0,
        'total_commission' => 0,
        'total_reimbursement' => 0,
        'total_employees' => 0,
        'processed_employees' => 0,
        'is_multi_company' => false,
    ];

    protected $dispatchesEvents = [

    ];

    /**
     * Validation rules for the model.
     */
    protected static $rules = [

    ];

    /**
     * Custom validation messages.
     */
    protected static $messages = [

    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

    }

    /**
     * Validate the model instance.
     */
    public function validate()
    {
        $validator = Validator::make($this->attributesToArray(), static::$rules, static::$messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return true;
    }

    /**
     * Save the model to the database with validation.
     */
    public function save(array $options = [])
    {
        $this->validate();
        return parent::save($options);
    }

    public function paySchedule()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\PaySchedule::class, 'pay_schedule_id', 'id');
    }

    public function payslips()
    {
        return $this->hasMany(\App\Modules\Hr\Models\PayrollPayslip::class, 'payroll_run_id', 'id');
    }

    public function adjustments()
    {
        return $this->hasMany(\App\Modules\Hr\Models\PayrollRunAdjustment::class, 'payroll_run_id', 'id');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by_user_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Company::class, 'company_id', 'id');
    }

    /**
     * Whether this payroll run has been finalized.
     */
    public function getIsFinalizedAttribute(): bool
    {
        return $this->finalized_at !== null;
    }

    /**
     * The workflow definition key for this model.
     */
    public function getWorkflowDefinitionKey(): string
    {
        return 'payroll_run';
    }

    /**
     * Additional context used for workflow routing decisions.
     */
    public function getWorkflowContext(): array
    {
        return [
            'workspace_id' => $this->company_id,
            'pay_schedule_id' => $this->pay_schedule_id,
            'period_start' => $this->period_start?->toDateString(),
            'period_end' => $this->period_end?->toDateString(),
        ];
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \App\Modules\Hr\Database\Factories\PayrollRunFactory::new();
    }
}
