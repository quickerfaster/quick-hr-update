<?php

namespace App\Modules\Holiday\Database\Seeders;

use Illuminate\Database\Seeder;
use QuickerFaster\UILibrary\Models\Role;
use Spatie\Permission\Models\Permission;

class HolidayPermissionSeeder extends Seeder
{
    /**
     * Holiday-specific permissions to seed.
     */
    protected array $permissions = [
        // Navigation / access
        'view_holiday_overview',
        'view_holiday_calendar',
        'view_holiday',

        // CRUD
        'create_holiday',
        'edit_holiday',
        'delete_holiday',

        // Batch operations
        'create_holiday_batch',

        // Calendar management
        'manage_holiday_calendar',
    ];

    /**
     * Roles that receive all holiday permissions.
     */
    protected array $adminRoles = [
        'super_admin',
        'admin',
        'company_admin',
        'hr_manager',
        'hr_officer',
    ];

    /**
     * Roles that receive view-only holiday permissions (can see holidays
     * and calendars but cannot create, edit, or delete).
     */
    protected array $viewOnlyRoles = [
        'manager',
        'supervisor',
        'employee',
        'finance_manager',
        'payroll_officer',
        'accountant',
        'recruiter',
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

        // Assign view-only permissions to ESS / limited roles
        $viewPermissions = [
            'view_holiday_overview',
            'view_holiday_calendar',
            'view_holiday',
        ];

        $viewOnlyRoles = Role::whereIn('name', $this->viewOnlyRoles)->get();
        foreach ($viewOnlyRoles as $role) {
            foreach ($viewPermissions as $perm) {
                if (!$role->hasPermissionTo($perm)) {
                    $role->givePermissionTo($perm);
                }
            }
        }
    }
}
