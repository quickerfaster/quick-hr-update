<?php

namespace App\Modules\Attendance\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Hr\Models\Employee;
use App\Modules\Attendance\Models\WorkPattern;

use Illuminate\Database\Eloquent\Model;


class EmployeeWorkPattern extends Model
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;




    protected $table = 'employee_work_patterns';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'employee_id', 'work_pattern_id', 'start_date', 'end_date'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
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

    public function employee()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Employee::class, 'employee_id', 'id');
    }

    public function workPattern()
    {
        return $this->belongsTo(\App\Modules\Attendance\Models\WorkPattern::class, 'work_pattern_id', 'id');
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
        return \App\Modules\Attendance\Database\Factories\EmployeeWorkPatternFactory::new();
    }
}
