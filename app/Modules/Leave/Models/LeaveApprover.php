<?php

namespace App\Modules\Leave\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Hr\Models\Employee;
use App\Modules\Leave\Models\LeaveType;

use Illuminate\Database\Eloquent\Model;


class LeaveApprover extends Model
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;




    protected $table = 'leave_approvers';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'employee_id', 'approver_id', 'approval_level', 'can_approve_all_types', 'leave_type_ids', 'max_approval_days', 'is_active'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'approval_level' => 'integer',
        'can_approve_all_types' => 'boolean',
        'max_approval_days' => 'integer',
        'is_active' => 'boolean'
    ];

    protected $attributes = [
        'approval_level' => 1,
        'can_approve_all_types' => true,
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

    public function approver()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Employee::class, 'approver_id', 'id');
    }

    public function leaveTypes()
    {
        return $this->belongsToMany(\App\Modules\Leave\Models\LeaveType::class, 'leave_approver_leave_type', 'leave_approver_id', 'leave_type_id', 'id', 'leave_type_id');
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
        return \App\Modules\Leave\Database\Factories\LeaveApproverFactory::new();
    }
}