<?php

namespace App\Modules\Holiday\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * Curated holiday templates per country/region.
 *
 * Used by the "Use Country Template" option in the batch creation
 * wizard. Templates define the month/day (or recurrence rule for
 * floating holidays) and default pay rate/type for each holiday.
 *
 * The actual date for a given target year is computed at read time
 * via computeDateForYear().
 */
class HolidayTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'holiday_templates';

    protected $fillable = [
        'country_code',
        'region_code',
        'name',
        'month',
        'day',
        'recurrence_rule',
        'holiday_type',
        'default_pay_rate',
        'is_paid',
        'is_active',
    ];

    protected $casts = [
        'month'            => 'integer',
        'day'              => 'integer',
        'default_pay_rate' => 'decimal:2',
        'is_paid'          => 'boolean',
        'is_active'        => 'boolean',
    ];

    protected $attributes = [
        'holiday_type'     => 'public',
        'default_pay_rate' => 1.0,
        'is_paid'          => true,
        'is_active'        => true,
    ];

    /**
     * Compute the actual date for a given target year.
     *
     * For fixed-date holidays (month + day), returns Carbon instance.
     * For floating holidays (recurrence_rule JSON), resolves the
     * rule to a specific date in the target year.
     *
     * @param int $targetYear
     * @return Carbon|null
     */
    public function computeDateForYear(int $targetYear): ?Carbon
    {
        // Floating holidays use recurrence_rule JSON
        if (!empty($this->recurrence_rule)) {
            return $this->resolveFloatingDate($targetYear);
        }

        // Fixed-date holidays
        if ($this->month && $this->day) {
            // Handle Feb 29 in non-leap years
            if ($this->month === 2 && $this->day === 29 && !Carbon::create($targetYear, 1, 1)->isLeapYear()) {
                return Carbon::create($targetYear, 2, 28);
            }
            return Carbon::create($targetYear, $this->month, $this->day);
        }

        return null;
    }

    /**
     * Resolve a floating holiday (e.g., "4th Thursday of November")
     * to a specific date in the target year.
     */
    protected function resolveFloatingDate(int $targetYear): ?Carbon
    {
        $rule = json_decode($this->recurrence_rule, true);
        if (!is_array($rule)) {
            return null;
        }

        $frequency = $rule['frequency'] ?? 'yearly';

        if ($frequency === 'yearly' && ($rule['yearly_mode'] ?? 'fixed') === 'floating') {
            $month  = (int) ($rule['floating_month'] ?? 1);
            $week   = $rule['floating_week'] ?? 'first';
            $day    = $rule['floating_day'] ?? 'monday';

            return $this->findNthWeekdayInMonth($targetYear, $month, $day, $week);
        }

        // Fallback: use month/day if available
        if ($this->month && $this->day) {
            return Carbon::create($targetYear, $this->month, $this->day);
        }

        return null;
    }

    /**
     * Find the Nth weekday of a month (e.g., 4th Thursday of November).
     */
    protected function findNthWeekdayInMonth(int $year, int $month, string $dayName, string $week): Carbon
    {
        $dayMap = [
            'monday'    => Carbon::MONDAY,
            'tuesday'   => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday'  => Carbon::THURSDAY,
            'friday'    => Carbon::FRIDAY,
            'saturday'  => Carbon::SATURDAY,
            'sunday'    => Carbon::SUNDAY,
        ];

        $targetDay = $dayMap[$dayName] ?? Carbon::MONDAY;

        if ($week === 'last') {
            // Start from the last day of the month and go backward
            $date = Carbon::create($year, $month, 1)->endOfMonth();
            while ($date->dayOfWeek !== $targetDay) {
                $date->subDay();
            }
            return $date;
        }

        $weekMap = ['first' => 1, 'second' => 2, 'third' => 3, 'fourth' => 4];
        $nth = $weekMap[$week] ?? 1;

        $date = Carbon::create($year, $month, 1);
        $count = 0;

        while ($date->month === $month) {
            if ($date->dayOfWeek === $targetDay) {
                $count++;
                if ($count === $nth) {
                    return $date;
                }
            }
            $date->addDay();
        }

        // Fallback: return the first occurrence
        return Carbon::create($year, $month, 1);
    }
}
