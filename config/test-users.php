<?php

/**
 * Test user credentials for seeding.
 * 
 * These emails are used by the mobile app for API communication.
 * Changing them requires corresponding mobile app updates.
 */
return [
    'users' => [
        [
            'name' => 'admin',
            'email' => 'admin@softui.com',
            'password' => 'secret',
            'role' => 'employee',
        ],
        [
            'name' => 'super admin',
            'email' => 'superadmin@quickerfaster.com',
            'password' => 'QuickHR@12345',
            'role' => 'super_admin',
        ],
        [
            'name' => 'company admin',
            'email' => 'gmadmin@agriwatts.ng',
            'password' => 'Test@12345',
            'role' => 'company_admin',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Test User
    |--------------------------------------------------------------------------
    */
    'default' => [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ],

    /*
    |--------------------------------------------------------------------------
    | ESS Test User
    |--------------------------------------------------------------------------
    */
    'ess' => [
        'name' => 'ESS Test User',
        'email' => 'ess.test@example.com',
        'password' => 'password',
        'role' => 'employee',
    ],
];