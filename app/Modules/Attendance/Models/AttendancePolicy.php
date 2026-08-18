<?php

namespace App\Modules\Attendance\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


use Illuminate\Database\Eloquent\Model;


class AttendancePolicy extends Model
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;




    protected $table = 'attendance_policies';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'name', 'code', 'description', 'grace_period_minutes', 'early_departure_grace_minutes', 'overtime_daily_threshold_hours', 'overtime_weekly_threshold_hours', 'max_daily_overtime_hours', 'overtime_multiplier', 'double_time_threshold_hours', 'double_time_multiplier', 'requires_break_after_hours', 'break_duration_minutes', 'unpaid_break_minutes', 'country_code', 'state_code', 'applies_to_shift_categories', 'effective_date', 'expiration_date', 'is_active', 'is_default'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'grace_period_minutes' => 'integer',
        'early_departure_grace_minutes' => 'integer',
        'overtime_daily_threshold_hours' => 'decimal:2',
        'overtime_weekly_threshold_hours' => 'decimal:2',
        'max_daily_overtime_hours' => 'decimal:2',
        'overtime_multiplier' => 'decimal:2',
        'double_time_threshold_hours' => 'decimal:2',
        'double_time_multiplier' => 'decimal:2',
        'requires_break_after_hours' => 'string',
        'break_duration_minutes' => 'string',
        'unpaid_break_minutes' => 'integer',
        'effective_date' => 'date',
        'expiration_date' => 'date',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'version' => 'integer',
        'last_updated_at' => 'datetime'
    ];

    protected $attributes = [
        'grace_period_minutes' => 5,
        'early_departure_grace_minutes' => 5,
        'overtime_daily_threshold_hours' => 8,
        'overtime_weekly_threshold_hours' => 40,
        'max_daily_overtime_hours' => 4,
        'overtime_multiplier' => 1.5,
        'double_time_threshold_hours' => 12,
        'double_time_multiplier' => 2,
        'requires_break_after_hours' => 5,
        'break_duration_minutes' => 30,
        'unpaid_break_minutes' => 0,
        'applies_to_shift_categories' => array (
  0 => 'regular',
),
        'is_active' => true,
        'is_default' => false,
        'version' => 1
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



    public function company()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Company::class, 'company_id', 'id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \App\Modules\Attendance\Database\Factories\AttendancePolicyFactory::new();
    }
}
