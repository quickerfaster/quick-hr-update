<?php

namespace App\Modules\Hr\Models;

use QuickerFaster\UILibrary\Contracts\Invitations\Invitable;
use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Hr\Models\EmployeePosition;
use App\Modules\Hr\Models\EmployeeJobHistory;
use App\Modules\Payroll\Models\EmployeePayrollProfile;
use App\Modules\Hr\Models\EmployeeProfile;
use App\Modules\Hr\Models\Company;
use App\Modules\Hr\Models\Document;
use App\Modules\Attendance\Models\EmployeeWorkPattern;
use App\Models\User;
use App\Modules\Hr\Models\EmployeeGroup;
use App\Modules\Hr\Models\Team;
use App\Modules\Hr\Models\Tag;

use Illuminate\Database\Eloquent\Model;


class Employee extends Model implements Invitable
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;




    protected $table = 'employees';



    public $timestamps = true;


    protected $fillable = [
        'employee_number', 'first_name', 'last_name', 'email', 'phone', 'company_id', 'employee_group_id', 'hire_date', 'user_id', 'tag_ids'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'hire_date' => 'date',
        'tag_ids' => 'array'
    ];

    protected $attributes = [

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

    public function employeePosition()
    {
        return $this->hasOne(\App\Modules\Hr\Models\EmployeePosition::class, 'employee_id', 'id');
    }

    public function jobHistory()
    {
        return $this->hasMany(\App\Modules\Hr\Models\EmployeeJobHistory::class, 'employee_id', 'id');
    }

    public function employeePayrollProfile()
    {
        return $this->hasOne(\App\Modules\Payroll\Models\EmployeePayrollProfile::class, 'employee_id', 'id');
    }

    public function employeeProfile()
    {
        return $this->hasOne(\App\Modules\Hr\Models\EmployeeProfile::class, 'employee_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Company::class, 'company_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany(\App\Modules\Hr\Models\Document::class, 'employee_id', 'id');
    }

    public function employeeWorkPatterns()
    {
        return $this->hasMany(\App\Modules\Attendance\Models\EmployeeWorkPattern::class, 'employee_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    public function employeeGroup()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\EmployeeGroup::class, 'employee_group_id', 'id');
    }

    public function teams()
    {
        return $this->belongsToMany(\App\Modules\Hr\Models\Team::class, 'employee_team', 'employee_id', 'team_id', 'id', 'id');
    }

    public function tags()
    {
        return $this->morphToMany(\App\Modules\Hr\Models\Tag::class, 'taggable', 'taggables', 'taggable_id', 'tag_id', 'id', 'id');
    }

    /**
     * Get the invitable type key for polymorphic linking.
     */
    public function getInvitableType(): string
    {
        return 'employee';
    }

    /**
     * Get the unique identifier for this invitable entity.
     */
    public function getInvitableId(): int|string
    {
        return $this->id;
    }

    public function clockEvents(): HasMany
    {
        return $this->hasMany(ClockEvent::class, 'employee_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \App\Modules\Hr\Database\Factories\EmployeeFactory::new();
    }
}
