<?php

namespace App\Modules\Organization\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Organization\Models\Company;
use App\Modules\Organization\Models\Branch;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Location;

class OrganizationSeeder extends Seeder
{
    /**
     * Determine if the seeder should run.
     *
     * Only runs in non-production environments. On production, the initial
     * company should be created through the app UI or a manual setup process
     * — never via a seeder that creates a "Demo Company."
     */
    protected function shouldRun(): bool
    {
        return app()->environment('local', 'staging', 'development');
    }

    public function run(): void
    {
        if (!$this->shouldRun()) {
            if ($this->command) {
                $this->command->warn('Skipping OrganizationSeeder (not in local/staging/development).');
            }
            return;
        }

        // Skip if data already exists
        if (Company::count() > 0) {
            return;
        }

        $company = Company::create([
            'name' => 'Demo Company',
            'code' => 'DEMO',
            'email' => 'info@demo.com',
            'currency_code' => 'USD',
            'timezone' => 'UTC',
        ]);

        Branch::create([
            'company_id' => $company->id,
            'name' => 'Headquarters',
            'code' => 'HQ',
            'is_headquarters' => true,
        ]);

        Department::create([
            'company_id' => $company->id,
            'name' => 'General',
            'code' => 'GEN',
        ]);

        Location::create([
            'company_id'      => $company->id,
            'name'            => 'Main Office',
            'code'            => 'MAIN',
            'is_headquarters' => true,
            'latitude'        => 9.0765,   // Abuja, Nigeria
            'longitude'       => 7.3986,
            'geofence_radius' => 200,       // 200m radius
            'address'         => 'Central Business District',
            'city'            => 'Abuja',
            'country_code'    => 'NG',
            'state_code'      => 'FC',
        ]);
    }
}
