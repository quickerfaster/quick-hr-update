<?php

namespace App\Modules\Attendance\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Models\ClockEvent;

use Illuminate\Database\Eloquent\Model;


class AttendanceSession extends Model
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;




    protected $table = 'attendance_sessions';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'attendance_id', 'start_time', 'end_time', 'duration_hours', 'session_type', 'is_adjusted', 'adjustment_reason', 'clock_in_event_id', 'clock_out_event_id', 'is_overnight', 'adjusted_by', 'adjusted_at', 'calculated_duration', 'validation_status', 'validation_notes'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_hours' => 'decimal:2',
        'is_adjusted' => 'boolean',
        'clock_in_event_id' => 'integer',
        'clock_out_event_id' => 'integer',
        'is_overnight' => 'boolean',
        'adjusted_at' => 'datetime',
        'calculated_duration' => 'decimal:2'
    ];

    protected $attributes = [
        'duration_hours' => 0,
        'session_type' => 'work',
        'is_adjusted' => false,
        'is_overnight' => false,
        'validation_status' => 'valid'
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

    public function attendance()
    {
        return $this->belongsTo(\App\Modules\Attendance\Models\Attendance::class, 'attendance_id', 'id');
    }

    public function clockInEvent()
    {
        return $this->belongsTo(\App\Modules\Attendance\Models\ClockEvent::class, 'clock_in_event_id', 'id');
    }

    public function clockOutEvent()
    {
        return $this->belongsTo(\App\Modules\Attendance\Models\ClockEvent::class, 'clock_out_event_id', 'id');
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
        return \App\Modules\Attendance\Database\Factories\AttendanceSessionFactory::new();
    }
}
