<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $memberUser = User::firstOrCreate(
            ['email' => 'admin@softui.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('secret'),
                'email_verified_at' => now(),
            ]
        );

        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@quickerfaster.com'],
            [
                'name' => 'super admin',
                'password' => Hash::make('ChangeMe@12345'),
                'email_verified_at' => now(),
            ]
        );

        $companyAdmin = User::firstOrCreate(
            ['email' => 'gmadmin@agriwatts.ng'],
            [
                'name' => 'company admin',
                'password' => Hash::make('Test@12345'),
                'email_verified_at' => now(),
            ]
        );

        $superAdminRole = Role::findByName('super_admin', 'web');
        $companyAdminRole = Role::findByName('company_admin', 'web');
        $memberRole = Role::findByName('member', 'web');

        if ($superAdminRole) {
            $superAdmin->assignRole($superAdminRole);
        } else {
            throw new \Exception('Role "super_admin" not found. Did you run RoleSeeder?');
        }

        if ($companyAdminRole) {
            $companyAdmin->assignRole($companyAdminRole);
        } else {
            throw new \Exception('Role "company_admin" not found. Did you run RoleSeeder?');
        }

        if ($memberRole) {
            $memberUser->assignRole($memberRole);
        } else {
            throw new \Exception('Role "member" not found. Did you run RoleSeeder?');
        }
    }
}
