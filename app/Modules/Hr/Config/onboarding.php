<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Employee Onboarding Configuration
    |--------------------------------------------------------------------------
    |
    | Consolidated single-page wizard configuration for post-acceptance
    | employee onboarding. Replaces the old 6 separate page steps with
    | 5 wizard steps rendered inside a single Livewire component at /onboarding.
    |
    | Each step maps to one primary model (or logical group) and may be
    | required or optional. The Payroll step is conditionally excluded
    | when the Payroll module is not installed.
    |
    */
    'employee_onboarding' => [
        'wizard_route' => '/onboarding',
        'steps' => [
            [
                'key'       => 'employee_record',
                'label'     => 'Employee Record',
                'description' => 'Set up your employee record',
                'model'     => \App\Modules\Hr\Models\Employee::class,
                'condition' => \App\Modules\Hr\Conditions\EmployeeProfileCreated::class,
                'component' => 'qf.onboarding.step1-employee-record',
                'required'  => true,
                'order'     => 1,
            ],
            [
                'key'       => 'employee_profile',
                'label'     => 'Employee Profile',
                'description' => 'Add personal details and emergency contacts',
                'model'     => \App\Modules\Hr\Models\EmployeeProfile::class,
                'condition' => \App\Modules\Hr\Conditions\EmployeeProfileComplete::class,
                'component' => 'qf.onboarding.step2-employee-profile',
                'required'  => false,
                'order'     => 2,
            ],
            [
                'key'       => 'payroll_banking',
                'label'     => 'Payroll & Banking',
                'description' => 'Set up bank details for salary payments',
                'model'     => \App\Modules\Payroll\Models\EmployeePayrollProfile::class,
                'condition' => \App\Modules\Hr\Conditions\BankDetailsAdded::class,
                'component' => 'qf.onboarding.step3-payroll-banking',
                'required'  => false,
                'order'     => 3,
            ],
            [
                'key'       => 'documents',
                'label'     => 'Documents',
                'description' => 'Upload ID, certificates, and other documents',
                'model'     => \App\Modules\Hr\Models\Document::class,
                'condition' => \App\Modules\Hr\Conditions\DocumentsUploaded::class,
                'component' => 'qf.onboarding.step4-documents',
                'required'  => false,
                'order'     => 4,
            ],
            [
                'key'       => 'preferences',
                'label'     => 'Notification Preferences',
                'description' => 'Choose how you want to be notified',
                'model'     => null,  // User settings, not a model
                'condition' => \App\Modules\Hr\Conditions\NotificationPreferencesSet::class,
                'component' => 'qf.onboarding.step5-preferences',
                'required'  => false,
                'order'     => 5,
            ],
        ],
    ],
];
