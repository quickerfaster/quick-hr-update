<?php

namespace App\Modules\Hr\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Hr\Models\PayrollRun;
use App\Modules\Hr\Models\Employee;

use Illuminate\Database\Eloquent\Model;


class PayrollRunAdjustment extends Model
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;




    protected $table = 'payroll_run_adjustments';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'payroll_run_id', 'employee_id', 'type', 'label', 'amount', 'note', 'source_type', 'source_id'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'source_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

    protected $attributes = [
        'source_type' => 'manual'
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

    public function payrollRun()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\PayrollRun::class, 'payroll_run_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Employee::class, 'employee_id', 'id');
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
        return \App\Modules\Hr\Database\Factories\PayrollRunAdjustmentFactory::new();
    }
}
