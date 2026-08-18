<?php

namespace App\Modules\Hr\Models;

use App\Modules\Organization\Models\Department as BaseDepartment;
use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Hr\Models\EmployeePosition;
use App\Modules\Hr\Models\Company;

class Department extends BaseDepartment
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;

    protected $table = 'departments';

    public $timestamps = true;

    protected $fillable = [
        'name', 'code', 'description', 'company_id', 'parent_department_id',
        'cost_center', 'branch_id', 'manager_id', 'metadata', 'is_active'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    protected $attributes = [
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

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Company::class, 'company_id', 'id');
    }

    public function employeePositions()
    {
        return $this->hasMany(\App\Modules\Hr\Models\EmployeePosition::class, 'department_id', 'id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \App\Modules\Hr\Database\Factories\DepartmentFactory::new();
    }
}
