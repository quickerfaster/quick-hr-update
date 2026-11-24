<?php

namespace App\Modules\Attendance\Traits;

trait HasPayPeriods
{
    /**
     * Get number of pay periods per year for a given frequency.
     *
     * Normalises incoming frequency strings (e.g. 'weekly', 'bi-weekly',
     * 'semi_monthly') to the canonical CamelCase keys used by
     * config('quick_hr_payroll.pay_periods_per_year').
     */
    protected function getPeriodsPerYear(string $frequency): int
    {
        $periods = config('quick_hr_payroll.pay_periods_per_year', []);
        $key = $this->normalizeFrequency($frequency);

        return $periods[$key] ?? 12;
    }

    /**
     * Normalise a pay frequency string to its canonical CamelCase key.
     */
    protected function normalizeFrequency(string $frequency): string
    {
        $normalized = strtolower(trim($frequency));

        return match ($normalized) {
            'weekly'                      => 'Weekly',
            'biweekly', 'bi-weekly',
            'bi_weekly'                   => 'Bi-weekly',
            'semi_monthly', 'semi-monthly',
            'semimonthly'                 => 'Semi-monthly',
            'monthly'                     => 'Monthly',
            'quarterly'                   => 'Quarterly',
            'yearly', 'annual', 'annually' => 'Yearly',
            'daily'                       => 'Daily',
            default                       => $frequency,
        };
    }

    /**
     * Annualise a period amount based on pay frequency.
     */
    protected function annualizeAmount(float $periodAmount, string $frequency): float
    {
        return $periodAmount * $this->getPeriodsPerYear($frequency);
    }

    /**
     * De‑annualise an annual amount back to a period amount.
     */
    protected function deAnnualizeAmount(float $annualAmount, string $frequency): float
    {
        $periods = $this->getPeriodsPerYear($frequency);
        return $periods > 0 ? $annualAmount / $periods : 0;
    }
}
