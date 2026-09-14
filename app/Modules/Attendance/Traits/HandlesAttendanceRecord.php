<?php

namespace App\Modules\Attendance\Traits;

use App\Modules\Attendance\Models\Attendance;
use App\Modules\Hr\Models\Employee;
use Carbon\Carbon;

trait HandlesAttendanceRecord
{
    /**
     * Get or create an Attendance record for the given employee and date.
     *
     * Uses whereDate() for the lookup to avoid SQLite string-mismatch
     * issues where the stored date includes a time component.
     *
     * Checks withTrashed() to reuse soft-deleted records instead of
     * hitting the UNIQUE constraint on (employee_id, date) which does
     * not exclude soft-deleted rows in SQLite.
     *
     * Denormalized company/department snapshots are set only on creation,
     * never updated on subsequent calls.
     */
    protected function getOrCreateAttendanceRecord(Employee $employee, Carbon $date, $schedule = null, $policy = null): Attendance
    {
        $dateString = $date->toDateString();

        // Check including soft-deleted records — if one exists, restore it
        $attendance = Attendance::withTrashed()
            ->where('employee_id', $employee->id)
            ->whereDate('date', $dateString)
            ->first();

        if ($attendance) {
            if ($attendance->trashed()) {
                $attendance->restore();
            }

            return $attendance;
        }

        return Attendance::create([
            'employee_id'           => $employee->id,
            'date'                  => $dateString,
            'company_id'            => $employee->company_id,
            'department_id'         => $employee->employeePosition?->department_id,
            'company'               => $employee->company?->name,
            'department'            => $employee->employeePosition?->department?->name,
            'shift_id'              => $schedule['shift']->id ?? null,
            'attendance_policy_id'  => $policy?->id,
            'status'                => 'pending',
            'is_approved'           => false,
            'net_hours'             => 0.00,
        ]);
    }
}
