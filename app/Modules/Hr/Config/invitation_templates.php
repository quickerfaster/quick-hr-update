<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Invitation Email Templates by Department
    |--------------------------------------------------------------------------
    |
    | Customize invitation email subject lines and greetings per department.
    | The department key should match the department name (lowercase, snake_case).
    | If no department-specific template exists, the 'default' template is used.
    |
    | Available placeholders:
    |   {company_name} - The application name from config('app.name')
    |   {employee_name} - The employee's full name
    |   {department}    - The department name
    |   {role}          - The assigned role name
    |
    */
    'default' => [
        'subject' => "You've been invited to join {company_name}",
        'greeting' => 'Hello,',
    ],

    'engineering' => [
        'subject' => "Welcome to the Engineering Team at {company_name}",
        'greeting' => 'Hello future engineer,',
    ],

    'marketing' => [
        'subject' => "Join Our Marketing Team at {company_name}",
        'greeting' => 'Hello creative mind,',
    ],

    'sales' => [
        'subject' => "Join Our Sales Team at {company_name}",
        'greeting' => 'Hello,',
    ],

    'human_resources' => [
        'subject' => "Welcome to the People Team at {company_name}",
        'greeting' => 'Hello,',
    ],

    'finance' => [
        'subject' => "Join Our Finance Team at {company_name}",
        'greeting' => 'Hello,',
    ],

    'product' => [
        'subject' => "Welcome to the Product Team at {company_name}",
        'greeting' => 'Hello,',
    ],

    'design' => [
        'subject' => "Join Our Design Team at {company_name}",
        'greeting' => 'Hello creative,',
    ],
];
