<?php

namespace App\Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Legacy placeholder.
 *
 * This seeder previously contained US-centric allowance/bonus/tax/deduction
 * types (FICA, Medicare, 401(k), HSA, etc.) that are not applicable to
 * Nigerian payroll.
 *
 * Nigerian statutory policies now live in:
 *   - NigerianPayeTaxPolicySeeder.php      (PAYE income tax bands)
 *   - NigerianStatutoryPolicySeeder.php    (pension, NHF, NSITF, ITF, NHIS)
 *
 * Kept as an empty seeder so the auto-discovery order remains stable and
 * existing deployments don't break when the class is referenced.
 */
class BonusAndDeductionTypeSeeders extends Seeder
{
    public function run(): void
    {
        // Intentionally empty — see NigerianStatutoryPolicySeeder.
    }
}
