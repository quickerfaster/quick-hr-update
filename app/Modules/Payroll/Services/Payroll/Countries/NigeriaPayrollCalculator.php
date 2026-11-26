<?php

namespace App\Modules\Payroll\Services\Payroll\Countries;

use App\Modules\Payroll\Contracts\CountryPayrollCalculator;

/**
 * Nigerian statutory payroll strategy (Nigeria Tax Act 2025 / PRA 2014).
 *
 * - Pensionable base = BHT (Basic + Housing + Transport).
 * - Income-tax exemption for minimum-wage earners (₦70,000/month).
 * - Rent relief (20% of annual rent, capped ₦500,000).
 */
class NigeriaPayrollCalculator implements CountryPayrollCalculator
{
    public function resolvePensionableBase(array $components, float $baseSalary): float
    {
        $group = config('quick_hr_payroll.component_groups.NG.pensionable', ['basic', 'housing', 'transport']);

        if (empty($components)) {
            return $baseSalary;
        }

        $sum = 0.0;
        foreach ($group as $key) {
            $sum += (float) ($components[$key] ?? 0);
        }

        return $sum > 0 ? $sum : $baseSalary;
    }

    public function isIncomeTaxExempt(float $annualGross): bool
    {
        $monthly = (float) config('quick_hr_payroll.country_tax_rules.NG.minimum_wage_exemption', 70000);

        return $annualGross <= ($monthly * 12);
    }

    public function applyTaxReliefs(float $annualTaxable, array $reliefs): float
    {
        $rules = config('quick_hr_payroll.country_tax_rules.NG', []);

        // 1. Rent relief (20% of annual rent, capped at ₦500,000)
        $annualRent = (float) ($reliefs['annual_rent'] ?? 0);
        if ($annualRent > 0) {
            $rate = (float) ($rules['rent_relief_rate'] ?? 0.20);
            $cap  = (float) ($rules['rent_relief_cap'] ?? 500000);
            $annualTaxable = max(0, $annualTaxable - min($annualRent * $rate, $cap));
        }

        // 2. Employee pension contribution (8% of BHT) — tax-exempt under PRA 2014
        $annualPension = (float) ($reliefs['annual_pension_employee'] ?? 0);
        if ($annualPension > 0) {
            $annualTaxable = max(0, $annualTaxable - $annualPension);
        }

        // 3. Employee NHF contribution (2.5% of basic) — tax-exempt under NHF Act
        $annualNhf = (float) ($reliefs['annual_nhf_employee'] ?? 0);
        if ($annualNhf > 0) {
            $annualTaxable = max(0, $annualTaxable - $annualNhf);
        }

        return $annualTaxable;
    }
}
