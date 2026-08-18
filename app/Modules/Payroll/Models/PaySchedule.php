<?php

namespace App\Modules\Payroll\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Payroll\Models\EmployeePayrollProfile;

use Illuminate\Database\Eloquent\Model;


class PaySchedule extends Model
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;




    protected $table = 'pay_schedules';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'name', 'code', 'frequency', 'description', 'first_period_start_date', 'next_pay_date', 'payment_delay_days', 'country_code', 'state_code', 'currency_code', 'timezone', 'is_active', 'is_default'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'first_period_start_date' => 'date',
        'next_pay_date' => 'date',
        'payment_delay_days' => 'integer',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

    protected $attributes = [
        'payment_delay_days' => 0,
        'country_code' => 'US',
        'currency_code' => 'USD',
        'timezone' => 'America/New_York',
        'is_active' => true,
        'is_default' => false
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

    public function payrollRuns()
    {
        return $this->hasMany(\App\Modules\Payroll\Models\PayrollRun::class, 'pay_schedule_id', 'id');
    }

    public function employeeProfiles()
    {
        return $this->hasMany(\App\Modules\Payroll\Models\EmployeePayrollProfile::class, 'pay_schedule_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Modules\Payroll\Models\Company::class, 'company_id', 'id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \App\Modules\Hr\Database\Factories\PayScheduleFactory::new();
    }
}
