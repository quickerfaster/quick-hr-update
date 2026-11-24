<?php

namespace App\Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Payroll\Models\PayrollPolicy;

/**
 * Seeds the default Nigerian PAYE (Pay As You Earn) progressive tax policy.
 *
 * Uses the Nigeria Tax Act 2025 bands effective 1 January 2026 (annual
 * taxable income — after pension, NHF and other reliefs):
 *   - First ₦800,000                 →  0%
 *   - Next ₦2,200,000 (≤ ₦3,000,000)  → 15%
 *   - Next ₦9,000,000 (≤ ₦12,000,000) → 18%
 *   - Next ₦13,000,000 (≤ ₦25,000,000) → 21%
 *   - Next ₦25,000,000 (≤ ₦50,000,000) → 23%
 *   - Above ₦50,000,000               → 25%
 *
 * The 2026 rules remove the old Consolidated Relief Allowance (CRA); the
 * ₦800,000 0% band takes its place. Rent relief (20% of annual rent, capped
 * at ₦500,000) still applies but is not yet implemented in this seeder.
 * Employees earning ≤ ₦70,000/month (national minimum wage) are exempt.
 *
 * The policy is global (no company scope) with country_code = 'NG' so it
 * automatically applies to all Nigerian companies without a company-specific
 * override. Existing rows are updated in place so re-seeding applies new bands.
 */
class NigerianPayeTaxPolicySeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'name'              => 'Nigerian PAYE (Income Tax)',
            'type'              => 'tax',
            'effect'            => 'subtraction',
            'description'       => 'Default Nigerian PAYE progressive income tax under the Nigeria Tax Act 2025 (effective 1 January 2026). ₦800,000 annual 0% band, then 15%–25%.',
            'country_code'      => 'NG',
            'state_code'        => null,
            'calculation_logic' => json_encode([
                'calculation_type' => 'tax_bands',
                'base'             => 'gross_pay',
                'bands'            => [
                    ['start' => 0,        'end' => 800000,    'rate' => 0],
                    ['start' => 800000,   'end' => 3000000,   'rate' => 15],
                    ['start' => 3000000,  'end' => 12000000,  'rate' => 18],
                    ['start' => 12000000, 'end' => 25000000,  'rate' => 21],
                    ['start' => 25000000, 'end' => 50000000,  'rate' => 23],
                    ['start' => 50000000, 'end' => 999999999, 'rate' => 25],
                ],
            ]),
            'is_statutory'      => true,
            'tax_year'          => (int) date('Y'),
            'effective_date'    => '2026-01-01',
            'expiry_date'       => null,
            'is_active'         => true,
            'parent_policy_id'  => null,
        ];

        $policy = PayrollPolicy::withoutCompanyScope()
            ->where('type', 'tax')
            ->where('country_code', 'NG')
            ->where('name', 'Nigerian PAYE (Income Tax)')
            ->first();

        if ($policy) {
            $policy->update($attributes);
            $this->command?->info('Nigerian PAYE tax policy updated to 2026 NTA bands.');
            return;
        }

        PayrollPolicy::create($attributes);
        $this->command?->info('Nigerian PAYE tax policy created with 2026 NTA bands.');
    }
}
