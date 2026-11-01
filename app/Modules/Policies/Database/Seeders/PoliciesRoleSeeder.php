<?php

namespace App\Modules\Policies\Database\Seeders;

use Illuminate\Database\Seeder;
use QuickerFaster\UILibrary\Models\Role;
use QuickerFaster\UILibrary\Models\System;
use Spatie\Permission\Models\Permission;

class PoliciesRoleSeeder extends Seeder
{
    /**
     * Policy-specific permissions to seed.
     */
    protected array $permissions = [
        'view_company_policy',
        'create_company_policy',
        'edit_company_policy',
        'delete_company_policy',
        'manage_policy_distribution',
        'view_policy_compliance_report',
    ];

    /**
     * Roles that receive all policy permissions.
     */
    protected array $adminRoles = [
        'super_admin',
        'admin',
        'company_admin',
        'hr_manager',
        'hr_officer',
    ];

    /**
     * Roles that receive view-only policy permissions (for ESS).
     */
    protected array $viewOnlyRoles = [
        'manager',
        'supervisor',
        'employee',
        'recruiter',
        'payroll_officer',
        'accountant',
    ];

    public function run(): void
    {
        // The System model is a singleton (id = 1) created by the systems
        // table migration. It has no 'name' column — use firstOrCreate on id.
        $system = System::firstOrCreate(['id' => 1]);

        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['system_id' => $system->id, 'guard_name' => 'web']
            );
        }

        // Assign full permissions to admin roles
        $adminRoles = Role::whereIn('name', $this->adminRoles)->get();
        foreach ($adminRoles as $role) {
            $role->givePermissionTo($this->permissions);
        }

        // Assign view-only permission to ESS roles
        $viewOnlyRoles = Role::whereIn('name', $this->viewOnlyRoles)->get();
        foreach ($viewOnlyRoles as $role) {
            if (!$role->hasPermissionTo('view_company_policy')) {
                $role->givePermissionTo('view_company_policy');
            }
        }
    }
}
