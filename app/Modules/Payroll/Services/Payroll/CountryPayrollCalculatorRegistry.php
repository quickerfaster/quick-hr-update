<?php

namespace App\Modules\Payroll\Services\Payroll;

use App\Modules\Payroll\Contracts\CountryPayrollCalculator;
use App\Modules\Payroll\Services\Payroll\Countries\DefaultCountryPayrollCalculator;

/**
 * Resolves the CountryPayrollCalculator strategy for a given country code.
 */
class CountryPayrollCalculatorRegistry
{
    /** @var array<string, class-string<CountryPayrollCalculator>> */
    protected array $map;

    public function __construct()
    {
        $this->map = config('quick_hr_payroll.country_calculators', []);
    }

    public function resolve(?string $countryCode): CountryPayrollCalculator
    {
        $class = $this->map[$countryCode] ?? DefaultCountryPayrollCalculator::class;

        return app($class);
    }
}
