<?php

namespace App\Modules\Payroll\Services\Payroll\Countries;

use App\Modules\Payroll\Contracts\CountryPayrollCalculator;

/**
 * Default country calculator — used when no country-specific strategy is
 * registered. Keeps the pre-multi-country behaviour (base_salary only,
 * no exemptions, no reliefs).
 */
class DefaultCountryPayrollCalculator implements CountryPayrollCalculator
{
    public function resolvePensionableBase(array $components, float $baseSalary): float
    {
        return $baseSalary;
    }

    public function isIncomeTaxExempt(float $annualGross): bool
    {
        return false;
    }

    public function applyTaxReliefs(float $annualTaxable, array $reliefs): float
    {
        return $annualTaxable;
    }
}
