<?php

namespace App\Modules\Hr\Database\Seeders;

use App\Models\User;
use App\Modules\Hr\Models\Company;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\EmployeePosition;
use App\Modules\Hr\Models\JobTitle;
use App\Modules\Hr\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EssTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates an ESS test employee linked to the ESS test user
     * from config('test-users.ess'), along with an EmployeePosition.
     *
     * Only runs in the local environment.
     */
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command->warn('EssTestDataSeeder only runs in the local environment. Skipping.');

            return;
        }

        $this->command->info('─── Seeding ESS test data ───');

        // ─── Find or create the ESS test user ───────────────────────
        $essConfig = config('test-users.ess');

        if (! $essConfig) {
            $this->command->warn('ESS test user config not found. Skipping.');

            return;
        }

        $essUser = User::firstOrCreate(
            ['email' => $essConfig['email']],
            [
                'name' => $essConfig['name'],
                'password' => Hash::make($essConfig['password']),
            ]
        );

        // ─── Assign the employee role ───────────────────────────────
        $roleName = $essConfig['role'] ?? 'employee';
        $role = Role::where('name', $roleName)->first();

        if ($role) {
            $essUser->assignRole($role);
            $this->command->info("  ✓ Role '{$roleName}' assigned to {$essUser->email}");
        } else {
            $this->command->warn("  ⚠ Role '{$roleName}' not found. Ensure HrRoleSeeder has been run.");
        }

        // ─── Find or create the ESS test company ────────────────────
        $company = Company::firstOrCreate(
            ['name' => 'ESS Test Company'],
            [
                'level' => 'division',
                'status' => 'active',
            ]
        );

        $this->command->info("  ✓ Company #{$company->id} " . ($company->wasRecentlyCreated ? 'created' : 'reused'));

        // ─── Create or reuse the ESS employee ───────────────────────
        $employee = Employee::firstOrCreate(
            ['user_id' => $essUser->id],
            [
                'employee_number' => 'ESS-' . str_pad((string) $essUser->id, 4, '0', STR_PAD_LEFT),
                'first_name' => $essConfig['name'],
                'last_name' => 'User',
                'email' => $essConfig['email'],
                'company_id' => $company->id,
                'hire_date' => now(),
            ]
        );

        $this->command->info("  ✓ Employee #{$employee->id} " . ($employee->wasRecentlyCreated ? 'created' : 'reused') . " for {$essUser->email}");

        // ─── Create an EmployeePosition ─────────────────────────────
        $jobTitle = JobTitle::firstOrCreate(
            ['title' => 'Employee Self-Service'],
            [
                'description' => 'Default job title for ESS test employee',
                'company_id' => $company->id,
            ]
        );

        $department = Department::firstOrCreate(
            ['name' => 'General'],
            [
                'code' => 'GEN',
                'description' => 'Default department for ESS test employee',
                'company_id' => $company->id,
            ]
        );

        EmployeePosition::firstOrCreate(
            ['employee_id' => $employee->id],
            [
                'job_title_id' => $jobTitle->id,
                'department_id' => $department->id,
                'company_id' => $company->id,
                'pay_type' => 'salary',
                'employment_status' => 'Active',
                'salary_currency' => 'USD',
                'base_salary' => 0,
                'hourly_rate' => 0,
            ]
        );

        $this->command->info("  ✓ EmployeePosition created for Employee #{$employee->id}");
        $this->command->info('─── ESS test data seeding complete ───');
    }
}