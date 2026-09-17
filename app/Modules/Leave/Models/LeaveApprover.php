<?php

namespace App\Modules\Leave\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Hr\Models\Employee;

class LeaveApprover extends Model
{
    use SoftDeletes;

    protected $table = 'leave_approvers';

    protected $fillable = [
        'company_id',
        'employee_id',
        'approver_id',
        'approval_level',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'approval_level' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }
}
