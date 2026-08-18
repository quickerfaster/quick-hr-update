<?php

namespace App\Modules\Hr\Models;

use App\Modules\Organization\Models\Team as BaseTeam;
use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Hr\Models\Employee;

class Team extends BaseTeam
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;

    protected $table = 'teams';

    public $timestamps = true;

    protected $fillable = [
        'company_id', 'name', 'code', 'description', 'team_lead_id',
        'department_id', 'type', 'metadata', 'is_active'
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

    public function employees()
    {
        return $this->belongsToMany(\App\Modules\Hr\Models\Employee::class, 'employee_team', 'team_id', 'employee_id', 'id', 'id');
    }

    public function teamLead()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Employee::class, 'team_lead_id', 'id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \App\Modules\Hr\Database\Factories\TeamFactory::new();
    }
}
