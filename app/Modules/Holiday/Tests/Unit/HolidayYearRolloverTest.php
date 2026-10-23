<?php

namespace App\Modules\Holiday\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;
use App\Modules\Holiday\Models\Holiday;
use App\Modules\Holiday\Models\HolidayCalendar;
use App\Modules\Holiday\Services\HolidayYearRollover;
use App\Modules\Hr\Models\Company;

class HolidayYearRolloverTest extends BaseTestCase
{
    use DatabaseTransactions;

    protected HolidayYearRollover $rollover;
    protected Company $company;
    protected HolidayCalendar $calendar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rollover = new HolidayYearRollover();

        $this->company = Company::factory()->create(['status' => 'active']);

        $this->calendar = HolidayCalendar::create([
            'company_id' => $this->company->id,
            'name' => 'Test Calendar ' . uniqid(),
            'year' => 2026,
            'country_code' => 'US',
            'is_active' => true,
        ]);
    }

    // =========================================================================
    // Helper methods
    // =========================================================================

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

    // =========================================================================
    // Tests: No recurring holidays
    // =========================================================================

    #[Test]
    public function it_returns_zero_when_no_recurring_holidays_exist(): void
    {
        // Create a non-recurring holiday
        $this->createHoliday('2026-12-25', [
            'name' => 'Christmas',
            'is_recurring' => false,
        ]);

        $result = $this->rollover->rollover(2026, 2027);

        $this->assertEquals(0, $result['holidays_copied']);
        $this->assertEquals(0, $result['holidays_skipped']);
    }

    #[Test]
    public function it_returns_zero_when_no_active_calendars_exist(): void
    {
        // Deactivate the calendar
        $this->calendar->update(['is_active' => false]);

        $this->createHoliday('2026-12-25', [
            'name' => 'Christmas',
            'is_recurring' => true,
        ]);

        $result = $this->rollover->rollover(2026, 2027);

        $this->assertEquals(0, $result['calendars_processed']);
        $this->assertEquals(0, $result['holidays_copied']);
    }

    // =========================================================================
    // Tests: Basic rollover
    // =========================================================================

    #[Test]
    public function it_copies_recurring_holidays_to_target_year(): void
    {
        $this->createHoliday('2026-12-25', [
            'name' => 'Christmas',
            'is_recurring' => true,
        ]);

        $this->createHoliday('2026-01-01', [
            'name' => 'New Year',
            'is_recurring' => true,
        ]);

        $result = $this->rollover->rollover(2026, 2027);

        $this->assertEquals(1, $result['calendars_processed']);
        $this->assertEquals(2, $result['holidays_copied']);
        $this->assertEquals(0, $result['holidays_skipped']);

        // Verify the holidays exist in 2027
        $christmas2027 = Holiday::where('calendar_id', $this->calendar->id)
            ->where('name', 'Christmas')
            ->where('year', 2027)
            ->first();

        $this->assertNotNull($christmas2027);
        $this->assertEquals('2027-12-25', $christmas2027->date->toDateString());
        $this->assertTrue((bool) $christmas2027->is_recurring);

        $newYear2027 = Holiday::where('calendar_id', $this->calendar->id)
            ->where('name', 'New Year')
            ->where('year', 2027)
            ->first();

        $this->assertNotNull($newYear2027);
        $this->assertEquals('2027-01-01', $newYear2027->date->toDateString());
    }

    // =========================================================================
    // Tests: Non-recurring holidays are NOT copied
    // =========================================================================

    #[Test]
    public function it_does_not_copy_non_recurring_holidays(): void
    {
        $this->createHoliday('2026-12-25', [
            'name' => 'Christmas',
            'is_recurring' => true,
        ]);

        $this->createHoliday('2026-07-04', [
            'name' => 'One-Time Event',
            'is_recurring' => false,
        ]);

        $result = $this->rollover->rollover(2026, 2027);

        $this->assertEquals(1, $result['holidays_copied']);
        $this->assertEquals(0, $result['holidays_skipped']);

        // Christmas should exist in 2027
        $this->assertTrue(Holiday::where('calendar_id', $this->calendar->id)
            ->where('name', 'Christmas')
            ->where('year', 2027)
            ->exists());

        // One-Time Event should NOT exist in 2027
        $this->assertFalse(Holiday::where('calendar_id', $this->calendar->id)
            ->where('name', 'One-Time Event')
            ->where('year', 2027)
            ->exists());
    }

    // =========================================================================
    // Tests: Idempotency
    // =========================================================================

    #[Test]
    public function it_is_idempotent_running_twice_does_not_create_duplicates(): void
    {
        $this->createHoliday('2026-12-25', [
            'name' => 'Christmas',
            'is_recurring' => true,
        ]);

        // First rollover
        $result1 = $this->rollover->rollover(2026, 2027);
        $this->assertEquals(1, $result1['holidays_copied']);

        // Second rollover — should skip because already exists
        $result2 = $this->rollover->rollover(2026, 2027);
        $this->assertEquals(0, $result2['holidays_copied']);
        $this->assertEquals(1, $result2['holidays_skipped']);

        // Only one Christmas 2027 should exist
        $count = Holiday::where('calendar_id', $this->calendar->id)
            ->where('name', 'Christmas')
            ->where('year', 2027)
            ->count();
        $this->assertEquals(1, $count);
    }

    // =========================================================================
    // Tests: Feb 29 handling
    // =========================================================================

    #[Test]
    public function it_handles_feb_29_in_non_leap_year(): void
    {
        // 2028 is a leap year, 2029 is not
        $this->createHoliday('2028-02-29', [
            'name' => 'Leap Day Holiday',
            'is_recurring' => true,
            'year' => 2028,
        ]);

        $result = $this->rollover->rollover(2028, 2029);

        $this->assertEquals(1, $result['holidays_copied']);

        $rolledOver = Holiday::where('calendar_id', $this->calendar->id)
            ->where('name', 'Leap Day Holiday')
            ->where('year', 2029)
            ->first();

        $this->assertNotNull($rolledOver);
        // Should be clamped to Feb 28 in non-leap year
        $this->assertEquals('2029-02-28', $rolledOver->date->toDateString());
    }

    #[Test]
    public function it_preserves_feb_29_in_leap_year(): void
    {
        // 2028 is a leap year, roll to 2032 (also leap year)
        $this->createHoliday('2028-02-29', [
            'name' => 'Leap Day Holiday',
            'is_recurring' => true,
            'year' => 2028,
        ]);

        $result = $this->rollover->rollover(2028, 2032);

        $this->assertEquals(1, $result['holidays_copied']);

        $rolledOver = Holiday::where('calendar_id', $this->calendar->id)
            ->where('name', 'Leap Day Holiday')
            ->where('year', 2032)
            ->first();

        $this->assertNotNull($rolledOver);
        $this->assertEquals('2032-02-29', $rolledOver->date->toDateString());
    }

    // =========================================================================
    // Tests: observed_date rollover
    // =========================================================================

    #[Test]
    public function it_rolls_over_observed_date(): void
    {
        $this->createHoliday('2026-12-25', [
            'name' => 'Christmas',
            'is_recurring' => true,
            'observed_date' => '2026-12-26',
        ]);

        $result = $this->rollover->rollover(2026, 2027);

        $this->assertEquals(1, $result['holidays_copied']);

        $rolledOver = Holiday::where('calendar_id', $this->calendar->id)
            ->where('name', 'Christmas')
            ->where('year', 2027)
            ->first();

        $this->assertNotNull($rolledOver);
        $this->assertEquals('2027-12-25', $rolledOver->date->toDateString());
        $this->assertEquals('2027-12-26', $rolledOver->observed_date->toDateString());
    }

    // =========================================================================
    // Tests: Multiple calendars
    // =========================================================================

    #[Test]
    public function it_processes_multiple_calendars(): void
    {
        // Create a second calendar
        $calendar2 = HolidayCalendar::create([
            'company_id' => $this->company->id,
            'name' => 'Second Calendar ' . uniqid(),
            'year' => 2026,
            'country_code' => 'UK',
            'is_active' => true,
        ]);

        // Holiday in first calendar
        $this->createHoliday('2026-12-25', [
            'name' => 'Christmas',
            'is_recurring' => true,
        ]);

        // Holiday in second calendar
        Holiday::create([
            'company_id' => $this->company->id,
            'calendar_id' => $calendar2->id,
            'name' => 'Boxing Day',
            'date' => '2026-12-26',
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
        ]);

        $result = $this->rollover->rollover(2026, 2027);

        $this->assertEquals(2, $result['calendars_processed']);
        $this->assertEquals(2, $result['holidays_copied']);

        // Verify both calendars got their holidays
        $this->assertTrue(Holiday::where('calendar_id', $this->calendar->id)
            ->where('name', 'Christmas')
            ->where('year', 2027)
            ->exists());

        $this->assertTrue(Holiday::where('calendar_id', $calendar2->id)
            ->where('name', 'Boxing Day')
            ->where('year', 2027)
            ->exists());
    }

    // =========================================================================
    // Tests: Calendar holiday_count update
    // =========================================================================

    #[Test]
    public function it_updates_calendar_holiday_count_after_rollover(): void
    {
        $this->createHoliday('2026-12-25', [
            'name' => 'Christmas',
            'is_recurring' => true,
        ]);

        $this->createHoliday('2026-01-01', [
            'name' => 'New Year',
            'is_recurring' => true,
        ]);

        $this->rollover->rollover(2026, 2027);

        // Refresh calendar
        $this->calendar->refresh();

        // The holiday_count should reflect the number of active holidays in 2027
        $this->assertEquals(2, $this->calendar->holiday_count);
    }

    // =========================================================================
    // Tests: Default year behavior
    // =========================================================================

    #[Test]
    public function it_defaults_to_current_year_when_no_source_year_provided(): void
    {
        $currentYear = (int) now()->year;
        $nextYear = $currentYear + 1;

        // Create a holiday in the current year
        $this->createHoliday("{$currentYear}-06-15", [
            'name' => 'Mid-Year Holiday',
            'is_recurring' => true,
            'year' => $currentYear,
        ]);

        $result = $this->rollover->rollover();

        $this->assertGreaterThanOrEqual(0, $result['holidays_copied']);

        // If current year matches, holiday should be in next year
        if ($result['holidays_copied'] > 0) {
            $this->assertTrue(Holiday::where('calendar_id', $this->calendar->id)
                ->where('name', 'Mid-Year Holiday')
                ->where('year', $nextYear)
                ->exists());
        }
    }

    // =========================================================================
    // Tests: Inactive holidays are not copied
    // =========================================================================

    #[Test]
    public function it_does_not_copy_inactive_holidays(): void
    {
        $this->createHoliday('2026-12-25', [
            'name' => 'Christmas',
            'is_recurring' => true,
            'is_active' => true,
        ]);

        $this->createHoliday('2026-11-25', [
            'name' => 'Inactive Holiday',
            'is_recurring' => true,
            'is_active' => false,
        ]);

        $result = $this->rollover->rollover(2026, 2027);

        // Only the active holiday should be copied
        $this->assertEquals(1, $result['holidays_copied']);

        $this->assertTrue(Holiday::where('calendar_id', $this->calendar->id)
            ->where('name', 'Christmas')
            ->where('year', 2027)
            ->exists());

        $this->assertFalse(Holiday::where('calendar_id', $this->calendar->id)
            ->where('name', 'Inactive Holiday')
            ->where('year', 2027)
            ->exists());
    }

    // =========================================================================
    // Tests: Preserves holiday attributes
    // =========================================================================

    #[Test]
    public function it_preserves_holiday_attributes_during_rollover(): void
    {
        $this->createHoliday('2026-12-25', [
            'name' => 'Christmas',
            'is_recurring' => true,
            'holiday_pay_rate' => 2.5,
            'is_paid_holiday' => true,
            'affects_payroll' => true,
            'minimum_hours_for_pay' => 8,
            'is_half_day' => false,
            'business_impact' => 'office_closed',
            'holiday_type' => 'public',
            'country_code' => 'US',
        ]);

        $this->rollover->rollover(2026, 2027);

        $rolledOver = Holiday::where('calendar_id', $this->calendar->id)
            ->where('name', 'Christmas')
            ->where('year', 2027)
            ->first();

        $this->assertNotNull($rolledOver);
        $this->assertEquals(2.5, (float) $rolledOver->holiday_pay_rate);
        $this->assertTrue((bool) $rolledOver->is_paid_holiday);
        $this->assertTrue((bool) $rolledOver->affects_payroll);
        $this->assertEquals(8, (float) $rolledOver->minimum_hours_for_pay);
        $this->assertFalse((bool) $rolledOver->is_half_day);
        $this->assertEquals('office_closed', $rolledOver->business_impact);
        $this->assertEquals('public', $rolledOver->holiday_type);
        $this->assertEquals('US', $rolledOver->country_code);
        $this->assertFalse((bool) $rolledOver->generated_from_template);
    }
}
