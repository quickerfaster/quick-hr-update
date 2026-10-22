<?php

namespace App\Modules\Loans\Services;

/**
 * Calculates loan EMI (Equated Monthly Installment) and generates
 * repayment schedules using the reducing balance method.
 *
 * Formula: EMI = P × r × (1 + r)^n / ((1 + r)^n - 1)
 * Where: P = Principal, r = monthly interest rate, n = number of months
 */
class LoanCalculator
{
    /**
     * Calculate the monthly EMI for a loan.
     *
     * @param float $principal      Loan principal amount
     * @param float $annualRate     Annual interest rate (percentage, e.g., 5.0 for 5%)
     * @param int   $months         Number of repayment months
     * @return float                Monthly installment amount
     */
    public function calculateEMI(float $principal, float $annualRate, int $months): float
    {
        if ($months <= 0) {
            return $principal;
        }

        if ($annualRate <= 0) {
            return round($principal / $months, 2);
        }

        $monthlyRate = $annualRate / 12 / 100;

        if ($monthlyRate <= 0) {
            return round($principal / $months, 2);
        }

        $emi = $principal * $monthlyRate * pow(1 + $monthlyRate, $months)
            / (pow(1 + $monthlyRate, $months) - 1);

        return round($emi, 2);
    }

    /**
     * Calculate the total repayable amount (principal + interest).
     *
     * @param float $emi       Monthly installment
     * @param int   $months    Number of months
     * @return float
     */
    public function calculateTotalRepayable(float $emi, int $months): float
    {
        return round($emi * $months, 2);
    }

    /**
     * Calculate total interest payable over the loan term.
     *
     * @param float $totalRepayable  Total amount to repay
     * @param float $principal       Original principal
     * @return float
     */
    public function calculateTotalInterest(float $totalRepayable, float $principal): float
    {
        return round($totalRepayable - $principal, 2);
    }

    /**
     * Generate a repayment schedule for the loan.
     *
     * Returns an array of installments, each with:
     * - installment_number
     * - due_date
     * - amount (EMI)
     * - principal_component
     * - interest_component
     * - balance_after
     *
     * @param float    $principal    Loan principal
     * @param float    $annualRate   Annual interest rate (percentage)
     * @param int      $months       Number of months
     * @param string   $startDate    First repayment date (Y-m-d)
     * @return array
     */
    public function generateSchedule(float $principal, float $annualRate, int $months, string $startDate): array
    {
        $emi = $this->calculateEMI($principal, $annualRate, $months);
        $monthlyRate = $annualRate / 12 / 100;
        $balance = $principal;
        $schedule = [];

        $date = new \DateTime($startDate);

        for ($i = 1; $i <= $months; $i++) {
            $interestComponent = round($balance * $monthlyRate, 2);
            $principalComponent = round($emi - $interestComponent, 2);

            // Adjust for final installment to account for rounding
            if ($i === $months) {
                $principalComponent = $balance;
                $interestComponent = round($emi - $principalComponent, 2);
                if ($interestComponent < 0) {
                    $interestComponent = 0;
                }
            }

            $balance = round($balance - $principalComponent, 2);
            if ($balance < 0) {
                $balance = 0;
            }

            $schedule[] = [
                'installment_number' => $i,
                'due_date' => $date->format('Y-m-d'),
                'amount' => $emi,
                'principal_component' => $principalComponent,
                'interest_component' => $interestComponent,
                'balance_after' => $balance,
            ];

            $date->modify('+1 month');
        }

        return $schedule;
    }

    /**
     * Validate that the requested amount is within the loan type's limits.
     *
     * @param float $amount       Requested amount
     * @param float $minAmount    Minimum allowed
     * @param float $maxAmount    Maximum allowed
     * @return array              ['valid' => bool, 'message' => string]
     */
    public function validateAmount(float $amount, float $minAmount, float $maxAmount): array
    {
        if ($minAmount > 0 && $amount < $minAmount) {
            return [
                'valid' => false,
                'message' => "Amount must be at least " . number_format($minAmount, 2) . ".",
            ];
        }

        if ($maxAmount > 0 && $amount > $maxAmount) {
            return [
                'valid' => false,
                'message' => "Amount cannot exceed " . number_format($maxAmount, 2) . ".",
            ];
        }

        return ['valid' => true, 'message' => ''];
    }
}
