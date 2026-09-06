<?php

namespace App\Modules\Hr\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class HrRoleSeeder extends Seeder
{
    /**
     * All HR roles in the system.
     */
    protected array $roles = [
        'super_admin',
        'company_admin',
        'hr_manager',
        'hr_officer',
        'payroll_officer',
        'accountant',
        'manager',
        'supervisor',
        'recruiter',
        'employee',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
