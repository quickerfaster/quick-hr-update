<?php

namespace App\Modules\Hr\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Hr\Models\Employee;

use Illuminate\Database\Eloquent\Model;


class EmployeeGroup extends Model
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;




    protected $table = 'employee_groups';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'name', 'code', 'group_type', 'description', 'is_active', 'dynamic_rules'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'is_active' => 'boolean',
        'dynamic_rules' => 'array',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

    protected $attributes = [
        'group_type' => 'manual',
        'is_active' => true
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

    public function employees()
    {
        return $this->hasMany(\App\Modules\Hr\Models\Employee::class, 'employee_group_id', 'id');
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
        return \App\Modules\Hr\Database\Factories\EmployeeGroupFactory::new();
    }
}
