<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @throws \RuntimeException If a required role does not exist in the database.
     */
    public function run(): void
    {
        // ─── Mobile app test users ─────────────────────────────────
        $users = config('test-users.users', []);

        foreach ($users as $userData) {
            $roleName = $userData['role'];

            $role = Role::where('name', $roleName)->first();

            if (! $role) {
                throw new \RuntimeException(
                    "Role '{$roleName}' not found. Ensure HrRoleSeeder has been run before UserSeeder."
                );
            }

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                ]
            );

            $user->assignRole($role);
        }

        // ─── Default test user ─────────────────────────────────────
        $default = config('test-users.default');

        if ($default) {
            User::firstOrCreate(
                ['email' => $default['email']],
                [
                    'name' => $default['name'],
                    'password' => Hash::make($default['password']),
                ]
            );
        }

        // ─── ESS test user ─────────────────────────────────────────
        $ess = config('test-users.ess');

        if ($ess) {
            $roleName = $ess['role'];

            $role = Role::where('name', $roleName)->first();

            if (! $role) {
                throw new \RuntimeException(
                    "Role '{$roleName}' not found. Ensure HrRoleSeeder has been run before UserSeeder."
                );
            }

            $user = User::firstOrCreate(
                ['email' => $ess['email']],
                [
                    'name' => $ess['name'],
                    'password' => Hash::make($ess['password']),
                ]
            );

            $user->assignRole($role);
        }
    }
}