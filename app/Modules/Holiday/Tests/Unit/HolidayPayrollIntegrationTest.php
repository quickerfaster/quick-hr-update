<?php

namespace App\Modules\Holiday\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;
use App\Modules\Holiday\Models\Holiday;
use App\Modules\Holiday\Models\HolidayCalendar;
use App\Modules\Holiday\Services\HolidayPayrollIntegration;
use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Payroll\Models\PayrollRunAdjustment;
use App\Modules\Payroll\Models\PaySchedule;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\EmployeePosition;
use App\Modules\Hr\Models\Company;
use App\Modules\Hr\Models\Location;
use App\Modules\Hr\Models\Department;
use App\Modules\Hr\Models\JobTitle;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Models\WorkPattern;

class HolidayPayrollIntegrationTest extends BaseTestCase
{
    use DatabaseTransactions;

    protected HolidayPayrollIntegration $integration;
    protected Company $company;
    protected PayrollRun $payrollRun;
    protected HolidayCalendar $calendar;
    protected Carbon $periodStart;
    protected Carbon $periodEnd;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integration = new HolidayPayrollIntegration();

        $this->company = Company::factory()->create(['status' => 'active']);

        $this->calendar = HolidayCalendar::create([
            'company_id' => $this->company->id,
            'name' => 'Test Calendar ' . uniqid(),
            'year' => 2026,
            'country_code' => 'US',
            'is_active' => true,
        ]);

        $paySchedule = PaySchedule::create([
            'name' => 'Test Monthly ' . uniqid(),
            'code' => 'TEST-MON-' . uniqid(),
            'frequency' => 'Monthly',
            'first_period_start_date' => '2026-01-01',
            'next_pay_date' => '2026-01-31',
            'payment_delay_days' => 0,
            'country_code' => 'US',
            'currency_code' => 'USD',
            'timezone' => 'America/New_York',
            'is_active' => true,
        ]);

        $this->periodStart = Carbon::parse('2026-01-01');
        $this->periodEnd = Carbon::parse('2026-01-31');

        $this->payrollRun = PayrollRun::create([
            'title' => 'Test January 2026 ' . uniqid(),
            'pay_schedule_id' => $paySchedule->id,
            'period_start' => $this->periodStart,
            'period_end' => $this->periodEnd,
            'status' => 'processing',
            'calculation_status' => 'pending',
        ]);
    }

    // =========================================================================
    // Helper methods
    // =========================================================================

    protected function createEmployeeWithPosition(array $positionOverrides = []): array
    {
        $employee = Employee::create([
            'employee_number' => 'EMP-TEST-' . uniqid(),
            'first_name' => 'Test',
            'last_name' => 'Employee',
            'email' => 'test' . uniqid() . '@example.com',
            'company_id' => $this->company->id,
            'hire_date' => '2026-01-01',
        ]);

        $location = Location::factory()->create([
            'company_id' => $this->company->id,
            'country_code' => 'US',
        ]);

        $department = Department::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $jobTitle = JobTitle::factory()->create();

        $position = EmployeePosition::create(array_merge([
            'employee_id' => $employee->id,
            'job_title_id' => $jobTitle->id,
            'department_id' => $department->id,
            'location_id' => $location->id,
            'pay_type' => 'salaried_full',
            'base_salary' => 5000.00,
            'hourly_rate' => 0,
            'pay_frequency' => 'Monthly',
            'employment_status' => 'Active',
            'salary_currency' => 'USD',
        ], $positionOverrides));

        $position->load('employee');

        return ['employee' => $employee, 'position' => $position];
    }

    protected function createHoliday(string $date, array $overrides = []): Holiday
    {
        return Holiday::create(array_merge([
            'company_id' => $this->company->id,
            'calendar_id' => $this->calendar->id,
            'name' => 'Test Holiday ' . uniqid(),
            'date' => $date,
            'is_recurring' => true,
            'is_paid_holiday' => true,
            'affects_payroll' => true,
            'holiday_pay_rate' => 1.0,
            'minimum_hours_for_pay' => 8,
            'is_half_day' => false,
            'is_active' => true,
            'year' => 2026,
            'business_impact' => 'office_closed',
            'holiday_type' => 'company',
            'eligible_employee_types' => json_encode(['full_time', 'part_time']),
        ], $overrides));
    }

    protected function createHolidayAttendance(Employee $employee, string $date, float $creditedHours = 8.0): Attendance
    {
        return Attendance::create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id,
            'date' => $date,
            'status' => 'holiday',
            'net_hours' => $creditedHours,
            'regular_hours' => $creditedHours,
            'overtime_hours' => 0,
            'double_time_hours' => 0,
            'is_approved' => true,
            'is_paid_absence' => true,
            'notes' => 'Company Holiday',
            'needs_review' => false,
            'is_unplanned' => false,
            'calculation_method' => 'auto',
        ]);
    }

    // =========================================================================
    // Tests: No adjustments when no holiday attendance
    // =========================================================================

    #[Test]
    public function it_returns_zero_when_no_holiday_attendance_exists(): void
    {
        $count = $this->integration->createAdjustmentsForRun($this->payrollRun);
        $this->assertEquals(0, $count);
    }

    // =========================================================================
    // Tests: No premium when pay rate is 1.0
    // =========================================================================

    #[Test]
    public function it_creates_no_adjustment_when_holiday_pay_rate_is_one(): void
    {
        $data = $this->createEmployeeWithPosition([
            'pay_type' => 'hourly',
            'hourly_rate' => 25.00,
            'base_salary' => 0,
        ]);

        $holiday = $this->createHoliday('2026-01-15', [
            'holiday_pay_rate' => 1.0,
        ]);

        $this->createHolidayAttendance($data['employee'], '2026-01-15', 8.0);

        $count = $this->integration->createAdjustmentsForRun($this->payrollRun);
        $this->assertEquals(0, $count, 'No premium should be created when pay rate is 1.0x');
    }

    // =========================================================================
    // Tests: Hourly employee holiday pay premium
    // =========================================================================

    #[Test]
    public function it_calculates_holiday_premium_for_hourly_employee(): void
    {
        $data = $this->createEmployeeWithPosition([
            'pay_type' => 'hourly',
            'hourly_rate' => 25.00,
            'base_salary' => 0,
        ]);

        // 2.0x pay rate → premium = 8h × $25 × (2.0 - 1.0) = 8 × 25 × 1.0 = $200
        $this->createHoliday('2026-01-15', [
            'holiday_pay_rate' => 2.0,
        ]);

        $this->createHolidayAttendance($data['employee'], '2026-01-15', 8.0);

        $count = $this->integration->createAdjustmentsForRun($this->payrollRun);
        $this->assertEquals(1, $count);

        $adjustment = PayrollRunAdjustment::where('payroll_run_id', $this->payrollRun->id)
            ->where('source_type', 'holiday_pay')
            ->first();

        $this->assertNotNull($adjustment);
        $this->assertEquals('earning', $adjustment->type);
        $this->assertEquals(200.00, (float) $adjustment->amount);
        $this->assertEquals($data['employee']->id, $adjustment->employee_id);
    }

    #[Test]
    public function it_calculates_holiday_premium_for_hourly_employee_with_3x_rate(): void
    {
        $data = $this->createEmployeeWithPosition([
            'pay_type' => 'hourly',
            'hourly_rate' => 20.00,
            'base_salary' => 0,
        ]);

        // 3.0x pay rate → premium = 8h × $20 × (3.0 - 1.0) = 8 × 20 × 2.0 = $320
        $this->createHoliday('2026-01-20', [
            'holiday_pay_rate' => 3.0,
        ]);

        $this->createHolidayAttendance($data['employee'], '2026-01-20', 8.0);

        $count = $this->integration->createAdjustmentsForRun($this->payrollRun);
        $this->assertEquals(1, $count);

        $adjustment = PayrollRunAdjustment::where('payroll_run_id', $this->payrollRun->id)
            ->where('source_type', 'holiday_pay')
            ->first();

        $this->assertEquals(320.00, (float) $adjustment->amount);
    }

    #[Test]
    public function it_calculates_holiday_premium_for_hourly_employee_with_half_day(): void
    {
        $data = $this->createEmployeeWithPosition([
            'pay_type' => 'hourly',
            'hourly_rate' => 30.00,
            'base_salary' => 0,
        ]);

        // Half-day holiday: 4h credited
        // 2.0x pay rate → premium = 4h × $30 × (2.0 - 1.0) = 4 × 30 × 1.0 = $120
        $this->createHoliday('2026-01-10', [
            'holiday_pay_rate' => 2.0,
            'is_half_day' => true,
            'minimum_hours_for_pay' => 8,
        ]);

        $this->createHolidayAttendance($data['employee'], '2026-01-10', 4.0);

        $count = $this->integration->createAdjustmentsForRun($this->payrollRun);
        $this->assertEquals(1, $count);

        $adjustment = PayrollRunAdjustment::where('payroll_run_id', $this->payrollRun->id)
            ->where('source_type', 'holiday_pay')
            ->first();

        $this->assertEquals(120.00, (float) $adjustment->amount);
    }

    // =========================================================================
    // Tests: Salaried daily employee holiday pay premium
    // =========================================================================

    #[Test]
    public function it_calculates_holiday_premium_for_salaried_daily_employee(): void
    {
        // Create a shift first (required by WorkPattern)
        $shift = \App\Modules\Attendance\Models\Shift::create([
            'name' => 'Standard Shift ' . uniqid(),
            'code' => 'STD-SHIFT-' . uniqid(),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'duration_hours' => 8,
            'is_active' => true,
        ]);

        // Create a default work pattern (Mon-Fri) so daily rate can be computed
        WorkPattern::create([
            'name' => 'Standard Mon-Fri',
            'code' => 'STD-MF-' . uniqid(),
            'shift_id' => $shift->id,
            'applicable_days' => '1,2,3,4,5',
            'is_default' => true,
            'is_active' => true,
            'effective_date' => '2026-01-01',
        ]);

        $data = $this->createEmployeeWithPosition([
            'pay_type' => 'salaried_daily',
            'hourly_rate' => 0,
            'base_salary' => 3100.00, // 31 days in Jan, ~22 workdays → daily rate ≈ 140.91
        ]);

        // 2.0x pay rate → premium = daily_rate × (2.0 - 1.0) = daily_rate × 1.0
        $this->createHoliday('2026-01-15', [
            'holiday_pay_rate' => 2.0,
        ]);

        $this->createHolidayAttendance($data['employee'], '2026-01-15', 8.0);

        $count = $this->integration->createAdjustmentsForRun($this->payrollRun);
        $this->assertEquals(1, $count);

        $adjustment = PayrollRunAdjustment::where('payroll_run_id', $this->payrollRun->id)
            ->where('source_type', 'holiday_pay')
            ->first();

        $this->assertNotNull($adjustment);
        $this->assertEquals('earning', $adjustment->type);
        // Jan 2026 has 22 weekdays (Mon-Fri). 3100 / 22 ≈ 140.91
        $this->assertEquals(140.91, (float) $adjustment->amount);
    }

    // =========================================================================
    // Tests: Salaried full employee holiday pay premium
    // =========================================================================

    #[Test]
    public function it_calculates_holiday_premium_for_salaried_full_employee(): void
    {
        $data = $this->createEmployeeWithPosition([
            'pay_type' => 'salaried_full',
            'hourly_rate' => 0,
            'base_salary' => 6200.00,
        ]);

        // 2.0x pay rate → premium = (base_salary / 31 days) × (2.0 - 1.0) = 200 × 1.0 = $200
        $this->createHoliday('2026-01-15', [
            'holiday_pay_rate' => 2.0,
        ]);

        $this->createHolidayAttendance($data['employee'], '2026-01-15', 8.0);

        $count = $this->integration->createAdjustmentsForRun($this->payrollRun);
        $this->assertEquals(1, $count);

        $adjustment = PayrollRunAdjustment::where('payroll_run_id', $this->payrollRun->id)
            ->where('source_type', 'holiday_pay')
            ->first();

        $this->assertNotNull($adjustment);
        // 6200 / 31 = 200.00
        $this->assertEquals(200.00, (float) $adjustment->amount);
    }

    // =========================================================================
    // Tests: Idempotency
    // =========================================================================

    #[Test]
    public function it_is_idempotent_running_twice_does_not_create_duplicates(): void
    {
        $data = $this->createEmployeeWithPosition([
            'pay_type' => 'hourly',
            'hourly_rate' => 25.00,
            'base_salary' => 0,
        ]);

        $this->createHoliday('2026-01-15', [
            'holiday_pay_rate' => 2.0,
        ]);

        $this->createHolidayAttendance($data['employee'], '2026-01-15', 8.0);

        // First run
        $count1 = $this->integration->createAdjustmentsForRun($this->payrollRun);
        $this->assertEquals(1, $count1);

        // Second run — should skip because adjustment already exists
        $count2 = $this->integration->createAdjustmentsForRun($this->payrollRun);
        $this->assertEquals(0, $count2);

        // Only one adjustment should exist
        $total = PayrollRunAdjustment::where('payroll_run_id', $this->payrollRun->id)
            ->where('source_type', 'holiday_pay')
            ->count();
        $this->assertEquals(1, $total);
    }

    // =========================================================================
    // Tests: Multiple holidays in period
    // =========================================================================

    #[Test]
    public function it_creates_adjustments_for_multiple_holidays_in_period(): void
    {
        $data = $this->createEmployeeWithPosition([
            'pay_type' => 'hourly',
            'hourly_rate' => 25.00,
            'base_salary' => 0,
        ]);

        // Two holidays in the period
        $this->createHoliday('2026-01-01', [
            'name' => 'New Year',
            'holiday_pay_rate' => 2.0,
        ]);
        $this->createHoliday('2026-01-15', [
            'name' => 'Mid-Month Holiday',
            'holiday_pay_rate' => 2.5,
        ]);

        $this->createHolidayAttendance($data['employee'], '2026-01-01', 8.0);
        $this->createHolidayAttendance($data['employee'], '2026-01-15', 8.0);

        $count = $this->integration->createAdjustmentsForRun($this->payrollRun);
        $this->assertEquals(2, $count);

        $adjustments = PayrollRunAdjustment::where('payroll_run_id', $this->payrollRun->id)
            ->where('source_type', 'holiday_pay')
            ->get();

        $this->assertCount(2, $adjustments);

        // New Year: 8h × $25 × (2.0 - 1.0) = $200
        // Mid-Month: 8h × $25 × (2.5 - 1.0) = $300
        $totalPremium = $adjustments->sum('amount');
        $this->assertEquals(500.00, (float) $totalPremium);
    }

    // =========================================================================
    // Tests: Holiday with affects_payroll = false
    // =========================================================================

    #[Test]
    public function it_skips_holidays_where_affects_payroll_is_false(): void
    {
        $data = $this->createEmployeeWithPosition([
            'pay_type' => 'hourly',
            'hourly_rate' => 25.00,
            'base_salary' => 0,
        ]);

        $this->createHoliday('2026-01-15', [
            'holiday_pay_rate' => 2.0,
            'affects_payroll' => false,
        ]);

        $this->createHolidayAttendance($data['employee'], '2026-01-15', 8.0);

        $count = $this->integration->createAdjustmentsForRun($this->payrollRun);
        $this->assertEquals(0, $count, 'Holidays with affects_payroll=false should be skipped');
    }

    // =========================================================================
    // Tests: removeAdjustmentsForRun
    // =========================================================================

    #[Test]
    public function it_removes_all_holiday_adjustments_for_a_run(): void
    {
        $data = $this->createEmployeeWithPosition([
            'pay_type' => 'hourly',
            'hourly_rate' => 25.00,
            'base_salary' => 0,
        ]);

        $this->createHoliday('2026-01-15', [
            'holiday_pay_rate' => 2.0,
        ]);

        $this->createHolidayAttendance($data['employee'], '2026-01-15', 8.0);

        // Create adjustments
        $this->integration->createAdjustmentsForRun($this->payrollRun);

        // Verify they exist
        $this->assertEquals(1, PayrollRunAdjustment::where('payroll_run_id', $this->payrollRun->id)
            ->where('source_type', 'holiday_pay')
            ->count());

        // Remove them
        $removed = $this->integration->removeAdjustmentsForRun($this->payrollRun);
        $this->assertEquals(1, $removed);

        // Verify they're gone
        $this->assertEquals(0, PayrollRunAdjustment::where('payroll_run_id', $this->payrollRun->id)
            ->where('source_type', 'holiday_pay')
            ->count());
    }

    // =========================================================================
    // Tests: Multiple employees
    // =========================================================================

    #[Test]
    public function it_creates_adjustments_for_multiple_employees(): void
    {
        $data1 = $this->createEmployeeWithPosition([
            'pay_type' => 'hourly',
            'hourly_rate' => 25.00,
            'base_salary' => 0,
        ]);

        $data2 = $this->createEmployeeWithPosition([
            'pay_type' => 'hourly',
            'hourly_rate' => 30.00,
            'base_salary' => 0,
        ]);

        $this->createHoliday('2026-01-15', [
            'holiday_pay_rate' => 2.0,
        ]);

        $this->createHolidayAttendance($data1['employee'], '2026-01-15', 8.0);
        $this->createHolidayAttendance($data2['employee'], '2026-01-15', 8.0);

        $count = $this->integration->createAdjustmentsForRun($this->payrollRun);
        $this->assertEquals(2, $count);

        // Employee 1: 8h × $25 × 1.0 = $200
        $adj1 = PayrollRunAdjustment::where('payroll_run_id', $this->payrollRun->id)
            ->where('employee_id', $data1['employee']->id)
            ->where('source_type', 'holiday_pay')
            ->first();
        $this->assertEquals(200.00, (float) $adj1->amount);

        // Employee 2: 8h × $30 × 1.0 = $240
        $adj2 = PayrollRunAdjustment::where('payroll_run_id', $this->payrollRun->id)
            ->where('employee_id', $data2['employee']->id)
            ->where('source_type', 'holiday_pay')
            ->first();
        $this->assertEquals(240.00, (float) $adj2->amount);
    }

    // =========================================================================
    // Tests: Adjustment label format
    // =========================================================================

    #[Test]
    public function it_formats_adjustment_label_with_holiday_name_and_rate(): void
    {
        $data = $this->createEmployeeWithPosition([
            'pay_type' => 'hourly',
            'hourly_rate' => 25.00,
            'base_salary' => 0,
        ]);

        $this->createHoliday('2026-01-15', [
            'name' => 'Christmas',
            'holiday_pay_rate' => 2.5,
        ]);

        $this->createHolidayAttendance($data['employee'], '2026-01-15', 8.0);

        $this->integration->createAdjustmentsForRun($this->payrollRun);

        $adjustment = PayrollRunAdjustment::where('payroll_run_id', $this->payrollRun->id)
            ->where('source_type', 'holiday_pay')
            ->first();

        $this->assertStringContainsString('Christmas', $adjustment->label);
        $this->assertStringContainsString('2.5', $adjustment->label);
        $this->assertStringContainsString('Holiday Pay Premium', $adjustment->label);
    }
}
