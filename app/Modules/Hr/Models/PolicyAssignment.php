<?php

namespace App\Modules\Hr\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Hr\Models\AttendancePolicy;

use Illuminate\Database\Eloquent\Model;


class PolicyAssignment extends Model
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;




    protected $table = 'policy_assignments';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'attendance_policy_id', 'assignable_type', 'assignable_id', 'priority'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'assignable_id' => 'integer',
        'priority' => 'integer'
    ];

    protected $attributes = [
        'priority' => 0
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

    public function attendancePolicy()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\AttendancePolicy::class, 'attendance_policy_id', 'id');
    }

    public function assignable()
    {
        return $this->morphTo('assignable', 'assignable_type', 'assignable_id', 'id');
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
        return \App\Modules\Hr\Database\Factories\PolicyAssignmentFactory::new();
    }
}
