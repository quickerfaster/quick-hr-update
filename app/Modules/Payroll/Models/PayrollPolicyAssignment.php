<?php

namespace App\Modules\Payroll\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Payroll\Models\PayrollPolicy;

use Illuminate\Database\Eloquent\Model;


class PayrollPolicyAssignment extends Model
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;




    protected $table = 'payroll_policy_assignments';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'payroll_policy_id', 'assignable_type', 'assignable_id', 'priority', 'effective_date', 'expiry_date', 'is_active'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'assignable_id' => 'integer',
        'priority' => 'integer',
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

    protected $attributes = [
        'priority' => 0,
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

    public function payrollPolicy()
    {
        return $this->belongsTo(\App\Modules\Payroll\Models\PayrollPolicy::class, 'payroll_policy_id', 'id');
    }

    public function assignable()
    {
        return $this->morphTo('assignable', 'assignable_type', 'assignable_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Modules\Payroll\Models\Company::class, 'company_id', 'id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \App\Modules\Hr\Database\Factories\PayrollPolicyAssignmentFactory::new();
    }
}
