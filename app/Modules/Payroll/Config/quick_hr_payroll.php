<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payroll Processing Settings
    |--------------------------------------------------------------------------
    |
    | These values control how the payroll batch jobs behave.
    | Adjust them to fit your server's capacity and cron limitations.
    |
    */

    // Number of employees per batch job (adjust based on memory/time)
    'batch_size' => env('PAYROLL_BATCH_SIZE', 100),

    // Timeout (seconds) for each batch job – must be < cron/system limit (60s on cPanel)
    'batch_timeout' => env('PAYROLL_BATCH_TIMEOUT', 60),

    // Number of retry attempts for a failed batch job (1 = no retry)
    'batch_tries' => env('PAYROLL_BATCH_TRIES', 1),

    // Finalisation delay per batch (seconds) – used to estimate when all batches are done
    'finalization_delay_per_batch' => env('PAYROLL_FINALIZATION_DELAY_PER_BATCH', 5),

    // Additional buffer time for finalisation (seconds)
    'finalization_buffer' => env('PAYROLL_FINALIZATION_BUFFER', 30),


    /*
    |--------------------------------------------------------------------------
    | Attendance Integration
    |--------------------------------------------------------------------------
    |
    | Enable this to pull attendance records (clock events, sessions) for
    | employees with 'salaried_daily' and 'hourly' pay types.
    | If disabled, all employees are treated as 'salaried_full'.
    |
    */
    'attendance_integration' => [
        'enabled' => env('PAYROLL_ATTENDANCE_INTEGRATION_ENABLED', true),
    ],

    // Pay periods per year by frequency (canonical CamelCase keys).
    // HasPayPeriods::getPeriodsPerYear() normalises incoming frequency
    // strings (lowercase, underscores, hyphens) before looking them up.
    'pay_periods_per_year' => [
        'Monthly'      => 12,
        'Semi-monthly' => 24,
        'Bi-weekly'    => 26,
        'Weekly'       => 52,
        'Daily'        => 260,
        'Quarterly'    => 4,
        'Yearly'       => 1,
    ],

    // Default overtime multipliers (fallback if policy doesn't provide)
    'default_overtime_multiplier' => env('PAYROLL_DEFAULT_OT_MULTIPLIER', 1.5),
    'default_double_time_multiplier' => env('PAYROLL_DEFAULT_DT_MULTIPLIER', 2.0),

    // Group Life Assurance employer rate (% of gross pay). This is an
    // insurer-negotiated premium, so treat it as a configurable default —
    // companies override it via a company-specific policy.
    'group_life_employer_rate' => env('PAYROLL_GROUP_LIFE_EMPLOYER_RATE', 1.5),

    // Proration basis default ('calendar' or 'working_days')
    'default_proration_basis' => env('PAYROLL_DEFAULT_PRORATION_BASIS', 'calendar'),

    /*
    |--------------------------------------------------------------------------
    | Country payroll strategies
    |--------------------------------------------------------------------------
    | Maps a country code to its CountryPayrollCalculator implementation.
    | Unregistered countries fall back to the default (no-op) strategy.
    */
    'country_calculators' => [
        'NG' => \App\Modules\Payroll\Services\Payroll\Countries\NigeriaPayrollCalculator::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Salary component groups (per country)
    |--------------------------------------------------------------------------
    | Defines which salary components aggregate into a named group, used by
    | policy bases such as 'pensionable'.
    */
    'component_groups' => [
        'NG' => [
            'pensionable' => ['basic', 'housing', 'transport'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Salary component types (per country)
    |--------------------------------------------------------------------------
    | Available salary component labels, shown in the salary-components editor.
    | Keys are the canonical component_key values stored per employee.
    */
    'salary_component_types' => [
        'NG' => [
            'basic'          => 'Basic Salary',
            'housing'        => 'Housing Allowance',
            'transport'      => 'Transport Allowance',
            'other_allowance' => 'Other Allowance',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Country tax rules
    |--------------------------------------------------------------------------
    | Per-country tax constants (minimum-wage exemption, rent relief, etc.).
    */
    'country_tax_rules' => [
        'NG' => [
            'minimum_wage_exemption' => 70000,   // monthly ₦
            'rent_relief_rate'       => 0.20,    // 20% of annual rent
            'rent_relief_cap'        => 500000,  // annual ₦ cap
        ],
    ],

];
