<?php

namespace App\Modules\Payroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PayrollRoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'payroll_officer', 'guard_name' => 'web']);

        // Assign payroll_officer role to the test user so workflow
        // step recipients resolve correctly.
        \App\Models\User::where('email', 'test@example.com')->first()?->assignRole('payroll_officer');
    }
}
