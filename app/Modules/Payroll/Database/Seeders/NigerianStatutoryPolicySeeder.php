<?php

namespace App\Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Payroll\Models\PayrollPolicy;

/**
 * Seeds the statutory Nigerian payroll deduction/contribution policies
 * that are required by law and common to most Nigerian employers.
 *
 * Rates (verify with a Nigerian payroll/tax professional):
 *   - Contributory Pension Scheme (PRA 2014): employee 8%, employer 10%
 *   - National Housing Fund (NHF Act):         employee 2.5% of basic salary
 *   - NSITF (Employees' Compensation Act):     employer 1% of payroll
 *   - ITF (Industrial Training Fund Act):      employer 1% of payroll
 *   - NHIS/NHIA (NHIA Act 2022):               employee 5%, employer 10% of basic
 *
 * Each policy is idempotent and global (country_code = 'NG') so it
 * applies to every Nigerian company unless overridden by a company
 * specific policy.
 */
class NigerianStatutoryPolicySeeder extends Seeder
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected function policies(): array
    {
        return [
        [
            'name'         => 'Nigerian Pension (Contributory Pension Scheme)',
            'type'         => 'pension',
            'description'  => 'Employee 8% / employer 10% of monthly emolument (BHT: Basic + Housing + Transport) under the Pension Reform Act 2014.',
            'employee'     => 8,
            'employer'     => 10,
            'base'         => 'pensionable',
        ],
        [
            'name'         => 'National Housing Fund (NHF)',
            'type'         => 'deduction',
            'description'  => 'Employee 2.5% of basic salary under the National Housing Fund Act. Optional for private-sector employees — requires written consent.',
            'employee'     => 2.5,
            'employer'     => 0,
            'base'         => 'base_salary',
            'is_statutory' => false,
        ],
        [
            'name'         => 'NSITF (Employees Compensation)',
            'type'         => 'deduction',
            'description'  => 'Employer 1% of total monthly payroll under the Employees\' Compensation Act 2010.',
            'employee'     => 0,
            'employer'     => 1,
            'base'         => 'gross_pay',
        ],
        [
            'name'         => 'ITF (Industrial Training Fund)',
            'type'         => 'deduction',
            'description'  => 'Employer 1% of annual payroll under the Industrial Training Fund Act (employers with 5+ employees or ₦50m+ turnover).',
            'employee'     => 0,
            'employer'     => 1,
            'base'         => 'gross_pay',
        ],
        [
            'name'         => 'NHIS (National Health Insurance)',
            'type'         => 'insurance',
            'description'  => 'Employee 5% / employer 10% of basic salary under the NHIA Act 2022.',
            'employee'     => 5,
            'employer'     => 10,
            'base'         => 'base_salary',
        ],
        [
            'name'         => 'Group Life Assurance',
            'type'         => 'insurance',
            'description'  => 'Employer-funded group life insurance (PRA 2014 §4(5)). Premium rate is insurer-specific — default from config, overridable per company.',
            'employee'     => 0,
            'employer'     => (float) config('quick_hr_payroll.group_life_employer_rate', 1.5),
            'base'         => 'gross_pay',
        ],
        ];
    }

    public function run(): void
    {
        foreach ($this->policies() as $policy) {
            $this->seedPolicy($policy);
        }
    }

    protected function seedPolicy(array $policy): void
    {
        $attributes = [
            'name'              => $policy['name'],
            'type'              => $policy['type'],
            'effect'            => 'subtraction',
            'description'       => $policy['description'],
            'country_code'      => 'NG',
            'state_code'        => null,
            'calculation_logic' => json_encode([
                'calculation_type' => 'percentage',
                'base'             => $policy['base'],
                'employee_value'   => $policy['employee'],
                'employer_value'   => $policy['employer'],
            ]),
            'is_statutory'      => $policy['is_statutory'] ?? true,
            'tax_year'          => (int) date('Y'),
            'effective_date'    => '2020-01-01',
            'expiry_date'       => null,
            'is_active'         => true,
            'parent_policy_id'  => null,
        ];

        $existing = PayrollPolicy::withoutCompanyScope()
            ->where('type', $policy['type'])
            ->where('country_code', 'NG')
            ->where('name', $policy['name'])
            ->first();

        if ($existing) {
            $existing->update($attributes);
            $this->command?->info("Statutory policy updated: {$policy['name']}");
            return;
        }

        PayrollPolicy::create($attributes);
        $this->command?->info("Statutory policy created: {$policy['name']}");
    }
}
