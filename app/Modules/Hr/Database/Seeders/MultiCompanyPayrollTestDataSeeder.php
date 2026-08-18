<?php

namespace App\Modules\Hr\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Hr\Models\Company;
use App\Modules\Hr\Models\PaySchedule;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\EmployeePosition;
use App\Modules\Hr\Models\EmployeePayrollProfile;
use App\Modules\Hr\Models\PayrollRun;
use App\Modules\Hr\Models\PayrollRunAdjustment;
use App\Modules\Hr\Models\EmployeeAdjustmentProfile;
use App\Modules\Hr\Models\Department;
use App\Modules\Hr\Models\JobTitle;
use App\Modules\Hr\Models\Location;
use App\Modules\Hr\Models\Shift;
use App\Modules\Hr\Models\AttendancePolicy;
use Illuminate\Support\Facades\DB;
use Faker\Factory as FakerFactory;

class MultiCompanyPayrollTestDataSeeder extends Seeder
{
    /**
     * All seeded companies, keyed by index (1-based).
     */
    protected array $companies = [];

    /**
     * All seeded pay schedules, keyed by company index (1-based).
     */
    protected array $paySchedules = [];

    /**
     * All seeded employees, keyed by company index, then employee index (0-based).
     */
    protected array $employees = [];

    /**
     * Company configurations for the 4 test companies.
     */
    protected array $companyConfigs = [];

    /**
     * Faker instance.
     */
    protected $faker;

    /**
     * Run the seeder.
     */
    public function run(): void
    {
        // Guard: only run in safe environments
        if (!$this->shouldRun()) {
            $this->command->warn('Skipping MultiCompanyPayrollTestDataSeeder (not in local/staging/development and --force not set).');
            return;
        }

        // Initialize Faker
        $this->faker = FakerFactory::create();

        if (app()->runningInConsole() && $this->command) {
            $this->command->info('Seeding multi-company payroll test data...');
        }

        DB::transaction(function () {
            // Step 1: Companies
            $this->createCompanies();

            // Step 2: Pay Schedules
            $this->createPaySchedules();

            // Step 3: Dependencies (departments, job titles, locations, shifts, attendance policies)
            $this->createDependencies();

            // Step 4: Employees & Positions (100 total, 25 per company)
            $this->createEmployeesAndPositions();

            // Step 5: Payroll Profiles
            $this->createPayrollProfiles();

            // Step 6: Payroll Run & Adjustments (for Company 1)
            $this->createPayrollRunWithAdjustments();

            // Step 7: Recurring Employee Adjustment Profiles
            $this->createEmployeeAdjustmentProfiles();
        });

        if (app()->runningInConsole() && $this->command) {
            $this->command->info('Successfully seeded multi-company payroll test data!');
            $this->command->info('  - 4 Companies');
            $this->command->info('  - 4 Pay Schedules');
            $this->command->info('  - 100 Employees');
            $this->command->info('  - 100 Positions');
            $this->command->info('  - 100 Payroll Profiles');
            $this->command->info('  - 1 Draft Payroll Run with 8 Adjustments');
            $this->command->info('  - 3 Recurring Adjustment Profiles');
        }
    }

    /**
     * Determine if the seeder should run.
     */
    protected function shouldRun(): bool
    {
        return app()->environment('local', 'staging', 'development')
            || ($this->command && $this->command->option('force'));
    }

    /**
     * Company configuration data.
     */
    protected function getCompanyConfigs(): array
    {
        return [
            1 => [
                'name'                   => 'Alpha Holdings Ltd',
                'subdomain'              => 'alpha',
                'level'                  => 'parent',
                'billing_country_code'   => 'NG',
                'currency_code'          => 'NGN',
                'timezone'               => 'Africa/Lagos',
                'billing_city'           => 'Lagos',
                'billing_state_code'     => 'LA',
                'billing_address_line_1' => '1 Marina Boulevard',
                'billing_address_line_2' => 'Victoria Island',
                'billing_postal_code'    => '101241',
                'billing_email'          => 'billing@alphaholdings.test',
                'schedule_frequency'     => 'Monthly',
                'schedule_code'          => 'ALPHA-MON',
                'schedule_name'          => 'Alpha Monthly Payroll',
                'schedule_country_code'  => 'NG',
                'schedule_currency_code' => 'NGN',
                'schedule_timezone'      => 'Africa/Lagos',
                'next_pay_date'          => now()->endOfMonth(),
            ],
            2 => [
                'name'                   => 'Beta Manufacturing Inc',
                'subdomain'              => 'beta',
                'level'                  => 'division',
                'billing_country_code'   => 'NG',
                'currency_code'          => 'NGN',
                'timezone'               => 'Africa/Lagos',
                'billing_city'           => 'Abuja',
                'billing_state_code'     => 'FC',
                'billing_address_line_1' => '10 Industrial Avenue',
                'billing_address_line_2' => 'Idu Industrial Layout',
                'billing_postal_code'    => '900104',
                'billing_email'          => 'billing@betamanufacturing.test',
                'schedule_frequency'     => 'Bi-weekly',
                'schedule_code'          => 'BETA-BIW',
                'schedule_name'          => 'Beta Bi-Weekly Payroll',
                'schedule_country_code'  => 'NG',
                'schedule_currency_code' => 'NGN',
                'schedule_timezone'      => 'Africa/Lagos',
                'next_pay_date'          => now()->addWeeks(2),
            ],
            3 => [
                'name'                   => 'Gamma Services LLC',
                'subdomain'              => 'gamma',
                'level'                  => 'division',
                'billing_country_code'   => 'US',
                'currency_code'          => 'USD',
                'timezone'               => 'America/New_York',
                'billing_city'           => 'New York',
                'billing_state_code'     => 'NY',
                'billing_address_line_1' => '350 Fifth Avenue',
                'billing_address_line_2' => 'Suite 4200',
                'billing_postal_code'    => '10118',
                'billing_email'          => 'billing@gammaservices.test',
                'schedule_frequency'     => 'Weekly',
                'schedule_code'          => 'GAMMA-WEE',
                'schedule_name'          => 'Gamma Weekly Payroll',
                'schedule_country_code'  => 'US',
                'schedule_currency_code' => 'USD',
                'schedule_timezone'      => 'America/New_York',
                'next_pay_date'          => now()->addWeek()->startOfWeek(),
            ],
            4 => [
                'name'                   => 'Delta Technologies Corp',
                'subdomain'              => 'delta',
                'level'                  => 'division',
                'billing_country_code'   => 'US',
                'currency_code'          => 'USD',
                'timezone'               => 'America/Los_Angeles',
                'billing_city'           => 'San Francisco',
                'billing_state_code'     => 'CA',
                'billing_address_line_1' => '1 Market Street',
                'billing_address_line_2' => 'Spear Tower',
                'billing_postal_code'    => '94105',
                'billing_email'          => 'billing@deltatech.test',
                'schedule_frequency'     => 'Monthly',
                'schedule_code'          => 'DELTA-MON',
                'schedule_name'          => 'Delta Monthly Payroll',
                'schedule_country_code'  => 'US',
                'schedule_currency_code' => 'USD',
                'schedule_timezone'      => 'America/Los_Angeles',
                'next_pay_date'          => now()->endOfMonth(),
            ],
        ];
    }

    /**
     * First names pool for generating employees.
     */
    protected function getFirstNames(): array
    {
        return [
            'Chidi', 'Amina', 'Emeka', 'Folake', 'Obinna', 'Ngozi', 'Tunde', 'Chinelo',
            'Ifeanyi', 'Adaeze', 'Uchenna', 'Yetunde', 'Kayode', 'Bolaji', 'Oluwaseun',
            'Zainab', 'Ibrahim', 'Fatima', 'Yakubu', 'Halima', 'Abdul', 'Blessing',
            'David', 'Grace', 'Michael', 'Sarah', 'James', 'Esther', 'Joseph', 'Mary',
            'Samuel', 'Ruth', 'Daniel', 'Joy', 'John', 'Peace', 'Peter', 'Faith',
            'Matthew', 'Mercy', 'Andrew', 'Hope', 'Philip', 'Gloria', 'Stephen', 'Victoria',
            'Victor', 'Patience', 'Emmanuel', 'Charity',
        ];
    }

    /**
     * Last names pool for generating employees.
     */
    protected function getLastNames(): array
    {
        return [
            'Okafor', 'Abubakar', 'Nwachukwu', 'Adebayo', 'Okonkwo', 'Mohammed', 'Balogun',
            'Obi', 'Eze', 'Bello', 'Nnamdi', 'Suleiman', 'Akintola', 'Chukwuma', 'Adamu',
            'Olayinka', 'Odili', 'Nwosu', 'Ajayi', 'Ugwu', 'Popoola', 'Akinwale', 'Dangote',
            'Tafawa', 'Ekong', 'Williams', 'Johnson', 'Brown', 'Smith', 'Davis', 'Miller',
            'Wilson', 'Moore', 'Taylor', 'Anderson', 'Thomas', 'Jackson', 'White', 'Harris',
            'Martin', 'Thompson', 'Garcia', 'Martinez', 'Robinson', 'Clark', 'Rodriguez',
            'Lewis', 'Lee', 'Walker', 'Hall',
        ];
    }

    /**
     * Department names (same as DepartmentFactory list).
     */
    protected function getDepartmentNames(): array
    {
        return [
            'Human Resources',
            'Finance',
            'Engineering',
            'Sales',
            'Marketing',
            'Operations',
            'Legal',
            'IT',
            'Customer Support',
        ];
    }

    /**
     * Create 4 active companies with distinct names, subdomains, and billing info.
     */
    protected function createCompanies(): void
    {
        $this->companyConfigs = $this->getCompanyConfigs();

        foreach ($this->companyConfigs as $index => $config) {
            $company = Company::firstOrCreate(
                ['name' => $config['name']],
                [
                    'subdomain'              => $config['subdomain'],
                    'level'                  => $config['level'],
                    'status'                 => 'active',
                    'billing_email'          => $config['billing_email'],
                    'billing_address_line_1' => $config['billing_address_line_1'],
                    'billing_address_line_2' => $config['billing_address_line_2'],
                    'billing_city'           => $config['billing_city'],
                    'billing_state_code'     => $config['billing_state_code'],
                    'billing_postal_code'    => $config['billing_postal_code'],
                    'billing_country_code'   => $config['billing_country_code'],
                    'timezone'               => $config['timezone'],
                    'currency_code'          => $config['currency_code'],
                    'is_placeholder'         => false,
                ]
            );

            $this->companies[$index] = $company;
        }
    }

    /**
     * Create 4 distinct pay schedules, one per company.
     */
    protected function createPaySchedules(): void
    {
        foreach ($this->companyConfigs as $index => $config) {
            $company = $this->companies[$index];

            $schedule = PaySchedule::firstOrCreate(
                ['code' => $config['schedule_code']],
                [
                    'company_id'               => $company->id,
                    'name'                     => $config['schedule_name'],
                    'frequency'                => $config['schedule_frequency'],
                    'first_period_start_date'  => now()->startOfYear(),
                    'next_pay_date'            => $config['next_pay_date'],
                    'payment_delay_days'       => 3,
                    'country_code'             => $config['schedule_country_code'],
                    'currency_code'            => $config['schedule_currency_code'],
                    'timezone'                 => $config['schedule_timezone'],
                    'is_active'                => true,
                    'is_default'               => $index === 1,
                ]
            );

            $this->paySchedules[$index] = $schedule;
        }
    }

    /**
     * Create departments, job titles, locations, shifts, and attendance policies
     * for each company.
     */
    protected function createDependencies(): void
    {
        foreach ($this->companies as $index => $company) {
            $config = $this->companyConfigs[$index];

            // Departments
            foreach ($this->getDepartmentNames() as $deptName) {
                Department::firstOrCreate(
                    ['name' => $deptName, 'company_id' => $company->id],
                    [
                        'code'        => strtoupper(substr(str_replace(' ', '', $deptName), 0, 6)) . '-C' . $index,
                        'is_active'   => true,
                        'description' => $deptName . ' Department for ' . $config['name'],
                    ]
                );
            }

            // Job Titles (title is globally unique, so append company suffix)
            $baseJobTitles = [
                'Software Engineer', 'Product Manager', 'HR Officer', 'Accountant',
                'Sales Representative', 'Marketing Specialist', 'Operations Manager',
                'Legal Counsel', 'IT Support Specialist', 'Customer Service Agent',
                'Senior Developer', 'Team Lead', 'Finance Analyst', 'Data Scientist',
                'Business Analyst', 'Quality Assurance Engineer', 'DevOps Engineer',
                'UX Designer', 'Content Writer', 'Project Manager',
            ];
            foreach ($baseJobTitles as $baseTitle) {
                $title = $baseTitle . ' (C' . $index . ')';
                JobTitle::firstOrCreate(
                    ['title' => $title, 'company_id' => $company->id],
                    [
                        'description' => $baseTitle . ' role at ' . $config['name'],
                    ]
                );
            }

            // Locations
            $locationConfigs = [
                [
                    'name'            => $config['name'] . ' HQ',
                    'code'            => 'HQ-C' . $index,
                    'is_headquarters' => true,
                ],
                [
                    'name'            => $config['name'] . 'Branch Office',
                    'code'            => 'BR-C' . $index,
                    'is_headquarters' => false,
                ],
            ];
            foreach ($locationConfigs as $loc) {
                Location::firstOrCreate(
                    ['code' => $loc['code'], 'company_id' => $company->id],
                    [
                        'name'            => $loc['name'],
                        'address_line_1'  => $config['billing_address_line_1'],
                        'city'            => $config['billing_city'],
                        'country_code'    => $config['billing_country_code'],
                        'timezone'        => $config['timezone'],
                        'is_active'       => true,
                        'is_headquarters' => $loc['is_headquarters'],
                    ]
                );
            }

            // Shifts
            $shiftNames = [
                'Day Shift'     => ['start' => '08:00:00', 'end' => '16:00:00'],
                'Night Shift'   => ['start' => '16:00:00', 'end' => '00:00:00'],
                'Swing Shift'   => ['start' => '12:00:00', 'end' => '20:00:00'],
            ];
            foreach ($shiftNames as $shiftName => $times) {
                Shift::firstOrCreate(
                    ['name' => $shiftName, 'company_id' => $company->id],
                    [
                        'code'          => strtoupper(substr(str_replace(' ', '', $shiftName), 0, 4)) . '-C' . $index,
                        'start_time'    => $times['start'],
                        'end_time'      => $times['end'],
                        'is_active'     => true,
                    ]
                );
            }

            // Attendance Policies (using DB::table to bypass Eloquent model defaults for JSON column)
            $policyNames = ['Standard', 'Flexible', 'Strict'];
            foreach ($policyNames as $policyName) {
                $policyCode = strtoupper(substr($policyName, 0, 4)) . '-C' . $index;
                $existing = DB::table('attendance_policies')
                    ->where('name', $policyName)
                    ->where('company_id', $company->id)
                    ->first();

                if (!$existing) {
                    DB::table('attendance_policies')->insert([
                        'company_id'                   => $company->id,
                        'name'                         => $policyName,
                        'code'                         => $policyCode,
                        'grace_period_minutes'         => 15,
                        'early_departure_grace_minutes'=> 0,
                        'applies_to_shift_categories'  => json_encode(['regular']),
                        'effective_date'               => now()->startOfYear()->toDateString(),
                        'is_active'                    => true,
                        'created_at'                   => now(),
                        'updated_at'                   => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Create 100 employees (25 per company) with positions.
     */
    protected function createEmployeesAndPositions(): void
    {
        $firstNames = $this->getFirstNames();
        $lastNames = $this->getLastNames();

        foreach ($this->companies as $companyIndex => $company) {
            $config = $this->companyConfigs[$companyIndex];
            $schedule = $this->paySchedules[$companyIndex];
            $currencyCode = $config['currency_code'];

            // Pre-fetch dependencies for this company
            $departments = Department::where('company_id', $company->id)->get();
            $jobTitles = JobTitle::where('company_id', $company->id)->get();
            $locations = Location::where('company_id', $company->id)->get();
            $shifts = Shift::where('company_id', $company->id)->get();
            $attendancePolicies = AttendancePolicy::where('company_id', $company->id)->get();

            $this->employees[$companyIndex] = [];

            for ($i = 1; $i <= 25; $i++) {
                $employeeNumber = sprintf('EMP-MC-%d-%03d', $companyIndex, $i);
                $firstName = $firstNames[($companyIndex * 25 + $i) % count($firstNames)];
                $lastName = $lastNames[($companyIndex * 25 + $i) % count($lastNames)];

                // Determine status and pay type based on index
                $status = $this->getStatusForIndex($i);
                $payType = $this->getPayTypeForIndex($i);

                // Base salary / hourly rate based on currency
                $baseSalary = 0;
                $hourlyRate = 0;

                if ($payType === 'salaried_full') {
                    $baseSalary = $currencyCode === 'NGN'
                        ? $this->faker->numberBetween(50000, 500000)
                        : $this->faker->numberBetween(1500, 8000);
                } elseif ($payType === 'salaried_daily') {
                    $baseSalary = $currencyCode === 'NGN'
                        ? $this->faker->numberBetween(50000, 300000)
                        : $this->faker->numberBetween(1000, 5000);
                } elseif ($payType === 'hourly') {
                    $hourlyRate = $currencyCode === 'NGN'
                        ? $this->faker->numberBetween(500, 3000)
                        : $this->faker->numberBetween(15, 50);
                }

                // Edge case 1: Employee #1 per company — zero-hour contract (base_salary = 0)
                if ($i === 1 && in_array($payType, ['salaried_full', 'salaried_daily'])) {
                    $baseSalary = 0;
                }

                // Edge case 4: Employee #24 per company — terminated mid-cycle
                $hireDate = now()->subMonths($this->faker->numberBetween(6, 36))->toDateString();
                if ($i === 15) {
                    // Edge case 2: hired on the 15th of current month
                    $hireDate = now()->startOfMonth()->addDays(14)->toDateString();
                }

                // Create Employee
                $employee = Employee::firstOrCreate(
                    ['employee_number' => $employeeNumber],
                    [
                        'first_name' => $firstName,
                        'last_name'  => $lastName,
                        'email'      => strtolower($firstName . '.' . $lastName . '.' . $config['subdomain']) . '@test.payroll',
                        'phone'      => $this->faker->phoneNumber(),
                        'company_id' => $company->id,
                        'hire_date'  => $hireDate,
                    ]
                );

                // Determine pay frequency to match schedule
                $payFrequency = match ($schedule->frequency) {
                    'Monthly' => 'Monthly',
                    'Bi-weekly' => 'Bi-weekly',
                    'Weekly' => 'Weekly',
                    default => 'Monthly',
                };

                // Create EmployeePosition
                EmployeePosition::firstOrCreate(
                    ['employee_id' => $employee->id, 'company_id' => $company->id],
                    [
                        'job_title_id'         => $jobTitles->random()->id,
                        'department_id'        => $departments->random()->id,
                        'employment_status'    => $status,
                        'pay_type'             => $payType,
                        'base_salary'          => $baseSalary,
                        'hourly_rate'          => $hourlyRate,
                        'salary_currency'      => $currencyCode,
                        'pay_frequency'        => $payFrequency,
                        'pay_schedule_id'      => $schedule->id,
                        'location_id'          => $locations->random()->id,
                        'shift_id'             => $shifts->random()->id,
                        'attendance_policy_id' => $attendancePolicies->random()->id,
                        'cost_center'          => 'CC-' . str_pad($companyIndex, 3, '0', STR_PAD_LEFT) . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                        'work_email'           => strtolower($firstName . '.' . $lastName) . '@' . $config['subdomain'] . '.test',
                    ]
                );

                $this->employees[$companyIndex][] = $employee;
            }
        }
    }

    /**
     * Get the employment status for an employee index (1-25).
     *
     * Distribution: 18 Active, 3 On Leave, 2 Terminated, 2 Suspended
     */
    protected function getStatusForIndex(int $index): string
    {
        return match (true) {
            $index <= 18  => 'Active',
            $index <= 21  => 'On Leave',    // 19, 20, 21
            $index <= 23  => 'Terminated',   // 22, 23
            default       => 'Suspended',    // 24, 25
        };
    }

    /**
     * Get the pay type for an employee index (1-25).
     *
     * Distribution: 15 salaried_full, 5 salaried_daily, 5 hourly
     */
    protected function getPayTypeForIndex(int $index): string
    {
        return match (true) {
            $index <= 15  => 'salaried_full',
            $index <= 20  => 'salaried_daily',
            default       => 'hourly',
        };
    }

    /**
     * Create payroll profiles for all employees.
     */
    protected function createPayrollProfiles(): void
    {
        $bankNames = ['Access Bank', 'GTBank', 'First Bank', 'Zenith Bank', 'UBA'];
        $bankCodes = ['044', '058', '011', '057', '033'];

        foreach ($this->companies as $companyIndex => $company) {
            $config = $this->companyConfigs[$companyIndex];
            $schedule = $this->paySchedules[$companyIndex];
            $currencyCode = $config['currency_code'];

            foreach ($this->employees[$companyIndex] as $eeIndex => $employee) {
                $bankIndex = $eeIndex % 5;

                EmployeePayrollProfile::firstOrCreate(
                    ['employee_id' => $employee->id],
                    [
                        'company_id'          => $company->id,
                        'pay_schedule_id'     => $schedule->id,
                        'payment_method'      => 'bank_transfer',
                        'bank_name'           => $bankNames[$bankIndex],
                        'bank_code'           => $bankCodes[$bankIndex],
                        'bank_account_name'   => $employee->first_name . ' ' . $employee->last_name,
                        'bank_account_number' => $this->faker->numerify('##########'),
                        'bank_sort_code'      => $this->faker->numerify('##-##-##'),
                        'bank_routing_number' => $this->faker->regexify('[0-9]{9}'),
                        'account_type'        => $this->faker->randomElement(['checking', 'savings']),
                        'currency_code'       => $currencyCode,
                        'effective_date'      => $employee->hire_date,
                        'is_active'           => true,
                    ]
                );
            }
        }
    }

    /**
     * Create a draft payroll run for Company 1 (Alpha Holdings) with adjustments
     * covering various edge cases.
     */
    protected function createPayrollRunWithAdjustments(): void
    {
        $company = $this->companies[1];
        $schedule = $this->paySchedules[1];
        $employees = $this->employees[1];

        // Current month period
        $periodStart = now()->startOfMonth()->toDateString();
        $periodEnd = now()->endOfMonth()->toDateString();

        $payrollRun = PayrollRun::firstOrCreate(
            [
                'company_id' => $company->id,
                'title'      => 'Alpha Holdings - ' . now()->format('F Y') . ' Test Payroll',
            ],
            [
                'pay_schedule_id'   => $schedule->id,
                'period_start'      => $periodStart,
                'period_end'        => $periodEnd,
                'status'            => 'draft',
                'calculation_status' => 'pending',
                'current_step'      => 1,
                'base_currency'     => 'NGN',
                'is_multi_company'  => false,
                'total_employees'   => 25,
                'processed_employees' => 0,
            ]
        );

        // Payroll Run Adjustments for Company 1 employees
        // Employees: indices 0-24 (Active: 0-17, On Leave: 18-20, Terminated: 21-22, Suspended: 23-24)

        // 3 Bonus adjustments
        PayrollRunAdjustment::firstOrCreate(
            ['payroll_run_id' => $payrollRun->id, 'employee_id' => $employees[0]->id, 'type' => 'bonus', 'label' => 'Quarterly Performance Bonus'],
            ['company_id' => $company->id, 'amount' => 50000, 'note' => 'Q3 performance bonus for exceeding targets', 'source_type' => 'manual']
        );

        PayrollRunAdjustment::firstOrCreate(
            ['payroll_run_id' => $payrollRun->id, 'employee_id' => $employees[1]->id, 'type' => 'bonus', 'label' => 'Holiday Bonus'],
            ['company_id' => $company->id, 'amount' => 100000, 'note' => 'End of year holiday bonus', 'source_type' => 'manual']
        );

        PayrollRunAdjustment::firstOrCreate(
            ['payroll_run_id' => $payrollRun->id, 'employee_id' => $employees[2]->id, 'type' => 'bonus', 'label' => 'Spot Award'],
            ['company_id' => $company->id, 'amount' => 25000, 'note' => 'Exceptional performance on critical project', 'source_type' => 'review']
        );

        // 2 Deduction adjustments
        PayrollRunAdjustment::firstOrCreate(
            ['payroll_run_id' => $payrollRun->id, 'employee_id' => $employees[3]->id, 'type' => 'deduction', 'label' => 'Loan Repayment'],
            ['company_id' => $company->id, 'amount' => 15000, 'note' => 'Staff loan repayment - installment 3 of 12', 'source_type' => 'manual']
        );

        PayrollRunAdjustment::firstOrCreate(
            ['payroll_run_id' => $payrollRun->id, 'employee_id' => $employees[4]->id, 'type' => 'deduction', 'label' => 'Uniform Cost Recovery'],
            ['company_id' => $company->id, 'amount' => 5000, 'note' => 'Recovery for uniform provided in August', 'source_type' => 'manual']
        );

        // 1 Correction adjustment for an employee with a mid-month salary change
        // Use employee #15 (index 14) — hired mid-month (hire_date is 15th)
        PayrollRunAdjustment::firstOrCreate(
            ['payroll_run_id' => $payrollRun->id, 'employee_id' => $employees[14]->id, 'type' => 'correction', 'label' => 'Mid-Month Salary Proration Correction'],
            ['company_id' => $company->id, 'amount' => 20000, 'note' => 'Correcting salary to reflect mid-month hire on the 15th', 'source_type' => 'manual']
        );

        // 1 Commission for a sales employee
        PayrollRunAdjustment::firstOrCreate(
            ['payroll_run_id' => $payrollRun->id, 'employee_id' => $employees[5]->id, 'type' => 'commission', 'label' => 'Q3 Sales Commission'],
            ['company_id' => $company->id, 'amount' => 75000, 'note' => 'Commission for closing Enterprise deal with ABC Corp', 'source_type' => 'review']
        );

        // 1 Reimbursement for travel expense
        PayrollRunAdjustment::firstOrCreate(
            ['payroll_run_id' => $payrollRun->id, 'employee_id' => $employees[6]->id, 'type' => 'reimbursement', 'label' => 'Travel Expense Reimbursement'],
            ['company_id' => $company->id, 'amount' => 12500, 'note' => 'Business travel to Abuja for client meeting — transport and accommodation', 'source_type' => 'reimbursement']
        );
    }

    /**
     * Create recurring EmployeeAdjustmentProfile entries for edge-case testing.
     */
    protected function createEmployeeAdjustmentProfiles(): void
    {
        $company = $this->companies[1];
        $employees = $this->employees[1];

        // 2 employees with monthly fixed-amount deductions
        // Employee #8 (index 7): pension voluntary
        EmployeeAdjustmentProfile::firstOrCreate(
            ['employee_id' => $employees[7]->id, 'label' => 'Voluntary Pension Contribution'],
            [
                'company_id'       => $company->id,
                'type'             => 'deduction',
                'calculation_type' => 'fixed',
                'value'            => 10000,
                'effective_date'   => now()->startOfYear()->toDateString(),
                'is_active'        => true,
            ]
        );

        // Employee #9 (index 8): union dues
        EmployeeAdjustmentProfile::firstOrCreate(
            ['employee_id' => $employees[8]->id, 'label' => 'Union Dues'],
            [
                'company_id'       => $company->id,
                'type'             => 'deduction',
                'calculation_type' => 'fixed',
                'value'            => 5000,
                'effective_date'   => now()->startOfYear()->toDateString(),
                'is_active'        => true,
            ]
        );

        // 1 employee with percentage-based bonus (5% of base salary)
        EmployeeAdjustmentProfile::firstOrCreate(
            ['employee_id' => $employees[9]->id, 'label' => 'Performance Bonus'],
            [
                'company_id'       => $company->id,
                'type'             => 'earning',
                'calculation_type' => 'percentage',
                'value'            => 5,
                'effective_date'   => now()->startOfYear()->toDateString(),
                'is_active'        => true,
            ]
        );
    }
}
