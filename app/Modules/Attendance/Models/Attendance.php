<?php

namespace App\Modules\Attendance\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Hr\Models\Employee;
use App\Modules\Attendance\Models\AttendanceSession;
use App\Modules\Attendance\Models\AttendanceAdjustment;
use App\Modules\Leave\Models\LeaveRequest;
use App\Modules\Attendance\Models\Shift;
use App\Modules\Attendance\Models\AttendancePolicy;
use App\Modules\Attendance\Models\WorkPattern;

use Illuminate\Database\Eloquent\Model;


class Attendance extends Model
{
    use HasCompanyScope;
    use HasFactory;

    use SoftDeletes;



    protected $table = 'attendances';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'department_id', 'company', 'department', 'employee_id', 'date', 'shift_id', 'status', 'net_hours', 'regular_hours', 'overtime_hours', 'double_time_hours', 'absence_type', 'absence_reason', 'leave_request_id', 'is_paid_absence', 'hours_deducted', 'is_approved', 'needs_review', 'notes', 'minutes_late', 'minutes_early_departure', 'missed_break_minutes', 'sessions', 'approved_by', 'approved_at', 'last_calculated_at', 'calculation_method', 'attendance_policy_id', 'work_pattern_id', 'is_unplanned', 'calculation_metadata', 'calculation_version'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'date' => 'date',
        'net_hours' => 'decimal:2',
        'regular_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'double_time_hours' => 'decimal:2',
        'is_paid_absence' => 'boolean',
        'hours_deducted' => 'decimal:2',
        'is_approved' => 'boolean',
        'needs_review' => 'boolean',
        'minutes_late' => 'integer',
        'minutes_early_departure' => 'integer',
        'missed_break_minutes' => 'integer',
        'sessions' => 'array',
        'approved_at' => 'datetime',
        'last_calculated_at' => 'datetime',
        'is_unplanned' => 'boolean',
        'calculation_metadata' => 'array'
    ];

    protected $attributes = [
        'status' => 'present',
        'regular_hours' => 0,
        'overtime_hours' => 0,
        'double_time_hours' => 0,
        'is_paid_absence' => true,
        'hours_deducted' => 0,
        'is_approved' => false,
        'needs_review' => true,
        'minutes_late' => 0,
        'minutes_early_departure' => 0,
        'missed_break_minutes' => 0,
        'calculation_method' => 'auto',
        'is_unplanned' => false
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

    static::saving(function ($model) {
        /*
        Note: This assumes unpaid break deductions are already applied to the breakdown fields.
        In AttendanceCalculator, the breakdown fields are computed on gross hours,
        and net_hours is reduced by unpaid breaks. So if you want the model to also subtract
        unpaid breaks, you'd need to store unpaid_break_minutes in the attendance record
        (not currently there).
        For now, this approach keeps the calculator's logic intact and only enforces
         consistency for manual entries.
        */

        // If this record is being saved via the attendance calculator
        // (calculation_method = 'auto'), skip auto‑calculation — the
        // calculator already set net_hours correctly.
        if ($model->calculation_method === 'auto') {
            return;
        }

        // Recalculate net_hours from the breakdown fields
        $model->net_hours = round(
            ($model->regular_hours ?? 0) +
            ($model->overtime_hours ?? 0) +
            ($model->double_time_hours ?? 0),
            2
        );
    });
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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function attendanceSessions()
    {
        return $this->hasMany(\App\Modules\Attendance\Models\AttendanceSession::class, 'attendance_id', 'id');
    }

    public function adjustments()
    {
        return $this->hasMany(\App\Modules\Attendance\Models\AttendanceAdjustment::class, 'attendance_id', 'id');
    }

    public function leaveRequest()
    {
        return $this->belongsTo(\App\Modules\Leave\Models\LeaveRequest::class, 'leave_request_id', 'id');
    }

    public function shift()
    {
        return $this->belongsTo(\App\Modules\Attendance\Models\Shift::class, 'shift_id', 'id');
    }

    public function attendancePolicy()
    {
        return $this->belongsTo(\App\Modules\Attendance\Models\AttendancePolicy::class, 'attendance_policy_id', 'id');
    }

    public function workPattern()
    {
        return $this->belongsTo(\App\Modules\Attendance\Models\WorkPattern::class, 'work_pattern_id', 'id');
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
        return \App\Modules\Attendance\Database\Factories\AttendanceFactory::new();
    }
}
