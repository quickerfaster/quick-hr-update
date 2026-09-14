<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use QuickerFaster\UILibrary\Core\Admin\Database\Seeders\RoleSeeder;
use QuickerFaster\UILibrary\Core\Admin\Database\Seeders\UserSeeder as LibraryUserSeeder;
use QuickerFaster\UILibrary\Services\AccessControl\AccessControlPermissionService;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Runs library core seeders first, then the consuming app's
     * UserSeeder (production accounts), then auto-discovers and runs
     * all module-specific seeders from app/Modules/* /Database/Seeders/.
     * This ensures cpanel deployments (which only call db:seed) can
     * seed all business data without manual intervention.
     */
    public function run(): void
    {
        // 1. Library core seeders (must run first — creates roles and default admin)
        $this->call([
            RoleSeeder::class,
            LibraryUserSeeder::class,
        ]);

        AccessControlPermissionService::seedPermissionNames();

        // 2. Consuming-app UserSeeder — production accounts
        //    (admin@softui.com, superadmin@quickerfaster.com, gmadmin@agriwatts.ng)
        $this->call(UserSeeder::class);

        // 3. Auto-discover and run all module seeders
        $this->call($this->discoverModuleSeeders());
    }

    /**
     * Scan app/Modules/* /Database/Seeders/ for seeder classes and
     * return them in dependency order.
     *
     * Ordering rules:
     *   - Organization seeders run first (foundational: companies, departments)
     *   - Hr reference-data seeders run next (job titles, shifts, work days)
     *   - EmployeeWithDependenciesSeeder runs after reference data
     *   - Everything else runs last (templates, test data, notifications)
     *
     * @return array<class-string<Seeder>>
     */
    protected function discoverModuleSeeders(): array
    {
        $modulesPath = base_path('app/Modules');
        $seeders = [];

        if (!is_dir($modulesPath)) {
            return [];
        }

        foreach (scandir($modulesPath) as $module) {
            if ($module === '.' || $module === '..') {
                continue;
            }

            $seedersPath = "{$modulesPath}/{$module}/Database/Seeders";

            if (!is_dir($seedersPath)) {
                continue;
            }

            foreach (scandir($seedersPath) as $file) {
                if (!str_ends_with($file, '.php')) {
                    continue;
                }

                $class = "App\\Modules\\{$module}\\Database\\Seeders\\" . basename($file, '.php');

                if (class_exists($class) && is_subclass_of($class, Seeder::class)) {
                    $seeders[] = $class;
                }
            }
        }

        return $this->sortByDependency($seeders);
    }

    /**
     * Sort seeders so foundational modules run before dependent ones.
     *
     * Priority tiers (lower runs first):
     *   0 — Organization (companies, departments, branches, locations)
     *   1 — Hr reference data (job titles, shifts, work days, roles)
     *   2 — Hr employee data (EmployeeWithDependenciesSeeder)
     *   3 — Everything else (templates, test data, notifications, leave, payroll)
     */
    protected function sortByDependency(array $seeders): array
    {
        $priority = function (string $class): int {
            $module = $this->extractModule($class);
            $basename = class_basename($class);

            // Tier 0: Organization module — foundational
            if ($module === 'Organization') {
                return 0;
            }

            // Tier 1: Hr reference data
            if ($module === 'Hr' && in_array($basename, [
                'HrRoleSeeder', 'JobTitleSeeder', 'WorkShiftSeeder', 'WorkDaySeeder',
            ])) {
                return 1;
            }

            // Tier 2: Hr employee data (depends on reference data + organization)
            if ($basename === 'EmployeeWithDependenciesSeeder') {
                return 2;
            }

            // Tier 3: Everything else
            return 3;
        };

        usort($seeders, fn (string $a, string $b) => $priority($a) <=> $priority($b));

        return $seeders;
    }

    /**
     * Extract the module name from a fully-qualified seeder class.
     *
     * Example: "App\Modules\Hr\Database\Seeders\JobTitleSeeder" → "Hr"
     */
    protected function extractModule(string $class): string
    {
        $parts = explode('\\', $class);
        $modulesIndex = array_search('Modules', $parts, true);

        return $modulesIndex !== false ? ($parts[$modulesIndex + 1] ?? '') : '';
    }
}
