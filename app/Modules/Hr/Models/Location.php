<?php

namespace App\Modules\Hr\Models;

use QuickerFaster\UILibrary\Core\Organization\Models\Location as BaseLocation;
use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Hr\Models\EmployeePosition;

class Location extends BaseLocation
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;

    protected $table = 'locations';

    public $timestamps = true;

    protected $fillable = [
        'name', 'code', 'address_line_1', 'address_line_2', 'city',
        'country_code', 'state_code', 'postal_code', 'phone', 'email',
        'website', 'timezone', 'is_active', 'is_remote', 'is_headquarters',
        'capacity', 'opening_hours', 'opening_date', 'closing_date',
        'latitude', 'longitude', 'geofence_radius', 'company_id',
        'type', 'address', 'metadata', 'external_id'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_remote' => 'boolean',
        'is_headquarters' => 'boolean',
        'capacity' => 'integer',
        'opening_date' => 'date',
        'closing_date' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'geofence_radius' => 'decimal:2',
        'last_synced_at' => 'datetime',
        'employee_count' => 'integer',
        'department_count' => 'integer',
        'metadata' => 'array',
    ];

    protected $attributes = [
        'country_code' => 'US',
        'timezone' => 'America/New_York',
        'is_active' => true,
        'is_remote' => false,
        'is_headquarters' => false,
        'geofence_radius' => 100,
        'employee_count' => 0,
        'department_count' => 0
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
        return $this->hasMany(\App\Modules\Hr\Models\EmployeePosition::class, 'location_id', 'id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \App\Modules\Hr\Database\Factories\LocationFactory::new();
    }
}
