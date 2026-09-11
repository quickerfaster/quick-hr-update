<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Annual Leave Allowances
    |--------------------------------------------------------------------------
    |
    | Default annual leave allowance in days. Used when a leave type has
    | accrual_frequency set to "None" — the full balance is granted upfront
    | instead of accruing monthly.
    |
    | Override per leave type by adding the leave type's 'code' as a key.
    |
    */
    'annual_allowances' => [
        'default' => 20,

        // Override per leave type code:
        // 'annual' => 20,
        // 'sick' => 10,
        // 'maternity' => 90,
    ],
];
