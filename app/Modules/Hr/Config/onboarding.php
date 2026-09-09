<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Employee Onboarding Configuration
    |--------------------------------------------------------------------------
    |
    | Post-acceptance onboarding steps for newly invited employees.
    | Each step defines a condition class (implementing OnboardingCondition),
    | a route for the step's form view, and display metadata.
    |
    | Steps are executed in ascending 'order'. The first incomplete step
    | is presented to the user after they accept their invitation.
    |
    */
    'employee_onboarding' => [
        'steps' => [
            [
                'key' => 'employee_profile',
                'label' => 'Employee Profile',
                'condition' => \App\Modules\Hr\Conditions\EmployeeProfileCreated::class,
                'route' => 'hr.onboarding.employee-profile',
                'order' => 1,
            ],
            [
                'key' => 'personal_details',
                'label' => 'Personal Details',
                'condition' => \App\Modules\Hr\Conditions\PersonalDetailsComplete::class,
                'route' => 'hr.onboarding.personal-details',
                'order' => 2,
            ],
            [
                'key' => 'emergency_contact',
                'label' => 'Emergency Contact',
                'condition' => \App\Modules\Hr\Conditions\EmergencyContactAdded::class,
                'route' => 'hr.onboarding.emergency-contact',
                'order' => 3,
            ],
            [
                'key' => 'bank_details',
                'label' => 'Bank Details',
                'condition' => \App\Modules\Hr\Conditions\BankDetailsAdded::class,
                'route' => 'hr.onboarding.bank-details',
                'order' => 4,
            ],
            [
                'key' => 'documents',
                'label' => 'Documents',
                'condition' => \App\Modules\Hr\Conditions\DocumentsUploaded::class,
                'route' => 'hr.onboarding.documents',
                'order' => 5,
            ],
            [
                'key' => 'notification_preferences',
                'label' => 'Notification Preferences',
                'condition' => \App\Modules\Hr\Conditions\NotificationPreferencesSet::class,
                'route' => 'hr.onboarding.notification-preferences',
                'order' => 6,
            ],
        ],
    ],
];
