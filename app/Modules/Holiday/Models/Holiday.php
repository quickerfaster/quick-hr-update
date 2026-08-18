<?php

namespace App\Modules\Holiday\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Holiday\Models\HolidayCalendar;

use Illuminate\Database\Eloquent\Model;


class Holiday extends Model
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;




    protected $table = 'holidays';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'calendar_id', 'name', 'description', 'date', 'observed_date', 'is_recurring', 'recurrence_pattern', 'recurrence_rule', 'holiday_type', 'is_paid_holiday', 'affects_payroll', 'business_impact', 'eligible_employee_types', 'holiday_pay_rate', 'minimum_hours_for_pay', 'country_code', 'region_code', 'is_half_day', 'half_day_end_time', 'is_active', 'year', 'generated_from_template', 'override_id', 'last_synced_at'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'date' => 'date',
        'observed_date' => 'date',
        'is_recurring' => 'boolean',
        'is_paid_holiday' => 'boolean',
        'affects_payroll' => 'boolean',
        'holiday_pay_rate' => 'decimal:2',
        'minimum_hours_for_pay' => 'decimal:2',
        'is_half_day' => 'boolean',
        'is_active' => 'boolean',
        'year' => 'integer',
        'generated_from_template' => 'boolean',
        'override_id' => 'integer',
        'last_synced_at' => 'datetime'
    ];

    protected $attributes = [
        'is_recurring' => true,
        'recurrence_pattern' => 'yearly_fixed',
        'holiday_type' => 'company',
        'is_paid_holiday' => true,
        'affects_payroll' => true,
        'business_impact' => 'office_closed',
        'eligible_employee_types' => array (
  0 => 'full_time',
  1 => 'part_time',
),
        'holiday_pay_rate' => 1,
        'minimum_hours_for_pay' => 8,
        'is_half_day' => false,
        'is_active' => true,
        'generated_from_template' => false
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

    public function calendar()
    {
        return $this->belongsTo(\App\Modules\Holiday\Models\HolidayCalendar::class, 'calendar_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Modules\Organization\Models\Company::class, 'company_id', 'id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \App\Modules\Holiday\Database\Factories\HolidayFactory::new();
    }
}