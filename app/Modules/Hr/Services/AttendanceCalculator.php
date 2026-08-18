<?php

namespace App\Modules\Hr\Services;

use App\Modules\Hr\Models\{
    ClockEvent,
    Attendance,
    AttendancePolicy,
    WorkPattern,
    ShiftSchedule,
    Employee,
    EmployeePosition,
    AttendanceSession,
    PolicyAssignment
};
use App\Modules\Hr\Models\Shift;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Modules\Hr\Traits\HandlesAttendanceRecord;

class AttendanceCalculator
{
    use HandlesAttendanceRecord;

    /**
     * Calculate attendance for a specific employee and date
     * Creates/updates attendance record AND attendance sessions
     */
    public function calculateForDay(string $employeeNumber, Carbon $date): array
    {
        return DB::transaction(function () use ($employeeNumber, $date) {
            // 1. Get employee with all required relations
            $employee = Employee::with(['employeePosition.department.company'])
                ->where('employee_number', $employeeNumber)
                ->first();

            if (!$employee || !$employee->employeePosition) {
                throw new \Exception("Employee or Position not found: {$employeeNumber}");
            }

            $position = $employee->employeePosition;

            // 2. Schedule & Policy logic (with company filtering)
            $pattern = $this->getApplicableWorkPattern($employee, $position, $date);
            $schedule = $this->getExpectedSchedule($employee, $position, $pattern, $date);
            $shift = $schedule['shift'] ?? null;
            $policy = $this->getApplicablePolicy($employee, $position, $date, $shift);

            // 3. Get clock events using integer employee_id
            $events = ClockEvent::where('employee_id', $employee->id)
                ->whereDate('timestamp', $date)
                ->orderBy('timestamp')
                ->get();

            // 4. Process events
            $sessionData = $this->processClockEvents($events);
            $sessions = $sessionData['sessions'];
            $totalHours = $sessionData['total_hours'];
            $firstClockIn = $sessionData['first_clock_in'];
            $lastClockOut = $sessionData['last_clock_out'];

            // 5. Get or create attendance record
            $attendance = $this->getOrCreateAttendanceRecord($employee, $date, $schedule, $policy);

            // 6. DELETE existing sessions for this attendance (fresh calculation)
            AttendanceSession::where('attendance_id', $attendance->id)->forceDelete();

            // 7. CREATE new sessions from processed events
            foreach ($sessions as $session) {
                AttendanceSession::create([
                    'attendance_id' => $attendance->id,
                    'clock_in_event_id' => $session['clock_in_event_id'] ?? null,
                    'clock_out_event_id' => $session['clock_out_event_id'] ?? null,
                    'start_time' => $session['start'],
                    'end_time' => $session['end'],
                    'duration_hours' => $session['duration'],
                    'session_type' => 'work',
                    'is_overnight' => $session['is_overnight'] ?? false,
                    'notes' => $session['notes'] ?? null,
                ]);
            }

            // 8. Calculate attendance metrics using policy
            $calculation = $this->calculateAttendanceMetrics(
                actualWorkedHours: $totalHours,
                firstClockIn: $firstClockIn,
                lastClockOut: $lastClockOut,
                schedule: $schedule,
                policy: $policy,
                employee: $employee,
                date: $date,
                sessions: $sessions
            );

            // Create unpaid break session if policy requires it
            if ($policy && $policy->unpaid_break_minutes > 0 && $totalHours > 0 && $attendance->id) {
                $unpaidSession = [
                    'start' => null,
                    'end' => null,
                    'duration' => round($policy->unpaid_break_minutes / 60, 2),
                    'is_overnight' => false,
                    'notes' => 'Policy-mandated unpaid break deduction of ' . $policy->unpaid_break_minutes . ' minutes',
                ];
                $sessions[] = $unpaidSession;

                AttendanceSession::create([
                    'company_id' => $employee->company_id,
                    'attendance_id' => $attendance->id,
                    'start_time' => null,
                    'end_time' => null,
                    'duration_hours' => $unpaidSession['duration'],
                    'session_type' => 'unpaid_break',
                    'is_adjusted' => true,
                    'adjustment_reason' => $unpaidSession['notes'],
                ]);
            }

            // Override status if day is not in work pattern
            $dateString = $date->toDateString();
            $dayOfWeek = $date->dayOfWeekIso;
            if ($pattern) {
                // Fix: parse applicable_days as array of integers
                $days = array_map('intval', explode(',', $pattern->applicable_days ?? ''));
                if (!in_array($dayOfWeek, $days, true)) {
                    $calculation['status'] = 'unscheduled';
                }
            }

            // 9. Update attendance record with calculation results
            $attendance->update([
                'status' => $calculation['status'],
                'shift_id' => $shift?->id,
                'net_hours' => $calculation['total_hours'],
                'regular_hours' => $calculation['regular_hours'],
                'overtime_hours' => $calculation['overtime_hours'],
                'double_time_hours' => $calculation['double_time_hours'],
                'minutes_late' => $calculation['minutes_late'],
                'minutes_early_departure' => $calculation['minutes_early_departure'],
                'missed_break_minutes' => $calculation['missed_break_minutes'],
                'needs_review' => $calculation['needs_review'],
                'attendance_policy_id' => $policy?->id,
                'work_pattern_id' => $pattern?->id,
                'calculation_metadata' => json_encode($calculation['breakdown']),
                'calculation_version' => '1.0',
                'calculation_method' => 'auto',
                'sessions' => array_map(function ($s) {
                    return [
                        'start' => $s['start'] ? $s['start']->format('H:i') : null,
                        'end' => $s['end'] ? $s['end']->format('H:i') : null,
                        'duration' => $s['duration']
                    ];
                }, $sessions),
            ]);

            return [
                'success' => true,
                'attendance_id' => $attendance->id,
                'calculation' => $calculation,
                'sessions_created' => count($sessions)
            ];
        });
    }

    // -------------------- Policy & Schedule Resolution (with company filtering) --------------------

    /**
     * Get applicable attendance policy with proper priority and company filtering.
     */
    public function getApplicablePolicy(Employee $employee, EmployeePosition $position, Carbon $date, ?Shift $shift = null): ?AttendancePolicy
    {
        // Priority 1: Employee-specific policy
        if ($position->attendance_policy_id) {
            $policyQuery = AttendancePolicy::where('id', $position->attendance_policy_id);
            if ($employee->company_id) {
                $policyQuery->where('company_id', $employee->company_id);
            }
            $policy = $policyQuery->first();
            if ($policy && $this->isPolicyActive($policy, $date)) {
                return $policy;
            }
        }

        // Priority 2: Shift-specific policy via PolicyAssignment
        if ($shift) {
            $policy = $this->getPolicyForEntity($employee, Shift::class, $shift->id, $date);
            if ($policy) return $policy;
        }

        // Priority 3: Department policy
        if ($position->department) {
            $policy = $this->getPolicyForEntity($employee, \App\Modules\Hr\Models\Department::class, $position->department->id, $date);
            if ($policy) return $policy;
        }

        // Priority 4: Location policy
        if ($position->location) {
            $policy = $this->getPolicyForEntity($employee, \App\Modules\Hr\Models\Location::class, $position->location->id, $date);
            if ($policy) return $policy;
        }

        // Priority 5: Company policy
        $policy = $this->getPolicyForEntity($employee, \App\Modules\Hr\Models\Company::class, $employee->company_id, $date);
        if ($policy) return $policy;

        // Priority 6: System-wide default policy (must belong to the same company)
        $query = AttendancePolicy::where('is_default', true)
            ->where('is_active', true)
            ->whereDate('effective_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('expiration_date')->orWhereDate('expiration_date', '>=', $date);
            });

        if ($employee->company_id) {
            $query->where('company_id', $employee->company_id);
        }

        return $query->first();
    }

    /**
     * Fetch a policy assigned to a specific entity with company filter.
     */
    protected function getPolicyForEntity(Employee $employee, string $modelClass, ?int $id, Carbon $date): ?AttendancePolicy
    {
        // Skip if no entity ID provided
        if ($id === null) {
            return null;
        }

        // Using cache for performance (optional but recommended)
        $cacheKey = "policy_for_{$modelClass}_{$id}_company_{$employee->company_id}";
        return \Cache::remember($cacheKey, 3600, function () use ($employee, $modelClass, $id, $date) {
            $query = PolicyAssignment::where('assignable_type', $modelClass)
                ->where('assignable_id', $id);

            // Only filter by company_id if the employee has one set
            if ($employee->company_id) {
                $query->where('company_id', $employee->company_id);
            }

            $assignment = $query->with('attendancePolicy')->first();

            if ($assignment && $this->isPolicyActive($assignment->attendancePolicy, $date)) {
                return $assignment->attendancePolicy;
            }

            return null;
        });
    }

    /**
     * Check if a policy is active on the given date.
     */
    protected function isPolicyActive(AttendancePolicy $policy, Carbon $date): bool
    {
        if (!$policy->is_active) return false;
        if ($policy->effective_date && $policy->effective_date > $date) return false;
        if ($policy->expiration_date && $policy->expiration_date < $date) return false;
        return true;
    }

    /**
     * Get the applicable work pattern with company filtering.
     */
    public function getApplicableWorkPattern(Employee $employee, EmployeePosition $position, Carbon $date): ?WorkPattern
    {
        // 1. Employee-specific active work pattern (via EmployeeWorkPattern)
        $employeeWorkPattern = \App\Modules\Hr\Models\EmployeeWorkPattern::where('employee_id', $employee->id)
            ->where('start_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $date);
            })
            ->with('workPattern')
            ->first();

        if ($employeeWorkPattern && $employeeWorkPattern->workPattern) {
            $pattern = $employeeWorkPattern->workPattern;
            // The EmployeeWorkPattern assignment is an explicit assignment; no additional
            // company_id check is needed -- the global CompanyScope handles multi-tenancy.
            if ($this->isWorkPatternActive($pattern, $date)) {
                return $pattern;
            }
        }

        // 2. System-wide default work pattern.
        // Bypass the global CompanyScope so the system default is always findable
        // regardless of the employee's company_id or session context.
        $baseQuery = WorkPattern::withoutGlobalScope(\QuickerFaster\UILibrary\Scopes\CompanyScope::class)
            ->where('is_default', true)
            ->where('is_active', true)
            ->whereDate('effective_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $date);
            });

        // First attempt: filter by employee's company_id if set
        if ($employee->company_id) {
            $pattern = (clone $baseQuery)->where('company_id', $employee->company_id)->first();
            if ($pattern) {
                return $pattern;
            }
        }

        // Second attempt: any system default, regardless of company
        return $baseQuery->first();
    }

    /**
     * Check if a work pattern is active on the given date.
     */
    protected function isWorkPatternActive(WorkPattern $pattern, Carbon $date): bool
    {
        if (!$pattern->is_active) return false;
        if ($pattern->effective_date && $pattern->effective_date > $date) return false;
        if ($pattern->end_date && $pattern->end_date < $date) return false;
        return true;
    }

    /**
     * Get the expected schedule for an employee on a given date with company filtering.
     */
    public function getExpectedSchedule(
        Employee $employee,
        EmployeePosition $position,
        ?WorkPattern $pattern,
        Carbon $date
    ): ?array {
        $dateString = $date->toDateString();
        $dayOfWeek = $date->dayOfWeekIso;

        // Priority 1: Specific ShiftSchedule for the date
        $shiftSchedule = ShiftSchedule::where('employee_id', $employee->id)
            ->whereDate('schedule_date', $dateString)
            ->where('is_published', true)
            ->first();

        if ($shiftSchedule && $shiftSchedule->shift) {
            return [
                'type' => 'specific_shift_schedule',
                'schedule' => $shiftSchedule,
                'start_time' => $shiftSchedule->start_time_override
                    ? Carbon::parse($shiftSchedule->start_time_override)
                    : Carbon::parse($shiftSchedule->shift->start_time),
                'end_time' => $shiftSchedule->end_time_override
                    ? Carbon::parse($shiftSchedule->end_time_override)
                    : Carbon::parse($shiftSchedule->shift->end_time),
                'shift' => $shiftSchedule->shift
            ];
        }

        // Priority 2: WorkPattern for the day of week
        if ($pattern) {
            // Fix: parse applicable_days as array of integers
            $days = array_map('intval', explode(',', $pattern->applicable_days ?? ''));
            if (in_array($dayOfWeek, $days, true)) {
                $shift = $pattern->shift;
                if ($shift) {
                    $startTimeString = $pattern->override_start_time ?: $shift->start_time;
                    $endTimeString = $pattern->override_end_time ?: $shift->end_time;

                    return [
                        'type' => 'work_pattern',
                        'pattern' => $pattern,
                        'shift' => $shift,
                        'start_time' => $date->copy()->setTimeFromTimeString($startTimeString),
                        'end_time' => $date->copy()->setTimeFromTimeString($endTimeString),
                        'is_overnight' => $shift->is_overnight
                    ];
                }
            }
        }

        // Priority 3: Employee's default shift from position
        if ($position->shift_id) {
            $shift = Shift::where('id', $position->shift_id)
                ->where('is_active', true)
                ->first();
            if ($shift) {
                return [
                    'type' => 'user_default_shift',
                    'shift' => $shift,
                    'start_time' => $date->copy()->setTimeFromTimeString($shift->start_time),
                    'end_time' => $date->copy()->setTimeFromTimeString($shift->end_time),
                    'is_overnight' => $shift->is_overnight
                ];
            }
        }

        // Priority 4: System-wide default shift
        $defaultShift = Shift::withoutGlobalScope(\QuickerFaster\UILibrary\Scopes\CompanyScope::class)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();

        if ($defaultShift) {
            return [
                'type' => 'system_default_shift',
                'shift' => $defaultShift,
                'start_time' => $date->copy()->setTimeFromTimeString($defaultShift->start_time),
                'end_time' => $date->copy()->setTimeFromTimeString($defaultShift->end_time),
                'is_overnight' => $defaultShift->is_overnight
            ];
        }

        return null;
    }

    // -------------------- Clock Event Processing --------------------

    /**
     * Process raw clock events into work sessions.
     */
    protected function processClockEvents($events): array
    {
        $sessions = [];
        $totalHours = 0.0;
        $firstClockIn = null;
        $lastClockOut = null;

        $inSession = false;
        $sessionStart = null;
        $sessionStartEvent = null;

        foreach ($events as $event) {
            if ($event->event_type === 'clock_in' && !$inSession) {
                $inSession = true;
                $sessionStart = $event->timestamp;
                $sessionStartEvent = $event;

                if (!$firstClockIn) {
                    $firstClockIn = $event->timestamp;
                }
            } elseif ($event->event_type === 'clock_out' && $inSession) {
                $sessionEnd = $event->timestamp;
                $duration = $sessionStart->diffInMinutes($sessionEnd) / 60.0;

                $sessions[] = [
                    'clock_in_event_id' => $sessionStartEvent->id,
                    'clock_out_event_id' => $event->id,
                    'start' => $sessionStart,
                    'end' => $sessionEnd,
                    'duration' => round($duration, 2),
                    'is_overnight' => $sessionStart->format('Y-m-d') !== $sessionEnd->format('Y-m-d'),
                    'notes' => null
                ];

                $totalHours += $duration;
                $lastClockOut = $event->timestamp;
                $inSession = false;
                $sessionStart = null;
                $sessionStartEvent = null;
            }
        }

        // Handle orphaned clock-in
        if ($inSession && $sessionStart && $sessionStartEvent) {
            $sessions[] = [
                'clock_in_event_id' => $sessionStartEvent->id,
                'clock_out_event_id' => null,
                'start' => $sessionStart,
                'end' => null,
                'duration' => 0.0,
                'is_overnight' => false,
                'notes' => 'Missing clock-out'
            ];
        }

        return [
            'sessions' => $sessions,
            'total_hours' => round($totalHours, 2),
            'first_clock_in' => $firstClockIn,
            'last_clock_out' => $lastClockOut
        ];
    }

    // -------------------- Calculation Helpers (unchanged logic, but with company-awareness) --------------------

    /**
     * Calculate attendance metrics based on policy
     */
    protected function calculateAttendanceMetrics(
        float $actualWorkedHours,        // Renamed from $totalHours for clarity
        ?Carbon $firstClockIn,
        ?Carbon $lastClockOut,
        ?array $schedule,
        ?AttendancePolicy $policy,
        Employee $employee,
        Carbon $date,
        array $sessions
    ): array {


        $shiftId = null;
        if ($schedule && $schedule['shift'])
            $shiftId = $schedule['shift']->id;

        $result = [
            'status' => 'absent',
            'shift_id' => $shiftId,
            'total_hours' => 0.0,               // This will be payable hours (after deduction)
            'actual_hours' => $actualWorkedHours, // Store actual for breakdown
            'regular_hours' => 0.0,
            'overtime_hours' => 0.0,
            'double_time_hours' => 0.0,
            'minutes_late' => 0,
            'minutes_early_departure' => 0,
            'missed_break_minutes' => 0,
            'violations' => [],
            'breakdown' => [],
            'needs_review' => false
        ];



        // Ensure we always have a valid policy
        if (!$policy) {
            $policy = AttendancePolicy::where('is_default', true)->first();
            if (!$policy) {
                // This should never happen in a properly initialized system
                throw new \RuntimeException('No default attendance policy found.');
            }
        }



        // If no schedule, mark as unscheduled
        if (!$schedule) {
            $result['status'] = 'unscheduled';
            $result['needs_review'] = true;
            return $result;
        }

        // Compute expected hours for this schedule
        $expectedHours = $this->getExpectedHours($schedule);
        $result['breakdown']['expected_hours'] = $expectedHours;

        // If no hours and it's a work day → absent
        if ($actualWorkedHours == 0) {
            $result['status'] = 'absent';
            $result['needs_review'] = true;
            return $result;
        }

        // Check lateness (based on first clock-in)
        if ($firstClockIn) {
            $latenessCheck = $this->checkLateness(
                $firstClockIn,
                $schedule['start_time'],
                $policy->grace_period_minutes,
                $date
            );

            if ($latenessCheck['is_late']) {
                $result['minutes_late'] = $latenessCheck['minutes_late'];
                $result['violations'][] = [
                    'type' => 'late_arrival',
                    'minutes' => $latenessCheck['minutes_late']
                ];
            }
        }

        // Check early departure (based on last clock-out)
        if ($lastClockOut) {
            $earlyDepartureCheck = $this->checkEarlyDeparture(
                $lastClockOut,
                $schedule['end_time'],
                $policy->early_departure_grace_minutes,
                $date
            );

            if ($earlyDepartureCheck['is_early']) {
                $result['minutes_early_departure'] = $earlyDepartureCheck['minutes_early'];
                $result['violations'][] = [
                    'type' => 'early_departure',
                    'minutes' => $earlyDepartureCheck['minutes_early']
                ];
            }
        }

        // Calculate overtime breakdown based on actual worked hours
        $overtimeCalculation = $this->calculateOvertime(
            totalHours: $actualWorkedHours,
            policy: $policy,
            date: $date,
            employeeId: $employee->id
        );

        $result['regular_hours'] = $overtimeCalculation['regular_hours'];
        $result['overtime_hours'] = $overtimeCalculation['overtime_hours'];
        $result['double_time_hours'] = $overtimeCalculation['double_time_hours'];
        $result['breakdown']['overtime_calculation'] = $overtimeCalculation['breakdown'];

        // Check break compliance (requires break after X hours)
        $breakAfterValues = $this->parseBreakRuleValue($policy->requires_break_after_hours);
        $breakDurationValues = $this->parseBreakRuleValue($policy->break_duration_minutes);
        if (!empty($breakAfterValues) && !empty($breakDurationValues)) {
            $breakCheck = $this->checkBreakCompliance(
                $sessions,
                $policy->requires_break_after_hours,
                $policy->break_duration_minutes
            );

            if (!$breakCheck['compliant']) {
                $result['missed_break_minutes'] = $breakCheck['total_missed_minutes'];
                foreach ($breakCheck['violations'] as $violation) {
                    $result['violations'][] = [
                        'type' => 'missed_break',
                        'minutes' => $violation['required_minutes'],
                        'after_hours' => $violation['after_hours'],
                    ];
                }
            }
        }

        // --- Determine payable hours (after unpaid break deduction) ---
        $payableHours = $actualWorkedHours;
        if ($policy->unpaid_break_minutes > 0 && $actualWorkedHours > 0) {
            $deductionHours = $policy->unpaid_break_minutes / 60;
            $payableHours = max(0, $actualWorkedHours - $deductionHours);
            $result['breakdown']['unpaid_break_deducted'] = $policy->unpaid_break_minutes;
        }
        $result['total_hours'] = round($payableHours, 2);

        // --- Determine final status based on actual worked hours (not payable) ---
        $hasViolations = !empty($result['violations']);
        $result['status'] = $this->determineStatus(
            $actualWorkedHours,               // Use actual hours for status
            $result['minutes_late'],
            $result['minutes_early_departure'],
            $expectedHours,
            $hasViolations
        );

        // Mark as needing review if violations or special statuses
        $result['needs_review'] = $hasViolations ||
            $result['status'] === 'incomplete' ||
            $result['status'] === 'half_day' ||
            $result['status'] === 'unscheduled';

        // Store violations in breakdown for audit
        $result['breakdown']['violations'] = $result['violations'];

        return $result;
    }

    // -------------------- Helper Methods (unchanged) --------------------

    protected function getExpectedHours(?array $schedule): float
    {
        if (!$schedule) return 8.0;
        if (isset($schedule['shift']->duration_hours) && $schedule['shift']->duration_hours > 0) {
            return (float) $schedule['shift']->duration_hours;
        }
        $start = $schedule['start_time'];
        $end = $schedule['end_time'];
        $duration = $start->diffInMinutes($end) / 60.0;
        return round($duration, 2);
    }

    protected function checkLateness(?Carbon $actualStart, Carbon $scheduledStart, int $graceMinutes, Carbon $date): array
    {
        if (!$actualStart) return ['is_late' => false, 'minutes_late' => 0];
        $graceTime = $scheduledStart->copy()->addMinutes($graceMinutes);
        if ($actualStart->greaterThan($graceTime)) {
            $minutesLate = $actualStart->diffInMinutes($graceTime);
            return ['is_late' => true, 'minutes_late' => $minutesLate];
        }
        return ['is_late' => false, 'minutes_late' => 0];
    }

    protected function checkEarlyDeparture(?Carbon $actualEnd, Carbon $scheduledEnd, int $graceMinutes, Carbon $date): array
    {
        if (!$actualEnd) return ['is_early' => false, 'minutes_early' => 0];
        $graceTime = $scheduledEnd->copy()->subMinutes($graceMinutes);
        if ($actualEnd->lessThan($graceTime)) {
            $minutesEarly = $graceTime->diffInMinutes($actualEnd);
            return ['is_early' => true, 'minutes_early' => $minutesEarly];
        }
        return ['is_early' => false, 'minutes_early' => 0];
    }

    protected function calculateOvertime(float $totalHours, AttendancePolicy $policy, Carbon $date, int $employeeId): array
    {
        $regularHours = 0.0;
        $overtimeHours = 0.0;
        $doubleTimeHours = 0.0;
        $breakdown = [];

        $dailyThreshold = $policy->overtime_daily_threshold_hours;
        $weeklyThreshold = $policy->overtime_weekly_threshold_hours ?? 40;

        // Step 1: Split today's hours into daily regular and daily overtime
        // Preserve unpaid_break_minutes deduction for backward compatibility:
        // overtime is calculated from gross hours minus unpaid break, ensuring
        // regular + overtime <= net_hours (which already has unpaid break deducted)
        $effectiveHours = $totalHours - ($policy->unpaid_break_minutes / 60);
        $dailyRegularHours = min($effectiveHours, $dailyThreshold);
        $dailyOvertimeHours = max(0, $effectiveHours - $dailyThreshold);

        $breakdown['daily_regular'] = round($dailyRegularHours, 2);
        $breakdown['daily_overtime'] = round($dailyOvertimeHours, 2);

        // Step 2: Sum previous days' regular hours this week
        $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY);
        $previousRegularHours = (float) Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$weekStart->format('Y-m-d'), $date->copy()->subDay()->format('Y-m-d')])
            ->sum('regular_hours');

        $weeklyRegularSoFar = $previousRegularHours + $dailyRegularHours;
        $breakdown['weekly_regular_so_far'] = round($weeklyRegularSoFar, 2);
        $breakdown['weekly_threshold'] = $weeklyThreshold;

        // Step 3: If weekly regular exceeds threshold, overflow some regular into overtime
        if ($weeklyRegularSoFar > $weeklyThreshold) {
            $overflowIntoOvertime = $weeklyRegularSoFar - $weeklyThreshold;
            // Can only overflow today's regular hours (not previous days')
            $overflowIntoOvertime = min($overflowIntoOvertime, $dailyRegularHours);

            $finalRegularHours = $dailyRegularHours - $overflowIntoOvertime;
            $finalOvertimeHours = $dailyOvertimeHours + $overflowIntoOvertime;

            $breakdown['overflow_into_overtime'] = round($overflowIntoOvertime, 2);
        } else {
            $finalRegularHours = $dailyRegularHours;
            $finalOvertimeHours = $dailyOvertimeHours;
            $breakdown['overflow_into_overtime'] = 0;
        }

        $breakdown['final_regular'] = round($finalRegularHours, 2);
        $breakdown['final_overtime'] = round($finalOvertimeHours, 2);

        $overtimeHours = $finalOvertimeHours;
        $regularHours = $finalRegularHours;

        // Step 4: Apply max_daily_overtime_hours cap
        if ($policy->max_daily_overtime_hours > 0 && $overtimeHours > $policy->max_daily_overtime_hours) {
            $overtimeHours = $policy->max_daily_overtime_hours;
            $breakdown['daily_overtime_capped'] = true;
        }
        $breakdown['daily_threshold'] = $dailyThreshold;
        $breakdown['max_daily_overtime'] = $policy->max_daily_overtime_hours;
        $breakdown['double_time_threshold'] = $policy->double_time_threshold_hours;

        // Step 5: Apply double time threshold
        if ($policy->double_time_threshold_hours > 0 && $totalHours > $policy->double_time_threshold_hours) {
            $doubleTimeHours = $totalHours - $policy->double_time_threshold_hours;
            $overtimeHours -= $doubleTimeHours;
            if ($overtimeHours < 0) {
                $doubleTimeHours += $overtimeHours;
                $overtimeHours = 0;
            }
        }

        return [
            'regular_hours' => round($regularHours, 2),
            'overtime_hours' => round($overtimeHours, 2),
            'double_time_hours' => round($doubleTimeHours, 2),
            'breakdown' => $breakdown
        ];
    }

    protected function checkBreakCompliance(
        array $sessions,
        $requiresBreakAfterHours,
        $requiredBreakMinutes
    ): array {
        // Parse break thresholds: support JSON arrays or scalar values (backward compatible)
        $breakAfterHours = $this->parseBreakRuleValue($requiresBreakAfterHours);
        $breakDurationMinutes = $this->parseBreakRuleValue($requiredBreakMinutes);

        // Ensure both are arrays of the same length
        $ruleCount = max(count($breakAfterHours), count($breakDurationMinutes));
        $breakAfterHours = array_pad($breakAfterHours, $ruleCount, end($breakAfterHours) ?: 5);
        $breakDurationMinutes = array_pad($breakDurationMinutes, $ruleCount, end($breakDurationMinutes) ?: 30);

        if (empty($sessions) || $ruleCount === 0) {
            return [
                'compliant' => true,
                'missed_breaks' => 0,
                'total_missed_minutes' => 0,
                'violations' => [],
            ];
        }

        $sessions = collect($sessions)->sortBy('start')->values()->toArray();
        $cumulativeHours = 0;
        $missedBreaks = 0;
        $totalMissedMinutes = 0;
        $violations = [];

        foreach ($breakAfterHours as $ruleIndex => $thresholdHours) {
            $requiredMinutes = $breakDurationMinutes[$ruleIndex] ?? 30;
            $requiredSeconds = $requiredMinutes * 60;
            $thresholdReached = false;

            while (!$thresholdReached) {
                // Check if we can detect a break in the remaining sessions
                $foundBreak = false;

                for ($i = 0; $i < count($sessions); $i++) {
                    $session = $sessions[$i];
                    if (!$session['end']) continue;

                    $sessionHours = $session['duration'] ?? 0;
                    $cumulativeHours += $sessionHours;

                    if ($cumulativeHours > $thresholdHours) {
                        // Check if there's a sufficient gap before the next session
                        $gapFound = false;
                        if ($i < count($sessions) - 1) {
                            $nextSessionStart = Carbon::parse($sessions[$i + 1]['start']);
                            $thisSessionEnd = Carbon::parse($session['end']);
                            $gapSeconds = $thisSessionEnd->diffInSeconds($nextSessionStart);
                            if ($gapSeconds >= $requiredSeconds) {
                                $gapFound = true;
                                $cumulativeHours = 0; // Reset after break
                                $foundBreak = true;
                            }
                        }

                        if (!$gapFound) {
                            // No sufficient break found - record violation
                            $missedBreaks++;
                            $totalMissedMinutes += $requiredMinutes;
                            $violations[] = [
                                'after_hours' => $thresholdHours,
                                'required_minutes' => $requiredMinutes,
                            ];
                            $cumulativeHours = 0; // Reset for next rule
                        }

                        $thresholdReached = true;
                        break;
                    }

                    // Check for gaps even if threshold not yet reached (natural break)
                    if ($i < count($sessions) - 1) {
                        $nextSessionStart = Carbon::parse($sessions[$i + 1]['start']);
                        $thisSessionEnd = Carbon::parse($session['end']);
                        $gapSeconds = $thisSessionEnd->diffInSeconds($nextSessionStart);
                        if ($gapSeconds >= $requiredSeconds) {
                            $cumulativeHours = 0; // Reset after break
                        }
                    }
                }

                if (!$thresholdReached) {
                    // Exhausted all sessions without reaching threshold - done
                    break;
                }
            }
        }

        return [
            'compliant' => $missedBreaks === 0,
            'missed_breaks' => $missedBreaks,
            'total_missed_minutes' => $totalMissedMinutes,
            'violations' => $violations,
        ];
    }

    /**
     * Parse a break rule value that may be a JSON array or a scalar.
     *
     * @param  mixed  $value
     * @return array
     */
    protected function parseBreakRuleValue($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && str_starts_with(trim($value), '[')) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // Treat as scalar
        $numeric = is_numeric($value) ? (float) $value : 0;
        return $numeric > 0 ? [$numeric] : [];
    }

    protected function determineStatus(
        float $totalHours,
        int $minutesLate,
        int $minutesEarly,
        float $expectedHours,
        bool $hasViolations = false
    ): string {
        if ($totalHours == 0) return 'absent';
        if ($minutesLate > 0) return 'late';

        $halfDayThreshold = $expectedHours * 0.5;
        $earlyDepartureThreshold = $expectedHours * 0.9;

        if ($totalHours <= $halfDayThreshold) return 'half_day';
        if ($totalHours > $halfDayThreshold && $totalHours < $earlyDepartureThreshold) return 'incomplete';
        if ($minutesEarly > 0) return 'early_departure';

        return 'present';
    }
}
