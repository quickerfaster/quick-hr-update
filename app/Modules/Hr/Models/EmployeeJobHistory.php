<?php

namespace App\Modules\Hr\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Hr\Models\Employee;

use Illuminate\Database\Eloquent\Model;


class EmployeeJobHistory extends Model
{
    use HasCompanyScope;
    use HasFactory;






    protected $table = 'employee_job_histories';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'employee_id', 'effective_date', 'end_date', 'change_reason', 'notes', 'job_title', 'department', 'manager_name', 'pay_type', 'hourly_rate', 'base_salary', 'salary_currency', 'pay_frequency', 'employment_status', 'location', 'shift', 'changed_by_user_id'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'effective_date' => 'date',
        'end_date' => 'date',
        'hourly_rate' => 'decimal:2',
        'base_salary' => 'decimal:2',
        'changed_by_user_id' => 'integer'
    ];

    protected $attributes = [
        'salary_currency' => 'USD'
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

    public function company()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Company::class, 'company_id', 'id');
    }

    public function changedByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'changed_by_user_id', 'id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \App\Modules\Hr\Database\Factories\EmployeeJobHistoryFactory::new();
    }
}
