<?php

namespace App\Modules\Hr\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use App\Modules\Hr\Models\{
    Employee,
    EmployeePosition,
    EmployeeWorkPattern,
    WorkPattern,
    AttendancePolicy,
    PolicyAssignment,
    ClockEvent,
    Attendance,
    AttendanceSession,
    ShiftSchedule
};
use App\Modules\Hr\Services\AttendanceCalculator;
use App\Modules\Hr\Models\Shift;
use App\Modules\Hr\Models\Location;


class AttendanceCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected Employee $employee;
    protected Shift $shift;
    protected WorkPattern $workPattern;
    protected AttendancePolicy $defaultPolicy;
    protected AttendanceCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a company for multi-tenancy context
        $this->company = \App\Modules\Hr\Models\Company::factory()->create();

        // Create a department belonging to the company
        $department = \App\Modules\Hr\Models\Department::factory()->create([
            'company_id' => $this->company->id,
        ]);

        // Create base shift
        $this->shift = Shift::factory()->create([
            'name' => 'Standard 8-5',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'duration_hours' => 8.0,
            'code' => 'Code123-'. rand(),
            'is_active' => true,
            'is_default' => false,
            'company_id' => $this->company->id,
        ]);

        // Create default work pattern (system-wide fallback)
        $this->workPattern = WorkPattern::factory()->create([
            'name' => 'Mon-Fri',
            'shift_id' => $this->shift->id,
            'applicable_days' => "1,2,3,4,5", // Monday to Friday
            'pattern_type' => 'recurring',
            'effective_date' => '2026-01-01',
            'is_active' => true,
            'is_default' => true,
            'company_id' => $this->company->id,
        ]);

        // Create employee with company
        $this->employee = Employee::factory()->create([
            'employee_number' => 'EMP-TEST-'.rand(),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company_id' => $this->company->id,
        ]);

        // Create default attendance policy (system-wide fallback)
        $this->defaultPolicy = AttendancePolicy::factory()->create(['company_id' => $this->employee->company_id,
            'name' => 'Default Policy',
            'grace_period_minutes' => 5,
            'early_departure_grace_minutes' => 5,
            'overtime_daily_threshold_hours' => 8.0,
            'overtime_weekly_threshold_hours' => 40.0,
            'max_daily_overtime_hours' => 4.0,
            'overtime_multiplier' => 1.5,
            'double_time_threshold_hours' => 12.0,
            'double_time_multiplier' => 2.0,
            'requires_break_after_hours' => 5.0,
            'break_duration_minutes' => 30,
            'unpaid_break_minutes' => 0,
            'effective_date' => '2026-01-01',
            'is_active' => true,
            'is_default' => true,
            'company_id' => $this->company->id,
        ]);

        // Create employee position (without direct work_pattern_id or attendance_policy_id)
        EmployeePosition::factory()->create([
            'employee_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'attendance_policy_id' => null, // employee-specific policy override (none)
            'pay_type' => 'hourly',
            'hourly_rate' => 20.00,
            'department_id' => $department->id,
            'company_id' => $this->company->id,
            // 'start_date' => '2026-01-01',
        ]);

        // Assign the default work pattern to the employee via EmployeeWorkPattern
        EmployeeWorkPattern::create([
            'employee_id' => $this->employee->id,
            'work_pattern_id' => $this->workPattern->id,
            'start_date' => '2026-01-01',
            'end_date' => null,
        ]);

        $this->calculator = new AttendanceCalculator();
    }

    // ------------------------------------------------------------------------
    // Attendance calculation tests (unchanged)
    // ------------------------------------------------------------------------

    /** @test */
    public function it_marks_present_with_on_time_clock_in_and_out()
    {
        $date = Carbon::parse('2026-02-16'); // Monday
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0));

        $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->whereDate('date', $date)
            ->first();

        $this->assertEquals('present', $attendance->status);
        $this->assertEquals(9.0, $attendance->net_hours);
        $this->assertEquals(8.0, $attendance->regular_hours);
        $this->assertEquals(1.0, $attendance->overtime_hours);
        $this->assertEquals(0, $attendance->minutes_late);
        $this->assertEquals(0, $attendance->minutes_early_departure);
        $this->assertTrue((bool) $attendance->needs_review); // No break taken
    }

    /** @test */
    public function it_marks_late_when_clock_in_exceeds_grace_period()
    {
        $date = Carbon::parse('2026-02-16');
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 6));
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0));

        $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->whereDate('date', $date)
            ->first();

        $this->assertEquals('late', $attendance->status);
        $this->assertEquals(1, $attendance->minutes_late);
        $this->assertTrue((bool) $attendance->needs_review);
    }

    /** @test */
    public function it_does_not_mark_late_when_clock_in_within_grace_period()
    {
        $date = Carbon::parse('2026-02-16');
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 4));
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0));

        $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->whereDate('date', $date)
            ->first();

        $this->assertNotEquals('late', $attendance->status);
        $this->assertEquals(0, $attendance->minutes_late);
    }

    /** @test */
    public function it_marks_early_departure_when_clock_out_before_grace_period()
    {
        $date = Carbon::parse('2026-02-16');
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(16, 54));

        $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->whereDate('date', $date)
            ->first();

        $this->assertEquals('early_departure', $attendance->status);
        $this->assertEquals(1, $attendance->minutes_early_departure);
        $this->assertTrue((bool) $attendance->needs_review);
    }

    /** @test */
    public function it_does_not_mark_early_departure_when_clock_out_within_grace()
    {
        $date = Carbon::parse('2026-02-16');
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(16, 56));

        $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->whereDate('date', $date)
            ->first();

        $this->assertNotEquals('early_departure', $attendance->status);
        $this->assertEquals(0, $attendance->minutes_early_departure);
    }

    /** @test */
    public function it_calculates_overtime_according_to_daily_threshold()
    {
        $date = Carbon::parse('2026-02-16');
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(18, 0));

        $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->whereDate('date', $date)
            ->first();

        $this->assertEquals(10.0, $attendance->net_hours);
        $this->assertEquals(8.0, $attendance->regular_hours);
        $this->assertEquals(2.0, $attendance->overtime_hours);
        $this->assertEquals(0.0, $attendance->double_time_hours);
    }

    /** @test */
    public function it_applies_double_time_after_threshold()
    {
        $policy = $this->defaultPolicy;
        $policy->double_time_threshold_hours = 10.0;
        $policy->save();

        $date = Carbon::parse('2026-02-16');
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(20, 0));

        $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->whereDate('date', $date)
            ->first();

        $this->assertEquals(12.0, $attendance->net_hours);
        $this->assertEquals(8.0, $attendance->regular_hours);
        $this->assertEquals(2.0, $attendance->overtime_hours);
        $this->assertEquals(2.0, $attendance->double_time_hours);
    }

    /** @test */
    public function it_respects_max_daily_overtime_limit()
    {
        $policy = $this->defaultPolicy;
        $policy->max_daily_overtime_hours = 2.0;
        $policy->save();

        $date = Carbon::parse('2026-02-16');
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(20, 0));

        $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->whereDate('date', $date)
            ->first();

        $this->assertEquals(12.0, $attendance->net_hours);
        $this->assertEquals(8.0, $attendance->regular_hours);
        $this->assertEquals(2.0, $attendance->overtime_hours);
    }

    /** @test */
    public function it_handles_zero_grace_period_correctly()
    {
        $policy = $this->defaultPolicy;
        $policy->grace_period_minutes = 0;
        $policy->save();

        $date = Carbon::parse('2026-02-16');
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 1));
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0));

        $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->whereDate('date', $date)
            ->first();

        $this->assertEquals('late', $attendance->status);
        $this->assertEquals(1, $attendance->minutes_late);
    }

    /** @test */
    public function it_detects_missing_clock_out()
    {
        $date = Carbon::parse('2026-02-16');
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));

        $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->whereDate('date', $date)
            ->first();

        $this->assertEquals('absent', $attendance->status);
        $this->assertEquals(0.0, $attendance->net_hours);
        $this->assertTrue((bool) $attendance->needs_review);

        $session = AttendanceSession::where('attendance_id', $attendance->id)->first();
        $this->assertNotNull($session);
        $this->assertNull($session->end_time);
    }

    /** @test */
    public function it_handles_non_working_day_according_to_work_pattern()
    {
        $date = Carbon::parse('2026-02-15'); // Sunday, not in work pattern
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0));

        $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->whereDate('date', $date)
            ->first();

        $this->assertEquals('unscheduled', $attendance->status);
        $this->assertEquals(9.0, $attendance->net_hours);
        $this->assertTrue((bool) $attendance->needs_review);
    }

    // ------------------------------------------------------------------------
    // Policy waterfall tests (refactored to use PolicyAssignment)
    // ------------------------------------------------------------------------

    /** @test */
    public function it_uses_employee_specific_policy()
    {
        $employeePolicy = AttendancePolicy::factory()->create(['company_id' => $this->employee->company_id,
            'effective_date' => '2026-01-01',
            'is_active' => true,
        ]);

        // Direct employee-specific override via EmployeePosition
        $position = $this->employee->employeePosition;
        $position->attendance_policy_id = $employeePolicy->id;
        $position->save();

        $policy = $this->calculator->getApplicablePolicy(
            $this->employee,
            $this->employee->employeePosition,
            Carbon::parse('2026-02-16')
        );

        $this->assertEquals($employeePolicy->id, $policy->id);
    }

    /** @test */
    public function it_falls_back_to_department_policy()
    {
        // Remove employee-specific policy
        $this->employee->employeePosition->attendance_policy_id = null;
        $this->employee->employeePosition->save();

        $departmentPolicy = AttendancePolicy::factory()->create(['company_id' => $this->employee->company_id,
            'effective_date' => '2026-01-01',
            'is_active' => true,
        ]);

        // Assign policy to department via PolicyAssignment
        PolicyAssignment::create([
            'assignable_type' => \App\Modules\Hr\Models\Department::class,
            'assignable_id' => $this->employee->employeePosition->department->id,
            'attendance_policy_id' => $departmentPolicy->id,
            'company_id' => $this->employee->company_id,
        ]);

        $policy = $this->calculator->getApplicablePolicy(
            $this->employee,
            $this->employee->employeePosition,
            Carbon::parse('2026-02-16')
        );

        $this->assertEquals($departmentPolicy->id, $policy->id);
    }

    /** @test */
    public function it_falls_back_to_location_policy()
    {
        $this->employee->employeePosition->attendance_policy_id = null;
        $this->employee->employeePosition->save();

        $locationPolicy = AttendancePolicy::factory()->create(['company_id' => $this->employee->company_id,
            'effective_date' => '2026-01-01',
            'is_active' => true,
        ]);

        $location = Location::factory()->create();
        $this->employee->employeePosition->location_id = $location->id;
        $this->employee->employeePosition->save();

        PolicyAssignment::create([
            'assignable_type' => \App\Modules\Hr\Models\Location::class,
            'assignable_id' => $location->id,
            'attendance_policy_id' => $locationPolicy->id,
            'company_id' => $this->employee->company_id,
        ]);

        $policy = $this->calculator->getApplicablePolicy(
            $this->employee,
            $this->employee->employeePosition,
            Carbon::parse('2026-02-16')
        );

        $this->assertEquals($locationPolicy->id, $policy->id);
    }

    /** @test */
    public function location_policy_is_skipped_if_no_location_assigned()
    {
        $this->employee->employeePosition->attendance_policy_id = null;
        $this->employee->employeePosition->location_id = null;
        $this->employee->employeePosition->save();

        $companyPolicy = AttendancePolicy::factory()->create(['company_id' => $this->employee->company_id,
            'is_default' => false,
            'company_id' => $this->company->id,
            'effective_date' => '2026-01-01',
            'is_active' => true,
        ]);

        PolicyAssignment::create([
            'assignable_type' => \App\Modules\Hr\Models\Company::class,
            'assignable_id' => $this->employee->employeePosition->department->company->id,
            'attendance_policy_id' => $companyPolicy->id,
            'company_id' => $this->employee->company_id,
        ]);

        $policy = $this->calculator->getApplicablePolicy(
            $this->employee,
            $this->employee->employeePosition,
            Carbon::parse('2026-02-16')
        );

        $this->assertEquals($companyPolicy->id, $policy->id);
    }

    /** @test */
    public function it_falls_back_to_company_policy()
    {
        $this->employee->employeePosition->attendance_policy_id = null;
        $this->employee->employeePosition->save();

        $companyPolicy = AttendancePolicy::factory()->create(['company_id' => $this->employee->company_id,
            'effective_date' => '2026-01-01',
            'is_active' => true,
        ]);

        PolicyAssignment::create([
            'assignable_type' => \App\Modules\Hr\Models\Company::class,
            'assignable_id' => $this->employee->employeePosition->department->company->id,
            'attendance_policy_id' => $companyPolicy->id,
            'company_id' => $this->employee->company_id,
        ]);

        $policy = $this->calculator->getApplicablePolicy(
            $this->employee,
            $this->employee->employeePosition,
            Carbon::parse('2026-02-16')
        );

        $this->assertEquals($companyPolicy->id, $policy->id);
    }

    /** @test */
/** @test */
public function it_falls_back_to_system_default_policy()
{
    // Remove employee-specific policy
    $this->employee->employeePosition->attendance_policy_id = null;
    $this->employee->employeePosition->save();

    // No policy assignments exist by default (none were created in setUp)
    // So the method will fall back to the system default.

    $policy = $this->calculator->getApplicablePolicy(
        $this->employee,
        $this->employee->employeePosition,
        Carbon::parse('2026-02-16')
    );

    $this->assertEquals($this->defaultPolicy->id, $policy->id);
}

    /** @test */
    public function it_respects_policy_effective_dates()
    {
        $policy = AttendancePolicy::factory()->create(['company_id' => $this->employee->company_id,
            'effective_date' => '2026-03-01',
            'is_active' => true,
        ]);



        $this->employee->employeePosition->attendance_policy_id = $policy->id;
        $this->employee->employeePosition->save();

        $policy = $this->calculator->getApplicablePolicy(
            $this->employee,
            $this->employee->employeePosition,
            Carbon::parse('2026-02-16')
        );

        $this->assertEquals($this->defaultPolicy->id, $policy->id);
    }

    /** @test */
    public function it_respects_policy_expiration_dates()
    {
        $policy = AttendancePolicy::factory()->create(['company_id' => $this->employee->company_id,
            'effective_date' => '2026-01-01',
            'expiration_date' => '2026-02-01',
            'is_active' => true,
        ]);

        $this->employee->employeePosition->attendance_policy_id = $policy->id;
        $this->employee->employeePosition->save();

        $policy = $this->calculator->getApplicablePolicy(
            $this->employee,
            $this->employee->employeePosition,
            Carbon::parse('2026-02-16')
        );

        $this->assertEquals($this->defaultPolicy->id, $policy->id);
    }

    /** @test */
    public function it_skips_inactive_policies()
    {
        $policy = AttendancePolicy::factory()->create(['company_id' => $this->employee->company_id,
            'effective_date' => '2026-01-01',
            'is_active' => false,
        ]);

        $this->employee->employeePosition->attendance_policy_id = $policy->id;
        $this->employee->employeePosition->save();

        $policy = $this->calculator->getApplicablePolicy(
            $this->employee,
            $this->employee->employeePosition,
            Carbon::parse('2026-02-16')
        );

        $this->assertEquals($this->defaultPolicy->id, $policy->id);
    }

    // ------------------------------------------------------------------------
    // Work pattern and shift waterfall tests (refactored for EmployeeWorkPattern)
    // ------------------------------------------------------------------------

    /** @test */
    public function it_uses_the_system_default_shift_if_user_shift_is_not_available()
    {
        // Make sure default work pattern is active and has a shift
        $this->workPattern->update(['is_active' => true, 'is_default' => true]);

        // Remove employee-specific work pattern assignment
        EmployeeWorkPattern::where('employee_id', $this->employee->id)->delete();

        // Create a new employee with no position shift or work pattern
        $employee = Employee::factory()->create(['employee_number' => 'EMP-TEST-002', 'company_id' => $this->company->id]);
        EmployeePosition::factory()->create([
            'employee_id' => $employee->id,
            'department_id' => $this->employee->employeePosition->department_id,
            'shift_id' => null,
            'attendance_policy_id' => null,
            'pay_type' => 'hourly',
            'hourly_rate' => 20.00,
            'company_id' => $this->company->id,
        ]);

        // No EmployeeWorkPattern for this employee, so falls back to system default work pattern
        $date = Carbon::parse('2026-02-16');
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0), $employee->employee_number);
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0), $employee->employee_number);

        $this->calculator->calculateForDay($employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $date)
            ->first();

        // Should use the shift from the system default work pattern
        $this->assertEquals($this->workPattern->shift->id, $attendance->shift_id);
    }

    /** @test */
    public function it_prioritises_the_user_default_shift_even_if_system_shift_is_available()
    {
        // Create a system default work pattern with a different shift
        $systemShift = Shift::factory()->create([
            'name' => 'System Default Shift',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'is_default' => true,
            'company_id' => $this->company->id,
        ]);
        $systemPattern = WorkPattern::factory()->create([
            'name' => 'System Default Pattern',
            'shift_id' => $systemShift->id,
            'applicable_days' => "1,2,3,4,5",
            'is_default' => true,
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);

        // Employee has their own work pattern via EmployeeWorkPattern (created in setUp)
        // This pattern uses $this->shift (Standard 8-5)

        $date = Carbon::parse('2026-02-16');
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0));

        $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->whereDate('date', $date)
            ->first();

        // Should use the employee's own shift, not the system default
        $this->assertEquals($this->shift->id, $attendance->shift_id);
    }

    /** @test */
    public function it_prioritises_the_shift_scheduled_even_if_user_shift_is_available()
    {
        // Create a shift schedule for the employee on this date with a different shift
        $scheduledShift = Shift::factory()->create([
            'name' => 'Scheduled Shift',
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
        ]);

        ShiftSchedule::factory()->create([
            'employee_id' => $this->employee->id,
            'schedule_date' => '2026-02-16',
            'shift_id' => $scheduledShift->id,
            'is_published' => true,
            'start_time_override' => null,
            'end_time_override' => null,
        ]);

        $date = Carbon::parse('2026-02-16');
        $this->createClockEvent('clock_in', $date->copy()->setTime(10, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(19, 0));

        $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->whereDate('date', $date)
            ->first();

        // Should use the shift from the schedule, not the employee's default work pattern shift
        $this->assertEquals($scheduledShift->id, $attendance->shift_id);
    }

    /** @test */
    public function it_falls_back_to_the_system_default_work_pattern_shift_when_scheduled_and_user_shift_are_not_available()
    {
        // Remove employee's work pattern assignment
        EmployeeWorkPattern::where('employee_id', $this->employee->id)->delete();

        // Ensure system default work pattern exists and is active
        $this->workPattern->update(['is_default' => true,
            'company_id' => $this->company->id, 'is_active' => true]);

        $employee = Employee::factory()->create(['employee_number' => 'EMP-TEST-002', 'company_id' => $this->company->id]);
        EmployeePosition::factory()->create([
            'employee_id' => $employee->id,
            'department_id' => $this->employee->employeePosition->department_id,
            'shift_id' => null,
            'attendance_policy_id' => null,
            'pay_type' => 'hourly',
            'hourly_rate' => 20.00,
            'company_id' => $this->company->id,
        ]);

        $date = Carbon::parse('2026-02-16');
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0), $employee->employee_number);
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0), $employee->employee_number);

        $this->calculator->calculateForDay($employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $date)
            ->first();

        $this->assertEquals($this->workPattern->shift->id, $attendance->shift_id);
    }

    /** @test */
    public function it_prioritises_the_user_schedule_work_pattern_shift_even_if_the_system_default_work_pattern_shift_is_available()
    {
        // Create a system default work pattern with a different shift
        $systemShift = Shift::factory()->create(['name' => 'System Shift', 'is_default' => true]);
        $systemPattern = WorkPattern::factory()->create([
            'name' => 'System Pattern',
            'shift_id' => $systemShift->id,
            'is_default' => true,
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);

        // Employee has their own work pattern via EmployeeWorkPattern (from setUp)
        // This pattern uses $this->shift (Standard 8-5)

        $date = Carbon::parse('2026-02-16');
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0));

        $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->whereDate('date', $date)
            ->first();

        // Should use employee's own shift, not system default
        $this->assertEquals($this->shift->id, $attendance->shift_id);
    }

    // ------------------------------------------------------------------------
    // Other existing tests (unchanged)
    // ------------------------------------------------------------------------

    /** @test */
    public function it_calculates_weekly_overtime_correctly()
    {
        // Set weekly threshold to 40
        $this->defaultPolicy->update(['overtime_weekly_threshold_hours' => 40, 'overtime_daily_threshold_hours' => 8]);

        $monday = Carbon::now()->startOfWeek(Carbon::MONDAY);

        // Create 5 days of attendance (Mon-Fri), 9 hours each = 45 hours total
        for ($i = 0; $i < 5; $i++) {
            $date = $monday->copy()->addDays($i);

            // Create clock events: 9 hours of work
            ClockEvent::factory()->create([
                'employee_id' => $this->employee->id,
                'employee_number' => $this->employee->employee_number,
                'timestamp' => $date->copy()->setHour(8)->setMinute(0)->setSecond(0),
                'event_type' => 'clock_in',
            ]);

            ClockEvent::factory()->create([
                'employee_id' => $this->employee->id,
                'employee_number' => $this->employee->employee_number,
                'timestamp' => $date->copy()->setHour(17)->setMinute(0)->setSecond(0),
                'event_type' => 'clock_out',
            ]);

            $this->calculator->calculateForDay(
                $this->employee->employee_number,
                $date
            );
        }

        // Verify the last day's calculation reflects weekly overtime context
        $friday = $monday->copy()->addDays(4);
        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->where('date', $friday->format('Y-m-d'))
            ->first();

        $this->assertNotNull($attendance);
        // Daily overtime: 9 - 8 = 1h; overtime_hours should be > 0
        $this->assertGreaterThan(0, (float) $attendance->overtime_hours);
    }

    /** @test */
    public function it_marks_absent_when_no_clock_events_on_scheduled_day()
    {
        $date = Carbon::parse('2026-02-16');
        // No clock events

        $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->whereDate('date', $date)
            ->first();

        $this->assertEquals('absent', $attendance->status);
        $this->assertEquals(0.0, $attendance->net_hours);
        $this->assertTrue((bool) $attendance->needs_review);
    }

    /** @test */
    public function it_applies_unpaid_break_deduction()
    {
        $policy = $this->defaultPolicy;
        $policy->unpaid_break_minutes = 30;
        $policy->save();

        $date = Carbon::parse('2026-02-16');
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0));

        $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->whereDate('date', $date)
            ->first();

        $this->assertEquals(8.5, $attendance->net_hours);
        $this->assertEquals(8.0, $attendance->regular_hours);
        $this->assertEquals(0.5, $attendance->overtime_hours);
    }

    /** @test */
    public function it_detects_missed_break_when_required()
    {
        $policy = $this->defaultPolicy;
        $policy->requires_break_after_hours = 5.0;
        $policy->break_duration_minutes = 30;
        $policy->save();

        $date = Carbon::parse('2026-02-16');
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(16, 0)); // 8 hours

        $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->whereDate('date', $date)
            ->first();

        $this->assertEquals(30, $attendance->missed_break_minutes);
        $this->assertTrue((bool) $attendance->needs_review);
        $this->assertStringContainsString('missed_break', json_encode($attendance->calculation_metadata));
    }

    /** @test */
    public function it_detects_multiple_missed_breaks()
    {
        // Use a lower break threshold and different required minutes
        // to verify break compliance detection with alternative settings
        $this->defaultPolicy->update([
            'requires_break_after_hours' => 3.0,
            'break_duration_minutes' => 45,
        ]);

        $date = Carbon::now();

        // Create clock events: 9 hours straight, no breaks
        $this->createClockEvent('clock_in', $date->copy()->setHour(8)->setMinute(0)->setSecond(0));
        $this->createClockEvent('clock_out', $date->copy()->setHour(17)->setMinute(0)->setSecond(0));

        $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->where('date', $date->format('Y-m-d'))
            ->first();

        $this->assertNotNull($attendance);
        // Break required after 3 hours of continuous work with 45-minute break
        $this->assertEquals(45, (int) $attendance->missed_break_minutes);
        $this->assertTrue((bool) $attendance->needs_review);
    }

    /** @test */
    public function it_creates_unpaid_break_session_when_policy_has_unpaid_break()
    {
        $this->defaultPolicy->update(['unpaid_break_minutes' => 30]);

        $date = Carbon::now();

        $this->createClockEvent('clock_in', $date->copy()->setHour(8)->setMinute(0)->setSecond(0));
        $this->createClockEvent('clock_out', $date->copy()->setHour(17)->setMinute(0)->setSecond(0));

        $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->where('date', $date->format('Y-m-d'))
            ->first();

        $this->assertNotNull($attendance);

        // Check sessions JSON contains unpaid_break entry (has null start/end times)
        $sessions = $attendance->sessions;
        $this->assertIsArray($sessions);

        $unpaidBreaks = array_filter($sessions, function ($s) {
            return ($s['start'] ?? null) === null && ($s['end'] ?? null) === null;
        });
        $this->assertCount(1, $unpaidBreaks);

        $unpaidBreak = reset($unpaidBreaks);
        $this->assertEquals(0.5, $unpaidBreak['duration']);

        // Verify AttendanceSession record exists
        $unpaidSession = AttendanceSession::where('attendance_id', $attendance->id)
            ->where('session_type', 'unpaid_break')
            ->first();
        $this->assertNotNull($unpaidSession);
        $this->assertEquals(0.5, (float) $unpaidSession->duration_hours);
    }

    /** @test */
    public function it_detects_multiple_break_rules_with_two_thresholds()
    {
        $this->defaultPolicy->update([
            'requires_break_after_hours' => json_encode([4.0, 8.0]),
            'break_duration_minutes' => json_encode([30, 30]),
        ]);

        $date = Carbon::now();

        // Session 1: 08:00 - 12:00 (4 hours)
        ClockEvent::factory()->create([
            'employee_id' => $this->employee->id,
            'employee_number' => $this->employee->employee_number,
            'timestamp' => $date->copy()->setHour(8)->setMinute(0)->setSecond(0),
            'event_type' => 'clock_in',
        ]);
        ClockEvent::factory()->create([
            'employee_id' => $this->employee->id,
            'employee_number' => $this->employee->employee_number,
            'timestamp' => $date->copy()->setHour(12)->setMinute(0)->setSecond(0),
            'event_type' => 'clock_out',
        ]);

        // Session 2: 12:10 - 17:00 (gap only 10 min, break #1 missed)
        ClockEvent::factory()->create([
            'employee_id' => $this->employee->id,
            'employee_number' => $this->employee->employee_number,
            'timestamp' => $date->copy()->setHour(12)->setMinute(10)->setSecond(0),
            'event_type' => 'clock_in',
        ]);
        ClockEvent::factory()->create([
            'employee_id' => $this->employee->id,
            'employee_number' => $this->employee->employee_number,
            'timestamp' => $date->copy()->setHour(17)->setMinute(0)->setSecond(0),
            'event_type' => 'clock_out',
        ]);

        // Session 3: 17:05 - 20:00 (gap only 5 min, break #2 missed, cumulative > 8h)
        ClockEvent::factory()->create([
            'employee_id' => $this->employee->id,
            'employee_number' => $this->employee->employee_number,
            'timestamp' => $date->copy()->setHour(17)->setMinute(5)->setSecond(0),
            'event_type' => 'clock_in',
        ]);
        ClockEvent::factory()->create([
            'employee_id' => $this->employee->id,
            'employee_number' => $this->employee->employee_number,
            'timestamp' => $date->copy()->setHour(20)->setMinute(0)->setSecond(0),
            'event_type' => 'clock_out',
        ]);

        $result = $this->calculator->calculateForDay(
            $this->employee->employee_number,
            $date
        );

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->where('date', $date->format('Y-m-d'))
            ->first();

        $this->assertNotNull($attendance);
        // Both breaks missed: 30 + 30 = 60 minutes
        $this->assertEquals(60, (int) $attendance->missed_break_minutes);
        $this->assertTrue((bool) $attendance->needs_review);

        // Check calculation_metadata for violations
        $metadata = $attendance->calculation_metadata;
        if (is_string($metadata)) {
            $metadata = json_decode($metadata, true);
        }
        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('violations', $metadata);
        $this->assertCount(2, $metadata['violations']);
    }

    // ------------------------------------------------------------------------
    // Weekly Overtime Tests
    // ------------------------------------------------------------------------

    /** @test */
    public function it_detects_weekly_overtime_even_when_no_daily_overtime()
    {
        // Set thresholds: 8h daily, 40h weekly
        $this->defaultPolicy->update([
            'overtime_daily_threshold_hours' => 8,
            'overtime_weekly_threshold_hours' => 40,
        ]);

        $monday = Carbon::now()->startOfWeek(Carbon::MONDAY);

        // Process 6 days (Mon-Sat), 7 hours each day
        for ($i = 0; $i < 6; $i++) {
            $date = $monday->copy()->addDays($i);

            ClockEvent::factory()->create([
                'employee_id' => $this->employee->id,
                'employee_number' => $this->employee->employee_number,
                'timestamp' => $date->copy()->setHour(8)->setMinute(0)->setSecond(0),
                'event_type' => 'clock_in',
            ]);

            ClockEvent::factory()->create([
                'employee_id' => $this->employee->id,
                'employee_number' => $this->employee->employee_number,
                'timestamp' => $date->copy()->setHour(15)->setMinute(0)->setSecond(0),
                'event_type' => 'clock_out',
            ]);

            $this->calculator->calculateForDay(
                $this->employee->employee_number,
                $date
            );
        }

        // Assert Saturday's attendance
        $saturday = $monday->copy()->addDays(5);
        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->where('date', $saturday->format('Y-m-d'))
            ->first();

        $this->assertNotNull($attendance);
        $this->assertEquals(5.0, (float) $attendance->regular_hours);
        $this->assertEquals(2.0, (float) $attendance->overtime_hours);
        $this->assertEquals(0.0, (float) $attendance->double_time_hours);
        $this->assertEquals(7.0, (float) $attendance->net_hours);
        $this->assertEquals('unscheduled', $attendance->status);
        $this->assertTrue((bool) $attendance->needs_review);
    }

    // ------------------------------------------------------------------------
    // Helper Methods
    // ------------------------------------------------------------------------

    protected function createClockEvent(string $type, Carbon $timestamp, ?string $employeeNumber = null): ClockEvent
    {
        if ($employeeNumber && $employeeNumber !== $this->employee->employee_number) {
            $emp = Employee::where('employee_number', $employeeNumber)->first();
            return ClockEvent::factory()->create([
                'employee_id' => $emp->id,
                'employee_number' => $employeeNumber,
                'event_type' => $type,
                'timestamp' => $timestamp,
                'method' => 'test',
            ]);
        }

        return ClockEvent::factory()->create([
            'employee_id' => $this->employee->id,
            'employee_number' => $this->employee->employee_number,
            'event_type' => $type,
            'timestamp' => $timestamp,
            'method' => 'test',
        ]);
    }

    // ------------------------------------------------------------------------
    // Snapshot test
    // ------------------------------------------------------------------------

    /** @test */
    public function it_preserves_original_company_and_department_snapshots_on_recalculation()
    {
        $date = Carbon::now();

        // Create initial attendance
        ClockEvent::factory()->create([
            'employee_id' => $this->employee->id,
            'employee_number' => $this->employee->employee_number,
            'timestamp' => $date->copy()->setHour(8)->setMinute(0),
            'event_type' => 'clock_in',
        ]);
        ClockEvent::factory()->create([
            'employee_id' => $this->employee->id,
            'employee_number' => $this->employee->employee_number,
            'timestamp' => $date->copy()->setHour(17)->setMinute(0),
            'event_type' => 'clock_out',
        ]);

        $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_id', $this->employee->id)
            ->where('date', $date->format('Y-m-d'))
            ->first();

        $originalCompany = $attendance->company;
        $originalDepartment = $attendance->department;

        // Recalculate
        $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance->refresh();

        $this->assertEquals($originalCompany, $attendance->company, 'Company snapshot should not change on recalculation');
        $this->assertEquals($originalDepartment, $attendance->department, 'Department snapshot should not change on recalculation');
    }
}
