<?php

namespace App\Modules\Hr\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\JobTitle;
use App\Modules\Hr\Models\Department;
use App\Modules\Hr\Models\Location;
use App\Modules\Attendance\Models\Shift;
use App\Modules\Attendance\Models\EmployeeWorkPattern;
use App\Modules\Attendance\Models\AttendancePolicy;

use Illuminate\Database\Eloquent\Model;


class EmployeePosition extends Model
{
    use HasCompanyScope;
    use HasFactory;
    use SoftDeletes;





    protected $table = 'employee_positions';






    protected $fillable = [
        'company_id',
        'employee_id',
        'job_title_id',
        'department_id',
        'manager_id',
        'pay_type',
        'hourly_rate',
        'base_salary',
        'salary_currency',
        'pay_frequency',
        'employment_status',
        'location_id',
        'shift_id',
        'attendance_policy_id',
        'cost_center',
        'work_email',
        'work_phone_extension',
        'reports_to',
        'job_description'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'base_salary' => 'decimal:2'
    ];

    protected $attributes = [
        'hourly_rate' => 0,
        'base_salary' => 0,
        'salary_currency' => 'USD',
        'employment_status' => 'Active'
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

    public function jobTitle()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\JobTitle::class, 'job_title_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Department::class, 'department_id', 'id');
    }

    public function manager()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Employee::class, 'manager_id', 'id');
    }

    public function reportsTo()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Employee::class, 'reports_to', 'id');
    }

    public function location()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Location::class, 'location_id', 'id');
    }

    public function shift()
    {
        return $this->belongsTo(\App\Modules\Attendance\Models\Shift::class, 'shift_id', 'id');
    }

    public function employeeWorkPatterns()
    {
        return $this->hasMany(\App\Modules\Attendance\Models\EmployeeWorkPattern::class, 'employee_id', 'id');
    }

    public function attendancePolicy()
    {
        return $this->belongsTo(\App\Modules\Attendance\Models\AttendancePolicy::class, 'attendance_policy_id', 'id');
    }



    /**
     * Get the pay schedule through the employee's payroll profile.
     * The direct pay_schedule_id column was dropped — the canonical link
     * is now via employee_payroll_profiles.
     */
    public function paySchedule()
    {
        return $this->hasOneThrough(
            \App\Modules\Payroll\Models\PaySchedule::class,
            \App\Modules\Payroll\Models\EmployeePayrollProfile::class,
            'employee_id',     // Foreign key on employee_payroll_profiles
            'id',              // Foreign key on pay_schedules
            'employee_id',     // Local key on employee_positions
            'pay_schedule_id'  // Local key on employee_payroll_profiles
        );
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
        return \App\Modules\Hr\Database\Factories\EmployeePositionFactory::new();
    }










    // Manually added
    /**
     * Fields that should trigger a history entry when changed.
     */
    protected $historyTrackedFields = [
        'job_title_id',
        'department_id',
        'manager_id',
        'pay_type',
        'hourly_rate',
        'base_salary',
        'salary_currency',
        'pay_frequency',
        'employment_status',
        'location_id',
        'shift_id',
        'attendance_policy_id',
        'cost_center',
        'work_email',
        'work_phone_extension',
        'reports_to',
        'job_description',
    ];










    /**
     * The "booted" method is called after the model is initialised.
     * No service provider registration needed.
     */
    protected static function booted()
    {
        // Auto-set company_id from the associated employee to prevent
        // cross-company record mismatches when admins create records
        // in "All Companies" mode.
        static::saving(function (self $position) {
            // Auto-set company_id from the associated employee
            if ($position->employee_id) {
                $employee = \App\Modules\Hr\Models\Employee::withoutCompanyScope()
                    ->find($position->employee_id);

                if ($employee && $employee->company_id) {
                    $position->company_id = $employee->company_id;
                }
            }
        });

        static::updating(function (self $position) {
            $original = $position->getOriginal();
            $changes = [];

            // Check each tracked field for changes
            foreach ($position->historyTrackedFields as $field) {
                if ($position->isDirty($field)) {
                    $changes[$field] = [
                        'old' => data_get($original, $field),
                        'new' => $position->$field,
                    ];
                }
            }

            if (empty($changes)) {
                return;
            }

            DB::transaction(function () use ($position, $changes) {
                // Close the previous active history entry by setting its end_date
                EmployeeJobHistory::where('employee_id', $position->employee_id)
                    ->whereNull('end_date')
                    ->orderBy('effective_date', 'desc')
                    ->first()
                    ?->update(['end_date' => now()->toDateString()]);

                // Build a snapshot of the *new* state (what the employee moves to)
                $historyData = self::buildHistorySnapshot($position, [
                    'effective_date' => now()->toDateString(),
                    'change_reason' => self::determineReason($changes),
                    'notes' => self::buildDescription($changes),
                    'changed_by_user_id' => \Auth::id(),
                ]);

                EmployeeJobHistory::create($historyData);
            });

            // NEW: Sync company_id on Employee when department changes
            if ($position->isDirty('department_id')) {
                $department = $position->department; // automatically loaded via relation
                $companyId = $department?->company_id;
                if ($position->employee) {
                    $position->employee->updateQuietly(['company_id' => $companyId]);

                    // Recompute onboarding_status after department/company change
                    $status = self::computeOnboardingStatus($position);
                    \DB::table('employees')->where('id', $position->employee_id)->update([
                        'onboarding_status' => $status,
                    ]);
                }
            }
        });

        /**
         * When a new position is created, record it as the initial job history entry.
         */
        static::created(function (self $position) {
            $historyData = self::buildHistorySnapshot($position, [
                'effective_date' => now()->toDateString(),
                'end_date' => null,
                'change_reason' => 'New Hire / Initial Position',
                'notes' => 'Initial position assignment as ' . (optional($position->jobTitle)->title ?? 'Unknown')
                    . ' in ' . (optional($position->department)->name ?? 'Unknown'),
                'changed_by_user_id' => \Auth::id(),
                'pay_frequency' => $position->pay_frequency ?? 'monthly',
            ]);

            EmployeeJobHistory::create($historyData);

            // Sync company_id on Employee from the department
            $department = $position->department;
            $companyId = $department?->company_id;
            if ($position->employee) {
                $position->employee->updateQuietly(['company_id' => $companyId]);

                // Recompute onboarding_status now that position exists
                $status = self::computeOnboardingStatus($position);
                \DB::table('employees')->where('id', $position->employee_id)->update([
                    'onboarding_status' => $status,
                ]);
            }
        });

        /**
         * When a position is deleted (soft-deleted), record a final termination history entry.
         */
        static::deleted(function (self $position) {
            DB::transaction(function () use ($position) {
                // Close the previous active history entry
                EmployeeJobHistory::where('employee_id', $position->employee_id)
                    ->whereNull('end_date')
                    ->orderBy('effective_date', 'desc')
                    ->first()
                    ?->update(['end_date' => now()->toDateString()]);

                $historyData = self::buildHistorySnapshot($position, [
                    'effective_date' => now()->toDateString(),
                    'end_date' => now()->toDateString(),
                    'change_reason' => 'Termination / Position Ended',
                    'notes' => 'Position ' . (optional($position->jobTitle)->title ?? 'Unknown')
                        . ' in ' . (optional($position->department)->name ?? 'Unknown') . ' ended',
                    'changed_by_user_id' => \Auth::id(),
                ]);

                EmployeeJobHistory::create($historyData);
            });
        });

        /**
         * After any save (create or update), always recompute onboarding_status.
         */
        static::saved(function (self $position) {
            if (!$position->employee) {
                return;
            }

            $status = self::computeOnboardingStatus($position);

            \DB::table('employees')->where('id', $position->employee_id)->update([
                'onboarding_status' => $status,
            ]);
        });
    }

    /**
     * Compute the onboarding_status for an employee based on their position.
     *
     * States:
     *   - 'company_pending'  — no company assigned to the employee
     *   - 'position_pending' — company assigned but position is bare (no job title or department)
     *   - 'complete'         — company assigned AND position has job details
     */
    protected static function computeOnboardingStatus(self $position): string
    {
        $hasCompany = (bool) ($position->employee->company_id
            ?: $position->department?->company_id);

        if (! $hasCompany) {
            return 'company_pending';
        }

        $hasJobDetails = (bool) ($position->job_title_id || $position->department_id);

        return $hasJobDetails ? 'complete' : 'position_pending';
    }

    /**
     * Build a snapshot data array for an EmployeeJobHistory record
     * from the current state of the given position model.
     *
     * @param  self   $position
     * @param  array  $additional  Extra key/value pairs to merge (e.g. effective_date, change_reason, notes)
     * @return array
     */
    protected static function buildHistorySnapshot(self $position, array $additional = []): array
    {
        return array_merge([
            'company_id'       => $position->company_id,
            'employee_id'      => $position->employee_id,
            'job_title'        => optional($position->jobTitle)->title,
            'department'       => optional($position->department)->name,
            'manager_name'     => optional($position->manager)->full_name,
            'pay_type'         => $position->pay_type,
            'hourly_rate'      => $position->hourly_rate,
            'base_salary'      => $position->base_salary,
            'salary_currency'  => $position->salary_currency,
            'pay_frequency'    => $position->pay_frequency,
            'employment_status'=> $position->employment_status,
            'location'         => optional($position->location)->name,
            'shift'            => optional($position->shift)->name,
        ], $additional);
    }

    /**
     * Guess the reason for change based on which fields changed.
     */
    protected static function determineReason(array $changes): string
    {
        if (isset($changes['job_title_id'])) {
            return 'Promotion / Role Change';
        }
        if (isset($changes['department_id'])) {
            return 'Department Transfer';
        }
        if (isset($changes['base_salary']) || isset($changes['hourly_rate'])) {
            return 'Salary Adjustment';
        }
        if (isset($changes['employment_status']) && $changes['employment_status']['new'] === 'Terminated') {
            return 'Termination';
        }
        if (isset($changes['employment_status']) && $changes['employment_status']['new'] === 'On Leave') {
            return 'Leave of Absence';
        }
        return 'Other Change';
    }

    /**
     * Build a human‑readable description of what changed.
     */
    protected static function buildDescription(array $changes): string
    {
        $descriptions = [];
        foreach ($changes as $field => $values) {
            $old = $values['old'];
            $new = $values['new'];

            // Convert IDs to names where possible
            if ($field === 'job_title_id') {
                $old = optional(\App\Modules\Hr\Models\JobTitle::find($old))->title ?? $old;
                $new = optional(\App\Modules\Hr\Models\JobTitle::find($new))->title ?? $new;
                $field = 'Job Title';
            } elseif ($field === 'department_id') {
                $old = optional(\App\Modules\Hr\Models\Department::find($old))->name ?? $old;
                $new = optional(\App\Modules\Hr\Models\Department::find($new))->name ?? $new;
                $field = 'Department';
            } elseif ($field === 'manager_id') {
                $old = optional(\App\Modules\Hr\Models\Employee::find($old))->full_name ?? $old;
                $new = optional(\App\Modules\Hr\Models\Employee::find($new))->full_name ?? $new;
                $field = 'Manager';
            } else {
                $field = str_replace('_', ' ', ucfirst($field));
            }

            $descriptions[] = "{$field}: from '{$old}' to '{$new}'";
        }
        return implode('; ', $descriptions);
    }











}
