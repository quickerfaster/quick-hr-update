<?php

namespace App\Modules\Leave\Models;

use QuickerFaster\UILibrary\Traits\HasCompanyScope;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Modules\Hr\Models\Employee;
use App\Modules\Leave\Models\LeaveType;
use App\Modules\Attendance\Models\Attendance;
use QuickerFaster\UILibrary\Contracts\Workflow\Workflowable;
use QuickerFaster\UILibrary\Contracts\Documents\Documentable;
use QuickerFaster\UILibrary\Traits\Workflows\HasWorkflow;
use Illuminate\Database\Eloquent\Model;


class LeaveRequest extends Model implements Workflowable, Documentable
{
    use HasCompanyScope;
    use HasFactory;
    use HasWorkflow;
    use SoftDeletes;




    protected $table = 'leave_requests';



    public $timestamps = true;


    protected $fillable = [
        'company_id', 'employee_id', 'leave_type_id', 'start_date', 'end_date', 'is_half_day', 'half_day_period', 'reason', 'status', 'approved_by', 'approved_at', 'denial_reason', 'attendance_synced', 'attendance_records_count', 'last_sync_at', 'is_retroactive', 'reported_after_absence', 'workdays_count', 'overlap_with_holiday'
    ];

    protected $guarded = [

    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_half_day' => 'boolean',
        'approved_at' => 'datetime',
        'attendance_synced' => 'boolean',
        'attendance_records_count' => 'integer',
        'last_sync_at' => 'datetime',
        'is_retroactive' => 'boolean',
        'reported_after_absence' => 'boolean',
        'workdays_count' => 'integer',
        'overlap_with_holiday' => 'boolean'
    ];

    protected $attributes = [
        'status' => 'Pending',
        'attendance_synced' => false,
        'attendance_records_count' => 0,
        'is_retroactive' => false,
        'reported_after_absence' => false,
        'overlap_with_holiday' => false,
        'is_half_day' => false
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
        return $this->belongsTo(\App\Modules\Leave\Models\LeaveType::class, 'leave_type_id', 'id');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Employee::class, 'approved_by', 'id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(\App\Modules\Attendance\Models\Attendance::class, 'leave_request_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Modules\Hr\Models\Company::class, 'company_id', 'id');
    }

    /**
     * The workflow definition key for this model.
     */
    public function getWorkflowDefinitionKey(): string
    {
        return 'leave_request';
    }

    /**
     * Additional context used for workflow routing decisions.
     */
    public function getWorkflowContext(): array
    {
        return [
            'workspace_id' => $this->company_id,
            'employee_id' => $this->employee_id,
            'leave_type_id' => $this->leave_type_id,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
        ];
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \App\Modules\Leave\Database\Factories\LeaveRequestFactory::new();
    }

    /**
     * Polymorphic relationship to uploaded documents/attachments.
     */
    public function documents()
    {
        return $this->morphMany(\QuickerFaster\UILibrary\Models\Document::class, 'documentable');
    }

    /**
     * Get the unique identifier for this documentable entity.
     */
    public function getDocumentableId(): int|string
    {
        return $this->id;
    }

    /**
     * Get the document type key for this entity.
     */
    public function getDocumentType(): string
    {
        return 'leave_request';
    }

    /**
     * Get the storage folder path for uploaded documents.
     */
    public function getDocumentStoragePath(): string
    {
        return 'documents/leave_requests/' . $this->id;
    }

    /**
     * Get template data for document generation.
     */
    public function getDocumentTemplateData(): array
    {
        return [
            'leave_request_id' => $this->id,
            'employee_name' => $this->employee?->full_name ?? '',
            'leave_type' => $this->leaveType?->name ?? '',
            'start_date' => $this->start_date?->toDateString() ?? '',
            'end_date' => $this->end_date?->toDateString() ?? '',
            'status' => $this->status,
        ];
    }

    /**
     * Get the effective status, considering the active workflow.
     *
     * When the leave request is under approval, returns the workflow status
     * (pending/approved/rejected/cancelled). Otherwise returns the model's
     * own status field (Pending/Approved/Denied/Cancelled).
     *
     * Note: This may return lowercase workflow statuses or PascalCase model
     * statuses depending on whether a workflow is active. For display purposes,
     * consider normalizing with ucfirst().
     */
    public function effectiveStatus(): string
    {
        if ($this->activeWorkflow) {
            return $this->activeWorkflow->status;
        }

        return $this->status;
    }
}
