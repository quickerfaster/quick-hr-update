<?php

namespace App\Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Payroll\Models\PayrollPolicy;

/**
 * Seeds the default Nigerian PAYE (Pay As You Earn) progressive tax policy.
 *
 * Uses the current Nigerian Finance Act tax bands (annual income):
 *   - First ₦300,000  →  0%
 *   - Next  ₦300,000  →  7%
 *   - Next  ₦500,000  → 11%
 *   - Next  ₦500,000  → 15%
 *   - Next  ₦1,600,000 → 19%
 *   - Above ₦3,200,000 → 21%
 *
 * The policy is global (no company scope) with country_code = 'NG'
 * so it automatically applies to all Nigerian companies that don't
 * have a company-specific tax policy override.
 */
class NigerianPayeTaxPolicySeeder extends Seeder
{
    public function run(): void
    {
        $exists = PayrollPolicy::withoutCompanyScope()
            ->where('type', 'tax')
            ->where('country_code', 'NG')
            ->where('name', 'Nigerian PAYE (Income Tax)')
            ->exists();

        if ($exists) {
            $this->command?->info('Nigerian PAYE tax policy already exists. Skipping.');
            return;
        }

        PayrollPolicy::create([
            'name'              => 'Nigerian PAYE (Income Tax)',
            'type'              => 'tax',
            'effect'            => 'subtraction',
            'description'       => 'Default Nigerian PAYE progressive income tax. Applies to all Nigerian employees unless overridden by a company-specific tax policy.',
            'country_code'      => 'NG',
            'state_code'        => null,
            'calculation_logic' => json_encode([
                'calculation_type' => 'tax_bands',
                'base'             => 'gross_pay',
                'bands'            => [
                    ['start' => 0,         'end' => 300000,   'rate' => 0],
                    ['start' => 300000,    'end' => 600000,   'rate' => 7],
                    ['start' => 600000,    'end' => 1100000,  'rate' => 11],
                    ['start' => 1100000,   'end' => 1600000,  'rate' => 15],
                    ['start' => 1600000,   'end' => 3200000,  'rate' => 19],
                    ['start' => 3200000,   'end' => 999999999, 'rate' => 21],
                ],
            ]),
            'is_statutory'      => true,
            'tax_year'          => (int) date('Y'),
            'effective_date'    => '2020-01-01',
            'expiry_date'       => null,
            'is_active'         => true,
            'parent_policy_id'  => null,
        ]);

        $this->command?->info('Nigerian PAYE tax policy created successfully.');
    }
}
