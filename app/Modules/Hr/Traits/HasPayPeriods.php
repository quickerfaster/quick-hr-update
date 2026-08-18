<?php

namespace App\Modules\Hr\Traits;

trait HasPayPeriods
{
    /**
     * Get number of pay periods per year for a given frequency.
     */
    protected function getPeriodsPerYear(string $frequency): int
    {
        return config('quick_hr_payroll.pay_periods_per_year')[$frequency] ?? 12;
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
