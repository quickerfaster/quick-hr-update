<?php

namespace App\Modules\Hr\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class HrRoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'hr_manager', 'guard_name' => 'web']);
    }
}
