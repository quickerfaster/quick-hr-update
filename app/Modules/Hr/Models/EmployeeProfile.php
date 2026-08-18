<?php

namespace App\Modules\Hr\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Hr\Models\Employee;

use Illuminate\Database\Eloquent\Model;


class EmployeeProfile extends Model
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;




    protected $table = 'employee_profiles';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'employee_id', 'photo', 'preferred_name', 'middle_name', 'date_of_birth', 'gender', 'nationality', 'marital_status', 'bio', 'personal_email', 'personal_phone', 'work_phone', 'address_street', 'address_city', 'address_state', 'address_postal_code', 'address_country', 'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship', 'passport_number', 'passport_expiry_date', 'national_id_number'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'passport_expiry_date' => 'date'
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
        return \App\Modules\Hr\Database\Factories\EmployeeProfileFactory::new();
    }
}
