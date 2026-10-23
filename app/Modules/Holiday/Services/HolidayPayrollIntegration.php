<?php

namespace App\Modules\Holiday\Services;

use App\Modules\Holiday\Models\Holiday;
use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Payroll\Models\PayrollRunAdjustment;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Hr\Models\EmployeePosition;
use Illuminate\Support\Facades\Log;

/**
 * Integrates holiday pay rates with the Payroll module.
 *
 * When a payroll run covers a period containing company holidays,
 * this service creates PayrollRunAdjustment records for the holiday
 * pay premium (the extra amount above the base rate). The base pay
 * for holiday hours is already included via the attendance summary;
 * this adds the multiplier premium on top.
 *
 * Pattern mirrors LoanRepaymentPayrollIntegration — adjustments are
 * created BEFORE payroll calculation so the PayrollCalculator picks
 * them up during payslip computation.
 */
class HolidayPayrollIntegration
{
    /**
     * Create PayrollRunAdjustment records for holiday pay premiums
     * within the payroll run's period.
     *
     * Called before payroll processing begins (from ProcessPayrollRun job).
     *
     * @param PayrollRun $run
     * @return int Number of adjustments created
     */
    public function createAdjustmentsForRun(PayrollRun $run): int
    {
        $periodStart = $run->period_start->toDateString();
        $periodEnd   = $run->period_end->toDateString();

        // Find all holiday attendance records in this pay period
        $holidayAttendances = Attendance::withoutCompanyScope()
            ->where('status', 'holiday')
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->where('is_approved', true)
            ->get();

        if ($holidayAttendances->isEmpty()) {
            return 0;
        }

        // Pre-load all holidays in the period for fast lookup.
        // Key by date string so we can match attendance.date → holiday.date
        $holidays = Holiday::withoutCompanyScope()
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->where('is_active', true)
            ->where('affects_payroll', true)
            ->get()
            ->keyBy(fn ($h) => $h->date->toDateString());

        // Also check observed_date matches (holiday observed on a different day)
        $observedHolidays = Holiday::withoutCompanyScope()
            ->whereBetween('observed_date', [$periodStart, $periodEnd])
            ->where('is_active', true)
            ->where('affects_payroll', true)
            ->whereNotNull('observed_date')
            ->get()
            ->keyBy(fn ($h) => $h->observed_date->toDateString());

        // Build combined lookup: start with date-based holidays, then
        // add observed_date entries (date-based takes precedence).
        // We avoid Collection::merge() because it loses string keys
        // when one of the collections is empty.
        $allHolidays = collect();
        foreach ($holidays as $key => $holiday) {
            $allHolidays->put($key, $holiday);
        }
        foreach ($observedHolidays as $key => $holiday) {
            if (!$allHolidays->has($key)) {
                $allHolidays->put($key, $holiday);
            }
        }

        // Pre-load employee positions for all affected employees
        $employeeIds = $holidayAttendances->pluck('employee_id')->unique()->toArray();
        $positions = EmployeePosition::withoutCompanyScope()
            ->whereIn('employee_id', $employeeIds)
            ->where('employment_status', 'Active')
            ->with(['employee' => function ($q) {
                $q->withoutCompanyScope();
            }])
            ->get()
            ->keyBy('employee_id');

        $count = 0;

        foreach ($holidayAttendances as $attendance) {
            $dateKey = $attendance->date->toDateString();
            $holiday = $allHolidays->get($dateKey);

            if (!$holiday) {
                continue;
            }

            // Only apply premium if holiday_pay_rate > 1.0
            $payRate = (float) ($holiday->holiday_pay_rate ?? 1.0);
            if ($payRate <= 1.0) {
                continue;
            }

            $position = $positions->get($attendance->employee_id);
            if (!$position) {
                continue;
            }

            $employee = $position->employee;
            if (!$employee) {
                continue;
            }

            // Check if an adjustment already exists for this holiday+employee in this run
            $existing = PayrollRunAdjustment::withoutGlobalScope(\QuickerFaster\UILibrary\Scopes\CompanyScope::class)
                ->where('payroll_run_id', $run->id)
                ->where('source_type', 'holiday_pay')
                ->where('source_id', $holiday->id)
                ->where('employee_id', $attendance->employee_id)
                ->exists();

            if ($existing) {
                continue;
            }

            // Calculate the holiday pay premium
            $premiumAmount = $this->calculateHolidayPremium(
                $position,
                $attendance,
                $holiday,
                $run
            );

            if ($premiumAmount <= 0) {
                continue;
            }

            // Create the adjustment
            PayrollRunAdjustment::create([
                'company_id'      => $employee->company_id ?? $run->company_id,
                'payroll_run_id'  => $run->id,
                'employee_id'     => $attendance->employee_id,
                'type'            => 'earning',
                'label'           => 'Holiday Pay Premium - ' . $holiday->name . ' (' . number_format($payRate, 1) . 'x)',
                'amount'          => round($premiumAmount, 2),
                'note'            => "Holiday pay rate premium for {$holiday->name} on {$dateKey}",
                'source_type'     => 'holiday_pay',
                'source_id'       => $holiday->id,
            ]);

            $count++;

            Log::info('Holiday pay premium adjustment created for payroll run', [
                'payroll_run_id' => $run->id,
                'holiday_id'     => $holiday->id,
                'holiday_name'   => $holiday->name,
                'employee_id'    => $attendance->employee_id,
                'pay_rate'       => $payRate,
                'premium_amount' => round($premiumAmount, 2),
            ]);
        }

        return $count;
    }

    /**
     * Calculate the holiday pay premium for a single employee on a single holiday.
     *
     * The premium is the EXTRA amount above the base rate:
     *   base_pay_for_holiday_hours × (holiday_pay_rate − 1.0)
     *
     * Calculation varies by pay type:
     * - hourly:        holiday_hours × hourly_rate × (rate − 1.0)
     * - salaried_daily: daily_rate × (rate − 1.0)
     * - salaried_full:  (base_salary ÷ days_in_period) × (rate − 1.0)
     *
     * @param EmployeePosition $position
     * @param Attendance       $attendance
     * @param Holiday          $holiday
     * @param PayrollRun       $run
     * @return float
     */
    protected function calculateHolidayPremium(
        EmployeePosition $position,
        Attendance $attendance,
        Holiday $holiday,
        PayrollRun $run
    ): float {
        $payRate    = (float) ($holiday->holiday_pay_rate ?? 1.0);
        $multiplier = $payRate - 1.0; // premium portion only

        if ($multiplier <= 0) {
            return 0.0;
        }

        $payType      = $position->pay_type;
        $hourlyRate   = (float) ($position->hourly_rate ?? 0);
        $baseSalary   = (float) ($position->base_salary ?? 0);
        $holidayHours = (float) ($attendance->regular_hours ?? 0);

        if ($payType === 'hourly') {
            // Premium = holiday hours × hourly rate × (multiplier − 1)
            return $holidayHours * $hourlyRate * $multiplier;
        }

        if ($payType === 'salaried_daily') {
            // Daily rate = base_salary ÷ workdays in period
            $workPatternId = $this->resolveWorkPatternId($position, $run);
            $totalWorkdays = $this->getWorkdaysInPeriod(
                $run->period_start,
                $run->period_end,
                $workPatternId
            );
            $dailyRate = $totalWorkdays > 0 ? $baseSalary / $totalWorkdays : 0;

            return $dailyRate * $multiplier;
        }

        // salaried_full (and fallback): prorate base salary to a single day
        $totalDays = $run->period_start->diffInDays($run->period_end) + 1;
        $dailyBase = $totalDays > 0 ? $baseSalary / $totalDays : 0;

        return $dailyBase * $multiplier;
    }

    /**
     * Resolve the work pattern ID for an employee position.
     */
    protected function resolveWorkPatternId(EmployeePosition $position, PayrollRun $run): ?int
    {
        $assignment = \App\Modules\Attendance\Models\EmployeeWorkPattern::withoutCompanyScope()
            ->where('employee_id', $position->employee_id)
            ->where('start_date', '<=', $run->period_end)
            ->where(function ($q) use ($run) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $run->period_start);
            })
            ->orderBy('start_date', 'desc')
            ->first();

        if ($assignment) {
            return $assignment->work_pattern_id;
        }

        $defaultPattern = \App\Modules\Attendance\Models\WorkPattern::withoutCompanyScope()
            ->where('is_default', true)
            ->where('is_active', true)
            ->where('effective_date', '<=', $run->period_end)
            ->where(function ($q) use ($run) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $run->period_start);
            })
            ->when($position->employee->company_id ?? null, function ($query, $companyId) {
                return $query->where('company_id', $companyId);
            })
            ->first();

        return $defaultPattern?->id;
    }

    /**
     * Calculate the number of workdays in a given period based on work pattern.
     */
    protected function getWorkdaysInPeriod(\Carbon\Carbon $start, \Carbon\Carbon $end, ?int $workPatternId): int
    {
        if ($workPatternId) {
            $workPattern = \App\Modules\Attendance\Models\WorkPattern::find($workPatternId);
            if ($workPattern && !empty($workPattern->applicable_days)) {
                $applicableDays = $workPattern->applicable_days;
                if (is_string($applicableDays)) {
                    $applicableDays = array_map('intval', explode(',', $applicableDays));
                }

                $days = 0;
                $current = $start->copy();
                while ($current <= $end) {
                    if (in_array($current->dayOfWeek, $applicableDays)) {
                        $days++;
                    }
                    $current->addDay();
                }
                return $days;
            }
        }

        // Fallback: count weekdays (Mon–Fri)
        $days = 0;
        $current = $start->copy();
        while ($current <= $end) {
            if ($current->isWeekday()) {
                $days++;
            }
            $current->addDay();
        }
        return $days;
    }

    /**
     * Remove holiday pay adjustments for a payroll run (e.g., if reprocessing).
     *
     * @param PayrollRun $run
     * @return int Number of adjustments removed
     */
    public function removeAdjustmentsForRun(PayrollRun $run): int
    {
        return PayrollRunAdjustment::withoutGlobalScope(\QuickerFaster\UILibrary\Scopes\CompanyScope::class)
            ->where('payroll_run_id', $run->id)
            ->where('source_type', 'holiday_pay')
            ->delete();
    }
}
