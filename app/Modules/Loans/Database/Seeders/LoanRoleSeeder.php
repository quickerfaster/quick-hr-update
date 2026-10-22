<?php

namespace App\Modules\Loans\Database\Seeders;

use Illuminate\Database\Seeder;
use QuickerFaster\UILibrary\Models\Role;
use Spatie\Permission\Models\Permission;

class LoanRoleSeeder extends Seeder
{
    /**
     * Loan-specific permissions to seed.
     */
    protected array $permissions = [
        'view_loan',
        'create_loan',
        'edit_loan',
        'delete_loan',
        'view_loan_type',
        'create_loan_type',
        'edit_loan_type',
        'delete_loan_type',
        'manage_loan_types',
        'view_loan_reports',
    ];

    /**
     * Roles that receive all loan permissions.
     */
    protected array $adminRoles = [
        'super_admin',
        'admin',
        'company_admin',
        'hr_manager',
        'hr_officer',
        'finance_manager',
    ];

    /**
     * Roles that receive view-only loan permissions (for ESS).
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
        // Ensure all permissions exist
        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
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
            if (!$role->hasPermissionTo('view_loan')) {
                $role->givePermissionTo('view_loan');
            }
        }
    }
}
