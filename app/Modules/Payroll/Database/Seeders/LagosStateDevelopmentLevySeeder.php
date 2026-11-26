<?php

namespace App\Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Payroll\Models\PayrollPolicy;

/**
 * Seeds the Lagos State Development Levy.
 *
 * Under the Lagos State Local Government Administration Law and the
 * Personal Income Tax Act (as applicable in Lagos State), every
 * employee resident in Lagos State is liable to pay a development levy:
 *   - ₦100 per annum if gross monthly income ≤ ₦50,000
 *   - ₦200 per annum if gross monthly income > ₦50,000
 *
 * The levy is collected by the employer and remitted to the Lagos
 * State Internal Revenue Service (LIRS) alongside monthly PAYE.
 *
 * This policy is state-scoped (state_code = 'NG-LA') so it only
 * applies to employees whose location has a Lagos state code.
 */
class LagosStateDevelopmentLevySeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'name'                      => 'Lagos State Development Levy',
            'type'                      => 'deduction',
            'effect'                    => 'subtraction',
            'description'               => 'Lagos State Development Levy: ₦100/annum (≤₦50k/month) or ₦200/annum (>₦50k/month). Collected by employer, remitted to LIRS.',
            'country_code'              => 'NG',
            'state_code'                => 'NG-LA',
            'calculation_logic'         => json_encode([
                'calculation_type' => 'fixed',
                'base'             => 'gross_pay',
                'employee_value'   => 0,   // resolved dynamically below
                'employer_value'   => 0,
                'eligibility'      => [
                    'state_code' => 'NG-LA',
                ],
                'tiers' => [
                    ['max_monthly_gross' => 50000,  'annual_amount' => 100],
                    ['max_monthly_gross' => null,   'annual_amount' => 200],
                ],
            ]),
            'is_statutory'              => true,
            'tax_year'                  => (int) date('Y'),
            'remittance_due_days'       => 10,
            'late_penalty_rate'         => null,
            'late_penalty_description'  => 'Late payment may attract penalties under Lagos State Revenue Administration Law.',
            'effective_date'            => '2020-01-01',
            'expiry_date'               => null,
            'is_active'                 => true,
            'parent_policy_id'          => null,
        ];

        $existing = PayrollPolicy::withoutCompanyScope()
            ->where('type', 'deduction')
            ->where('country_code', 'NG')
            ->where('state_code', 'NG-LA')
            ->where('name', 'Lagos State Development Levy')
            ->first();

        if ($existing) {
            $existing->update($attributes);
            $this->command?->info('Lagos State Development Levy policy updated.');
            return;
        }

        PayrollPolicy::create($attributes);
        $this->command?->info('Lagos State Development Levy policy created.');
    }
}
