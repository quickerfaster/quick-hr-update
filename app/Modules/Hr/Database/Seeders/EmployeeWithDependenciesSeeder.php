<?php

namespace App\Modules\Hr\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\EmployeePosition;
use App\Modules\Hr\Models\EmployeePayrollProfile;
use App\Modules\Hr\Models\PaySchedule;
use App\Modules\Hr\Models\Department;
use App\Modules\Hr\Models\JobTitle;
use App\Modules\Hr\Models\Company;
use App\Modules\Hr\Models\Location;
use App\Modules\Hr\Models\Shift;
use App\Modules\Hr\Models\AttendancePolicy;
use Illuminate\Support\Facades\DB;

class EmployeeWithDependenciesSeeder extends Seeder
{
    /**
     * The company to associate all seeded data with.
     */
    protected $company;

    /**
     * Determine if the seeder should run.
     */
    protected function shouldRun(): bool
    {
        return app()->environment('local', 'staging', 'development')
            || ($this->command && $this->command->option('force'));
    }

    /**
     * Run the seeder.
     */
    public function run(): void
    {
        if (!$this->shouldRun()) {
            $this->command->warn('Skipping EmployeeWithDependenciesSeeder (not in local/staging/development and --force not set).');
            return;
        }

        $this->command->info('Seeding 5,000 employees with dependencies...');

        DB::transaction(function () {
            // Step 1: Create static dependencies
            $this->createDependencies();

            // Step 2: Create 5,000 employees
            $employees = $this->createEmployees(5000);

            // Step 3: Create positions
            $this->createPositionsForEmployees($employees);

            // Step 4: Create payroll profiles
            $this->createPayrollProfilesForEmployees($employees);
        });

        $this->command->info('Successfully seeded 5,000 employees!');
    }



    /**
     * Create all lookup tables that EmployeePosition and EmployeePayrollProfile reference.
     * Uses firstOrCreate to be idempotent across multiple runs.
     */
    private function createDependencies(): void
    {
        // Companies - use firstOrCreate to be idempotent
        $companyNames = ['Acme Corporation', 'Globex Industries', 'Initech Solutions'];
        $companies = collect();
        foreach ($companyNames as $i => $name) {
            $company = Company::firstOrCreate(
                ['name' => $name],
                [
                    'subdomain' => strtolower(explode(' ', $name)[0]),
                    'status' => 'active',
                ]
            );
            $companies->push($company);
        }
        $this->company = $companies->first();

        // Additional locations (not tied to a company, but can be used)
        $locationConfigs = [
            ['name' => 'Headquarters', 'code' => 'HQ', 'city' => 'New York', 'address_line_1' => '123 Main Street'],
            ['name' => 'Branch Office A', 'code' => 'BRANCHA', 'city' => 'Los Angeles', 'address_line_1' => '456 Sunset Boulevard'],
            ['name' => 'Branch Office B', 'code' => 'BRANCHB', 'city' => 'Chicago', 'address_line_1' => '789 Lake Shore Drive'],
            ['name' => 'Remote Hub', 'code' => 'REMOTE', 'city' => 'Austin', 'address_line_1' => '321 Tech Park'],
            ['name' => 'Warehouse', 'code' => 'WAREHSE', 'city' => 'Dallas', 'address_line_1' => '654 Industrial Way'],
        ];
        foreach ($locationConfigs as $loc) {
            Location::firstOrCreate(
                ['code' => $loc['code'], 'company_id' => $this->company->id],
                [
                    'name' => $loc['name'],
                    'address_line_1' => $loc['address_line_1'],
                    'city' => $loc['city'],
                    'is_active' => true,
                ]
            );
        }

        // Departments
        $departmentNames = [
            'Human Resources', 'Finance', 'Engineering', 'Sales',
            'Marketing', 'Operations', 'Legal', 'IT',
            'Customer Support', 'Research & Development',
        ];
        foreach ($departmentNames as $name) {
            Department::firstOrCreate(
                ['name' => $name, 'company_id' => $this->company->id],
                [
                    'code' => strtoupper(substr(str_replace(' ', '', $name), 0, 6)),
                    'is_active' => true,
                ]
            );
        }

        // Job titles
        $jobTitleNames = [
            'Software Engineer', 'Product Manager', 'HR Officer', 'Accountant',
            'Sales Representative', 'Marketing Specialist', 'Operations Manager',
            'Legal Counsel', 'IT Support Specialist', 'Customer Service Agent',
            'Senior Developer', 'Team Lead', 'Finance Analyst', 'Data Scientist',
            'Business Analyst', 'Quality Assurance Engineer', 'DevOps Engineer',
            'UX Designer', 'Content Writer', 'Project Manager',
        ];
        foreach ($jobTitleNames as $title) {
            JobTitle::firstOrCreate(
                ['title' => $title, 'company_id' => $this->company->id],
                ['description' => $title . ' role']
            );
        }

        // Shifts
        $shiftConfigs = [
            ['name' => 'Day Shift', 'start_time' => '08:00:00', 'end_time' => '16:00:00'],
            ['name' => 'Night Shift', 'start_time' => '16:00:00', 'end_time' => '00:00:00'],
            ['name' => 'Swing Shift', 'start_time' => '12:00:00', 'end_time' => '20:00:00'],
            ['name' => 'Morning Shift', 'start_time' => '06:00:00', 'end_time' => '14:00:00'],
            ['name' => 'Evening Shift', 'start_time' => '14:00:00', 'end_time' => '22:00:00'],
        ];
        foreach ($shiftConfigs as $shift) {
            Shift::firstOrCreate(
                ['name' => $shift['name'], 'company_id' => $this->company->id],
                [
                    'code' => strtoupper(substr(str_replace(' ', '', $shift['name']), 0, 4)),
                    'start_time' => $shift['start_time'],
                    'end_time' => $shift['end_time'],
                    'is_active' => true,
                ]
            );
        }

        // Attendance policies
        $policyNames = ['Standard', 'Flexible', 'Strict', 'Remote', 'Executive'];
        foreach ($policyNames as $policyName) {
            AttendancePolicy::firstOrCreate(
                ['name' => $policyName, 'company_id' => $this->company->id],
                [
                    'code' => strtoupper(substr($policyName, 0, 4)),
                    'grace_period_minutes' => 15,
                    'early_departure_grace_minutes' => 0,
                    'applies_to_shift_categories' => json_encode(['regular']),
                    'effective_date' => now()->startOfYear()->toDateString(),
                    'is_active' => true,
                ]
            );
        }

        // Pay Schedules
        $this->createPaySchedules();
    }

    /**
     * Create pay schedules directly because no factory is available.
     * Uses firstOrCreate to be idempotent.
     */
    private function createPaySchedules(): void
    {
        $schedules = [
            [
                'company_id' => $this->company->id,
                'name' => 'Monthly',
                'code' => 'MON',
                'frequency' => 'Monthly',
                'first_period_start_date' => now()->startOfYear(),
                'next_pay_date' => now()->addMonth()->startOfMonth(),
                'payment_delay_days' => 5,
                'country_code' => 'US',
                'currency_code' => 'USD',
                'timezone' => 'America/New_York',
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'company_id' => $this->company->id,
                'name' => 'Bi-Weekly',
                'code' => 'BIW',
                'frequency' => 'Bi-weekly',
                'first_period_start_date' => now()->startOfYear(),
                'next_pay_date' => now()->addWeeks(2),
                'payment_delay_days' => 3,
                'country_code' => 'US',
                'currency_code' => 'USD',
                'timezone' => 'America/New_York',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'company_id' => $this->company->id,
                'name' => 'Weekly',
                'code' => 'WEE',
                'frequency' => 'Weekly',
                'first_period_start_date' => now()->startOfYear(),
                'next_pay_date' => now()->addWeek(),
                'payment_delay_days' => 2,
                'country_code' => 'US',
                'currency_code' => 'USD',
                'timezone' => 'America/New_York',
                'is_active' => true,
                'is_default' => false,
            ],
        ];

        foreach ($schedules as $schedule) {
            PaySchedule::firstOrCreate(
                ['name' => $schedule['name'], 'company_id' => $schedule['company_id']],
                $schedule
            );
        }
    }

    /**
     * Create employees with sequential employee numbers.
     * Uses firstOrCreate to be idempotent.
     */
    private function createEmployees(int $count): \Illuminate\Support\Collection
    {
        $employees = collect();

        for ($i = 1; $i <= $count; $i++) {
            $employeeNumber = 'EMP' . str_pad($i, 4, '0', STR_PAD_LEFT);

            $employee = Employee::firstOrCreate(
                ['employee_number' => $employeeNumber],
                [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->unique()->safeEmail(),
                    'phone' => fake()->phoneNumber(),
                    'company_id' => $this->company->id,
                    'hire_date' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
                ]
            );

            $employees->push($employee);
        }

        return $employees;
    }

    /**
     * Create one EmployeePosition record per employee.
     * Uses firstOrCreate to be idempotent.
     */
    private function createPositionsForEmployees(\Illuminate\Support\Collection $employees): void
    {
        // Pre-fetch all dependency collections for random selection
        $jobTitles = JobTitle::all();
        $departments = Department::all();
        $attendancePolicies = AttendancePolicy::all();
        $locations = Location::all();
        $shifts = Shift::all();
        $paySchedules = PaySchedule::all();

        foreach ($employees as $employee) {
            // Check if position already exists for this employee
            $existing = EmployeePosition::where('employee_id', $employee->id)->first();
            if ($existing) {
                continue;
            }

            // Random selections
            $jobTitle = $jobTitles->random();
            $department = $departments->random();
            $attendancePolicy = $attendancePolicies->random();
            $location = $locations->random();
            $shift = $shifts->random();
            $paySchedule = $paySchedules->random();

            // Decide pay type (70% salaried, 30% hourly)
            $payType = fake()->boolean(70) ? 'salaried_full' : 'hourly';
            $baseSalary = $payType === 'salaried_full' ? fake()->randomFloat(2, 40000, 120000) : 0;
            $hourlyRate = $payType === 'hourly' ? fake()->randomFloat(2, 15, 50) : 0;

            EmployeePosition::firstOrCreate(
                ['employee_id' => $employee->id],
                [
                    'company_id' => $this->company->id,
                    'job_title_id' => $jobTitle->id,
                    'department_id' => $department->id,
                    'attendance_policy_id' => $attendancePolicy->id,
                    'location_id' => $location->id,
                    'shift_id' => $shift->id,
                    'pay_schedule_id' => $paySchedule->id,
                    'manager_id' => null,
                    'reports_to' => null,
                    'pay_type' => $payType,
                    'hourly_rate' => $hourlyRate,
                    'base_salary' => $baseSalary,
                    'salary_currency' => fake()->randomElement(['USD', 'EUR', 'GBP']),
                    'pay_frequency' => $paySchedule->frequency,
                    'employment_status' => 'Active',
                    'cost_center' => fake()->optional(0.5)->bothify('CC-####'),
                    'work_email' => fake()->optional(0.8)->companyEmail(),
                    'work_phone_extension' => fake()->optional(0.3)->numerify('###'),
                ]
            );
        }
    }

    /**
     * Create one EmployeePayrollProfile per employee.
     * Uses firstOrCreate to be idempotent.
     */
    private function createPayrollProfilesForEmployees(\Illuminate\Support\Collection $employees): void
    {
        $paySchedules = PaySchedule::all();

        foreach ($employees as $employee) {
            // Check if payroll profile already exists for this employee
            $existing = EmployeePayrollProfile::where('employee_id', $employee->id)->first();
            if ($existing) {
                continue;
            }

            // Random pay schedule
            $paySchedule = $paySchedules->random();

            EmployeePayrollProfile::firstOrCreate(
                ['employee_id' => $employee->id],
                [
                    'company_id' => $this->company->id,
                    'pay_schedule_id' => $paySchedule->id,
                    'bank_account_name' => $employee->first_name . ' ' . $employee->last_name,
                    'bank_name' => fake()->company() . ' Bank',
                    'bank_account_number' => fake()->bankAccountNumber(),
                    'bank_routing_number' => fake()->regexify('[0-9]{9}'),
                    'bank_iban' => fake()->optional(0.5)->iban('US'),
                    'bank_swift' => fake()->optional(0.3)->swiftBicNumber(),
                    'account_type' => fake()->randomElement(['checking', 'savings']),
                    'payment_method' => 'bank_transfer',
                    'tax_filing_status' => fake()->randomElement(['single', 'married', 'head_of_household']),
                    'allowances' => fake()->numberBetween(0, 5),
                    'extra_withholding' => fake()->randomFloat(2, 0, 200),
                    'is_exempt_from_federal_tax' => fake()->boolean(10),
                    'override_country_code' => 'US',
                    'override_state_code' => fake()->stateAbbr(),
                    'currency_code' => 'USD',
                    'effective_date' => $employee->hire_date,
                    'expiry_date' => null,
                    'is_active' => true,
                ]
            );
        }
    }


}
