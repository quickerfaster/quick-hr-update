<?php

namespace App\Modules\Hr\Traits;

use App\Modules\Hr\Models\Attendance;
use App\Modules\Hr\Models\Employee;
use Carbon\Carbon;

trait HandlesAttendanceRecord
{
    /**
     * Get or create an Attendance record for the given employee and date.
     * Denormalized company/department snapshots are set only on creation,
     * never updated on subsequent calls.
     */
    protected function getOrCreateAttendanceRecord(Employee $employee, Carbon $date, $schedule = null, $policy = null): Attendance
    {
        $attendance = Attendance::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'date'        => $date->toDateString(),
            ],
            [
                'company_id'             => $employee->company_id,
                'department_id'          => $employee->employeePosition?->department_id,
                'company'                => $employee->company?->name,
                'department'             => $employee->employeePosition?->department?->name,
                'shift_id'               => $schedule['shift']->id ?? null,
                'attendance_policy_id'   => $policy?->id,
                'status'                 => 'pending',
                'is_approved'            => false,
                'net_hours'              => 0.00,
            ]
        );

        return $attendance;
    }
}
