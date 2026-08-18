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

    // Pay periods per year by frequency
    'pay_periods_per_year' => [
        'Monthly'      => 12,
        'Semi-monthly' => 24,
        'Bi-weekly'    => 26,
        'Weekly'       => 52,
        'Daily'        => 260,
    ],

    // Default overtime multipliers (fallback if policy doesn't provide)
    'default_overtime_multiplier' => env('PAYROLL_DEFAULT_OT_MULTIPLIER', 1.5),
    'default_double_time_multiplier' => env('PAYROLL_DEFAULT_DT_MULTIPLIER', 2.0),

    // Proration basis default ('calendar' or 'working_days')
    'default_proration_basis' => env('PAYROLL_DEFAULT_PRORATION_BASIS', 'calendar'),

];
