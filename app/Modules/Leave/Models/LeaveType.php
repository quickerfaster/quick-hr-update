<?php

namespace App\Modules\Leave\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Leave\Models\LeaveBalance;

use Illuminate\Database\Eloquent\Model;


class LeaveType extends Model
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;




    protected $table = 'leave_types';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'name', 'code', 'description', 'deducts_from_balance', 'requires_approval', 'max_days_per_request', 'is_active'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'deducts_from_balance' => 'boolean',
        'requires_approval' => 'boolean',
        'max_days_per_request' => 'integer',
        'is_active' => 'boolean'
    ];

    protected $attributes = [
        'deducts_from_balance' => true,
        'requires_approval' => true,
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

    public function leaveBalances()
    {
        return $this->hasMany(\App\Modules\Leave\Models\LeaveBalance::class, 'leave_type_id', 'id');
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
        return \App\Modules\Leave\Database\Factories\LeaveTypeFactory::new();
    }
}