<?php

namespace App\Modules\Hr\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\LeaveType;

use Illuminate\Database\Eloquent\Model;


class LeaveBalance extends Model
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;




    protected $table = 'leave_balances';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'employee_id', 'leave_type_id', 'balance', 'accrual_rate', 'accrual_frequency', 'year'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'accrual_rate' => 'decimal:2',
        'year' => 'integer'
    ];

    protected $attributes = [
        'balance' => 0,
        'accrual_rate' => 1,
        'accrual_frequency' => 'Monthly'
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

    public function leaveType()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\LeaveType::class, 'leave_type_id', 'id');
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
        return \App\Modules\Hr\Database\Factories\LeaveBalanceFactory::new();
    }
}
