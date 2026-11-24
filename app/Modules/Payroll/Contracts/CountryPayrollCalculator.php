<?php

namespace App\Modules\Payroll\Contracts;

/**
 * Country-specific statutory payroll calculation strategy.
 *
 * The generic PayrollCalculator delegates country-specific decisions
 * (pensionable base, income-tax exemption, tax reliefs) to an
 * implementation of this contract, resolved by country code.
 */
interface CountryPayrollCalculator
{
    /**
     * Resolve the pensionable base from salary components.
     *
     * @param array $components Keyed salary components (e.g. ['basic' => .., 'housing' => ..])
     * @param float $baseSalary Fallback base salary when components are absent.
     */
    public function resolvePensionableBase(array $components, float $baseSalary): float;

    /**
     * Whether an employee is exempt from income tax at the given annual gross.
     */
    public function isIncomeTaxExempt(float $annualGross): bool;

    /**
     * Apply country-specific tax reliefs to annual taxable income.
     *
     * @param array $reliefs Keyed relief inputs (e.g. ['annual_rent' => ..]).
     */
    public function applyTaxReliefs(float $annualTaxable, array $reliefs): float;
}
