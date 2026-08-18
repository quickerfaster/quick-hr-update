<?php

namespace App\Modules\Attendance\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Model;


class ClockEvent extends Model
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;



    protected $table = 'clock_events';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'employee_id', 'employee_number', 'event_type', 'timestamp', 'method', 'latitude', 'longitude', 'location_name', 'timezone', 'ip_address', 'device_id', 'device_name', 'sync_status', 'sync_attempts'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'sync_attempts' => 'integer'
    ];

    protected $attributes = [
        'method' => 'web',
        'timezone' => 'UTC',
        'sync_status' => 'pending',
        'sync_attempts' => 0
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

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \App\Modules\Attendance\Database\Factories\ClockEventFactory::new();
    }
}
