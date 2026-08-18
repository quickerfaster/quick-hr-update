<?php

namespace App\Modules\Hr\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\PayrollPolicy;

use Illuminate\Database\Eloquent\Model;


class EmployeeAdjustmentProfile extends Model
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;




    protected $table = 'employee_adjustment_profiles';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'employee_id', 'type', 'label', 'calculation_type', 'value', 'effective_date', 'expiry_date', 'is_active', 'policy_id'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'value' => 'decimal:2',
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer'
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

    public function employee()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Employee::class, 'employee_id', 'id');
    }

    public function policy()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\PayrollPolicy::class, 'policy_id', 'id');
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
        return \App\Modules\Hr\Database\Factories\EmployeeAdjustmentProfileFactory::new();
    }
}
