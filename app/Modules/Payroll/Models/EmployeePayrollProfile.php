<?php

namespace App\Modules\Payroll\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Hr\Models\Employee;
use App\Modules\Payroll\Models\PaySchedule;

use Illuminate\Database\Eloquent\Model;


class EmployeePayrollProfile extends Model
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;




    protected $table = 'employee_payroll_profiles';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'employee_id', 'pay_schedule_id', 'bank_name', 'account_type', 'bank_sort_code', 'bank_code', 'bank_account_name', 'bank_account_number', 'bank_routing_number', 'bank_iban', 'bank_swift', 'payment_method', 'tax_filing_status', 'allowances', 'extra_withholding', 'is_exempt_from_federal_tax', 'override_country_code', 'override_state_code', 'currency_code', 'effective_date', 'expiry_date', 'is_active'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'bank_sort_code' => 'encrypted',
        'bank_code' => 'encrypted',
        'bank_account_name' => 'encrypted',
        'bank_account_number' => 'encrypted',
        'bank_routing_number' => 'encrypted',
        'bank_iban' => 'encrypted',
        'bank_swift' => 'encrypted',
        'allowances' => 'integer',
        'extra_withholding' => 'decimal:2',
        'is_exempt_from_federal_tax' => 'boolean',
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

    protected $attributes = [
        'account_type' => 'checking',
        'payment_method' => 'bank_transfer',
        'allowances' => 0,
        'extra_withholding' => 0,
        'is_exempt_from_federal_tax' => false,
        'override_country_code' => 'US',
        'is_active' => true
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

    public function employee()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Employee::class, 'employee_id', 'id');
    }

    public function paySchedule()
    {
        return $this->belongsTo(\App\Modules\Payroll\Models\PaySchedule::class, 'pay_schedule_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Company::class, 'company_id', 'id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \App\Modules\Hr\Database\Factories\EmployeePayrollProfileFactory::new();
    }
}
