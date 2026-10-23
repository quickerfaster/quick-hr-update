<?php

namespace App\Modules\Holiday\Services;

use App\Modules\Holiday\Models\Holiday;
use App\Modules\Holiday\Models\HolidayCalendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Handles automatic year rollover for recurring holidays.
 *
 * On Dec 1 each year (or when manually triggered), this service copies
 * all recurring holidays (is_recurring = true) from active calendars
 * into the next year. Date-based holidays preserve their month/day;
 * only the year component is updated.
 *
 * The operation is idempotent — running it multiple times for the same
 * target year will not create duplicates.
 */
class HolidayYearRollover
{
    /**
     * Roll over recurring holidays to the target year.
     *
     * @param int|null $sourceYear  Source year (default: current year)
     * @param int|null $targetYear  Target year (default: current year + 1)
     * @return array{calendars_processed: int, holidays_copied: int, holidays_skipped: int, errors: array}
     */
    public function rollover(?int $sourceYear = null, ?int $targetYear = null): array
    {
        $sourceYear = $sourceYear ?? (int) now()->year;
        $targetYear = $targetYear ?? $sourceYear + 1;

        Log::info("Starting holiday year rollover", [
            'source_year' => $sourceYear,
            'target_year' => $targetYear,
        ]);

        // Find all active calendars
        $calendars = HolidayCalendar::where('is_active', true)->get();

        if ($calendars->isEmpty()) {
            Log::warning('Holiday year rollover: No active calendars found.');
            return [
                'calendars_processed' => 0,
                'holidays_copied'     => 0,
                'holidays_skipped'    => 0,
                'errors'              => [],
            ];
        }

        $totalCopied  = 0;
        $totalSkipped = 0;
        $errors       = [];

        foreach ($calendars as $calendar) {
            try {
                $result = $this->rolloverCalendar($calendar, $sourceYear, $targetYear);
                $totalCopied  += $result['copied'];
                $totalSkipped += $result['skipped'];
            } catch (\Throwable $e) {
                $errors[] = [
                    'calendar_id'   => $calendar->id,
                    'calendar_name' => $calendar->name,
                    'error'         => $e->getMessage(),
                ];
                Log::error("Holiday rollover failed for calendar #{$calendar->id} '{$calendar->name}'", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        Log::info("Holiday year rollover complete", [
            'source_year'         => $sourceYear,
            'target_year'         => $targetYear,
            'calendars_processed' => $calendars->count(),
            'holidays_copied'     => $totalCopied,
            'holidays_skipped'    => $totalSkipped,
            'errors'              => count($errors),
        ]);

        return [
            'calendars_processed' => $calendars->count(),
            'holidays_copied'     => $totalCopied,
            'holidays_skipped'    => $totalSkipped,
            'errors'              => $errors,
        ];
    }

    /**
     * Roll over recurring holidays for a single calendar.
     *
     * @param HolidayCalendar $calendar
     * @param int             $sourceYear
     * @param int             $targetYear
     * @return array{copied: int, skipped: int}
     */
    protected function rolloverCalendar(HolidayCalendar $calendar, int $sourceYear, int $targetYear): array
    {
        // Find recurring holidays in the source year for this calendar
        $sourceHolidays = Holiday::where('calendar_id', $calendar->id)
            ->where('is_recurring', true)
            ->where('is_active', true)
            ->where('year', $sourceYear)
            ->get();

        if ($sourceHolidays->isEmpty()) {
            Log::info("No recurring holidays to roll over for calendar '{$calendar->name}' (#{$calendar->id}) from {$sourceYear}.");
            return ['copied' => 0, 'skipped' => 0];
        }

        $copied  = 0;
        $skipped = 0;

        DB::transaction(function () use ($calendar, $sourceHolidays, $sourceYear, $targetYear, &$copied, &$skipped) {
            foreach ($sourceHolidays as $holiday) {
                // Compute the target date: same month/day, new year
                $originalDate = $holiday->date;
                $targetDate = $this->shiftYear($originalDate, $targetYear);

                // Compute target observed_date if present
                $targetObservedDate = null;
                if ($holiday->observed_date) {
                    $targetObservedDate = $this->shiftYear($holiday->observed_date, $targetYear);
                }

                // Idempotency check: does a holiday with the same date and name
                // already exist in the target year for this calendar?
                $exists = Holiday::where('calendar_id', $calendar->id)
                    ->where('date', $targetDate)
                    ->where('name', $holiday->name)
                    ->where('year', $targetYear)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    Log::info("Skipping duplicate holiday '{$holiday->name}' for {$targetDate->toDateString()} in calendar '{$calendar->name}'.");
                    continue;
                }

                // Copy the holiday to the target year
                $newHoliday = $holiday->replicate();
                $newHoliday->date          = $targetDate;
                $newHoliday->observed_date = $targetObservedDate;
                $newHoliday->year          = $targetYear;
                $newHoliday->generated_from_template = false;
                $newHoliday->override_id   = null;
                $newHoliday->last_synced_at = null;
                $newHoliday->save();

                $copied++;

                Log::info("Rolled over holiday '{$holiday->name}' to {$targetDate->toDateString()} in calendar '{$calendar->name}'", [
                    'source_holiday_id' => $holiday->id,
                    'new_holiday_id'    => $newHoliday->id,
                ]);
            }
        });

        // Update the calendar's holiday_count
        $count = Holiday::where('calendar_id', $calendar->id)
            ->where('year', $targetYear)
            ->where('is_active', true)
            ->count();
        $calendar->update([
            'holiday_count' => $count,
            'last_updated'  => now(),
        ]);

        Log::info("Calendar '{$calendar->name}' (#{$calendar->id}): copied {$copied}, skipped {$skipped} holidays for {$targetYear}.");

        return ['copied' => $copied, 'skipped' => $skipped];
    }

    /**
     * Shift a date to a new year while preserving month and day.
     *
     * Handles Feb 29 by clamping to Feb 28 in non-leap years.
     *
     * @param Carbon $date
     * @param int    $targetYear
     * @return Carbon
     */
    protected function shiftYear(Carbon $date, int $targetYear): Carbon
    {
        $month = $date->month;
        $day   = $date->day;

        // Handle Feb 29 in non-leap years
        if ($month === 2 && $day === 29 && !Carbon::create($targetYear, 1, 1)->isLeapYear()) {
            $day = 28;
        }

        return Carbon::create($targetYear, $month, $day);
    }
}
