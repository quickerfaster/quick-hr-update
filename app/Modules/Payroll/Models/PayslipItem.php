<?php

namespace App\Modules\Payroll\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Payroll\Models\PayrollPayslip;
use App\Modules\Payroll\Models\PayrollPolicy;
use App\Modules\Payroll\Models\PayrollRunAdjustment;
use App\Modules\Payroll\Models\EmployeeAdjustmentProfile;

use Illuminate\Database\Eloquent\Model;


class PayslipItem extends Model
{
    use HasCompanyScope;
    use HasFactory;






    protected $table = 'payslip_items';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'payslip_id', 'type', 'label', 'amount', 'policy_id', 'adjustment_id', 'employee_adjustment_profile_id', 'calculation_metadata'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'calculation_metadata' => 'array'
    ];

    protected $attributes = [

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

    public function payslip()
    {
        return $this->belongsTo(\App\Modules\Payroll\Models\PayrollPayslip::class, 'payslip_id', 'id');
    }

    public function policy()
    {
        return $this->belongsTo(\App\Modules\Payroll\Models\PayrollPolicy::class, 'policy_id', 'id');
    }

    public function adjustment()
    {
        return $this->belongsTo(\App\Modules\Payroll\Models\PayrollRunAdjustment::class, 'adjustment_id', 'id');
    }

    public function employeeAdjustmentProfile()
    {
        return $this->belongsTo(\App\Modules\Payroll\Models\EmployeeAdjustmentProfile::class, 'employee_adjustment_profile_id', 'id');
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
        return \App\Modules\Hr\Database\Factories\PayslipItemFactory::new();
    }
}
