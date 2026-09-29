<?php

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\Models\{
    ClockEvent, Attendance, AttendanceSession, AttendancePolicy,
    WorkPattern, Shift, ShiftSchedule
};
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\EmployeePosition;
use App\Modules\Holiday\Models\Holiday;
use App\Modules\Leave\Models\LeaveRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Modules\Attendance\Traits\HandlesAttendanceRecord;


class AttendanceAggregator
{
    use HandlesAttendanceRecord;
    protected AttendanceCalculator $calculator;

    public function __construct(AttendanceCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Recalculate attendance for a specific employee and day
     * Now uses AttendanceCalculator for normal days, handles special cases separately
     */
    public function recalculateForDay(string $employeeNumber, string $date): void
    {


        DB::transaction(function () use ($employeeNumber, $date) {
            $dateObj = Carbon::parse($date);
            $dateOnly = $dateObj->toDateString();

            // Get employee
            $employee = Employee::withoutCompanyScope()
                ->with(['employeePosition' => function ($q) {
                    $q->withoutGlobalScope(\QuickerFaster\UILibrary\Scopes\CompanyScope::class);
                }])
                ->where('employee_number', $employeeNumber)
                ->first();
            if (!$employee) {
                Log::error("Employee not found: {$employeeNumber}");
                return;
            }

            // Check for holiday
            $isHoliday = $this->isCompanyHoliday($dateOnly);

            // Check for approved leave (use integer employee_id, not string employee_number)
            $hasApprovedLeave = LeaveRequest::where('employee_id', $employee->id)
                ->where('status', 'Approved')
                ->whereDate('start_date', '<=', $dateOnly)
                ->whereDate('end_date', '>=', $dateOnly)
                ->exists();

            // SPECIAL CASE 1: Holiday
            if ($isHoliday) {
                $this->handleHolidayAttendance($employee, $dateOnly);
                return;
            }

            // SPECIAL CASE 2: Approved Leave
            if ($hasApprovedLeave) {
                $this->handleLeaveAttendance($employee, $dateOnly);
                return;
            }

            // SPECIAL CASE 3: No clock events (unplanned absence)
            $hasClockEvents = ClockEvent::where('employee_id', $employee->id)
                ->whereDate('timestamp', $dateOnly)
                ->exists();

            if (!$hasClockEvents) {
                $this->handleUnplannedAbsence($employee, $dateOnly);
                return;
            }

            // NORMAL CASE: Use AttendanceCalculator for full processing
            try {
                $result = $this->calculator->calculateForDay($employeeNumber, $dateObj);

                Log::info("Attendance calculated successfully via calculator", [
                    'employee' => $employeeNumber,
                    'date' => $dateOnly,
                    'attendance_id' => $result['attendance_id'],
                    'sessions' => $result['sessions_created']
                ]);
            } catch (\Exception $e) {
                Log::error("Calculator failed", [
                    'employee' => $employeeNumber,
                    'date' => $dateOnly,
                    'error' => $e->getMessage()
                ]);

                throw $e;
            }
        });
    }

    /**
     * Handle company holiday attendance
     */
    private function handleHolidayAttendance(Employee $employee, string $date): void
    {
        $holiday = Holiday::whereDate('date', $date)->first();

        $attendance = $this->getOrCreateAttendanceRecord($employee, Carbon::parse($date));

        // Delete any existing sessions (shouldn't exist, but clean up)
        AttendanceSession::where('attendance_id', $attendance->id)->forceDelete();

        // Determine holiday pay from Holiday model fields
        $isPaidHoliday = $holiday->is_paid_holiday ?? true;
        $affectsPayroll = $holiday->affects_payroll ?? true;
        $minimumHours = (float) ($holiday->minimum_hours_for_pay ?? 8);
        $isHalfDay = $holiday->is_half_day ?? false;

        $creditedHours = 0.00;
        if ($isPaidHoliday && $affectsPayroll) {
            $creditedHours = $isHalfDay ? ($minimumHours / 2) : $minimumHours;
        }

        $attendance->update([
            'status' => 'holiday',
            'net_hours' => $creditedHours,
            'regular_hours' => $creditedHours,
            'overtime_hours' => 0.00,
            'double_time_hours' => 0.00,
            'is_approved' => true,
            'is_paid_absence' => $isPaidHoliday,
            'notes' => $holiday ? "Company Holiday: {$holiday->name}" : "Company Holiday",
            'needs_review' => false,
            'is_unplanned' => false,
            'absence_type' => null,
            'calculation_method' => 'auto',
            'sessions' => null,
        ]);

        Log::info("Marked attendance as holiday", [
            'employee_id' => $employee->employee_number,
            'date' => $date,
            'attendance_id' => $attendance->id
        ]);
    }

    /**
     * Handle approved leave attendance
     */
    private function handleLeaveAttendance(Employee $employee, string $date): void
    {
        $leaveRequest = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'Approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->first();

        if (!$leaveRequest) {
            return;
        }

        $attendance = $this->getOrCreateAttendanceRecord($employee, Carbon::parse($date));

        AttendanceSession::where('attendance_id', $attendance->id)->delete();

        // Get standard hours from employee's shift if available
        $standardHours = 8.00;
        if ($employee->position && $employee->position->shift) {
            $standardHours = $employee->position->shift->duration_hours ?? 8.00;
        }

        // Handle half-day leave
        if ($leaveRequest->is_half_day) {
            $standardHours = $standardHours / 2;
        }

        $isPaid = $leaveRequest->leaveType->is_paid ?? true;

        $attendance->update([
            'status' => 'leave',
            'leave_request_id' => $leaveRequest->id,
            'net_hours' => $isPaid ? $standardHours : 0.00,
            'regular_hours' => $isPaid ? $standardHours : 0.00,
            'overtime_hours' => 0.00,
            'double_time_hours' => 0.00,
            'is_approved' => true,
            'notes' => "On Leave: " . ($leaveRequest->leaveType->name ?? 'Approved Leave'),
            'needs_review' => false,
            'is_unplanned' => false,
            'absence_type' => 'planned_leave',
            'hours_deducted' => ($leaveRequest->leaveType->deducts_from_balance ?? true) ? $standardHours : 0,
            'is_paid_absence' => $isPaid,
            'calculation_method' => 'auto',
            'sessions' => null,
        ]);

        Log::info("Marked attendance as leave", [
            'employee_id' => $employee->employee_number,
            'date' => $date,
            'leave_request_id' => $leaveRequest->id,
            'attendance_id' => $attendance->id
        ]);
    }

    /**
     * Handle unplanned absence (no clock events, no approved leave, not holiday)
     */
    private function handleUnplannedAbsence(Employee $employee, string $date): void
    {
        $attendance = $this->getOrCreateAttendanceRecord($employee, Carbon::parse($date));

        AttendanceSession::where('attendance_id', $attendance->id)->delete();

        // Get standard hours for deduction
        AttendanceSession::where('attendance_id', $attendance->id)->delete();

        // Get standard hours for deduction
        $standardHours = 8.00;
        if ($employee->position && $employee->position->shift) {
            $standardHours = $employee->position->shift->duration_hours ?? 8.00;
        }

        $attendance->update([
            'status' => 'absent',
            'net_hours' => 0.00,
            'regular_hours' => 0.00,
            'overtime_hours' => 0.00,
            'is_approved' => false,
            'notes' => 'No show - unplanned absence',
            'needs_review' => true,
            'is_unplanned' => true,
            'absence_type' => 'unplanned_absent',
            'hours_deducted' => $standardHours,
            'is_paid_absence' => false,
            'calculation_method' => 'auto',
            'sessions' => null,
        ]);

        Log::warning("Detected unplanned absence", [
            'employee_id' => $employee->employee_number,
            'date' => $date,
            'attendance_id' => $attendance->id
        ]);
    }

    /**
     * Fallback to original processing logic (kept for backward compatibility)
     */
    private function fallbackCalculation(Employee $employee, string $date): void
    {
        $attendance = $this->getOrCreateAttendanceRecord($employee, Carbon::parse($date));

        $events = ClockEvent::where('employee_id', $employee->id)
            ->whereDate('timestamp', $date)
            ->orderBy('timestamp')
            ->get();
    }







    /**
     * Check if date is a company holiday
     */
    private function isCompanyHoliday(string $date): bool
    {
        // Your existing implementation
        $dateObj = Carbon::parse($date);

        $exactMatch = Holiday::whereDate('date', $date)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('business_impact', 'office_closed')
                    ->orWhere('business_impact', 'reduced_staff');
            })
            ->exists();

        if ($exactMatch) {
            return true;
        }

        $observedMatch = Holiday::whereDate('observed_date', $date)
            ->where('is_active', true)
            ->whereNotNull('observed_date')
            ->where(function ($query) {
                $query->where('business_impact', 'office_closed')
                    ->orWhere('business_impact', 'reduced_staff');
            })
            ->exists();

        return $observedMatch;
    }

    /**
     * Batch recalculate for date range
     */
    public function recalculateDateRange(string $employeeNumber, string $startDate, string $endDate): void
    {
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $this->recalculateForDay($employeeNumber, $date->format('Y-m-d'));
        }

        Log::info("Recalculated attendance range", [
            'employee_id' => $employeeNumber,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_processed' => $period->count()
        ]);
    }
}
