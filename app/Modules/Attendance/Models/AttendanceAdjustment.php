<?php

namespace App\Modules\Attendance\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Attendance\Models\Attendance;

use Illuminate\Database\Eloquent\Model;


class AttendanceAdjustment extends Model
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;




    protected $table = 'attendance_adjustments';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'attendance_id', 'original_net_hours', 'original_status', 'adjusted_net_hours', 'adjusted_status', 'reason', 'adjusted_by', 'adjusted_at'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'original_net_hours' => 'decimal:2',
        'adjusted_net_hours' => 'decimal:2',
        'adjusted_at' => 'datetime'
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

    public function attendance()
    {
        return $this->belongsTo(\App\Modules\Attendance\Models\Attendance::class, 'attendance_id', 'id');
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
        return \App\Modules\Attendance\Database\Factories\AttendanceAdjustmentFactory::new();
    }
}
