<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Hr\Database\Seeders\EmployeeWithDependenciesSeeder;
use App\Modules\Hr\Database\Seeders\EssNotificationTemplateSeeder;
use App\Modules\Hr\Database\Seeders\HrRoleSeeder;
use App\Modules\Leave\Database\Seeders\LeaveWorkflowNotificationTemplateSeeder;
use App\Modules\Payroll\Database\Seeders\PayrollRoleSeeder;
use App\Modules\Payroll\Database\Seeders\WorkflowNotificationTemplateSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use QuickerFaster\UILibrary\Core\Common\Database\Seeders\NotificationTemplateSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // ─── Roles ────────────────────────────────────────────────
        $this->call(PayrollRoleSeeder::class);
        $this->call(HrRoleSeeder::class);

        // ─── Users ────────────────────────────────────────────────
        $this->call(UserSeeder::class);

        // ─── Employees ────────────────────────────────────────────
        // WARNING: EmployeeWithDependenciesSeeder.shouldRun() returns true
        // when the --force flag is passed (e.g. `php artisan db:seed --force`).
        // On production deploys via cpanel.yml, this causes ~5,000 firstOrCreate
        // checks on every deploy, adding ~2+ minutes to the deployment.
        $this->call(EmployeeWithDependenciesSeeder::class);

        // ─── Notification Templates ───────────────────────────────
        $this->call(NotificationTemplateSeeder::class);
        $this->call(EssNotificationTemplateSeeder::class);
        $this->call(WorkflowNotificationTemplateSeeder::class);
        $this->call(LeaveWorkflowNotificationTemplateSeeder::class);
    }
}
