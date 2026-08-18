<?php

namespace App\Modules\Holiday\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Holiday\Models\Holiday;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Location;

use Illuminate\Database\Eloquent\Model;


class HolidayCalendar extends Model
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;




    protected $table = 'holiday_calendars';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'name', 'year', 'description', 'country_code', 'region', 'applicable_to', 'is_default', 'is_active', 'holiday_count', 'last_updated'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'holiday_count' => 'integer',
        'last_updated' => 'datetime'
    ];

    protected $attributes = [
        'year' => 2026,
        'applicable_to' => 'all_employees',
        'is_default' => false,
        'is_active' => true,
        'holiday_count' => 0
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

    public function holidays()
    {
        return $this->hasMany(\App\Modules\Holiday\Models\Holiday::class, 'calendar_id', 'id');
    }

    public function departments()
    {
        return $this->belongsToMany(\App\Modules\Organization\Models\Department::class, 'department_holiday_calendar', 'holiday_calendar_id', 'department_id', 'id', 'id');
    }

    public function locations()
    {
        return $this->belongsToMany(\App\Modules\Organization\Models\Location::class, 'holiday_calendar_location', 'holiday_calendar_id', 'location_id', 'id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Modules\Organization\Models\Company::class, 'company_id', 'id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \App\Modules\Holiday\Database\Factories\HolidayCalendarFactory::new();
    }
}