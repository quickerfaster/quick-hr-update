<?php

/**
 * Absence Report Wizard — Employee Self-Service
 *
 * Dedicated wizard for employees to quickly report an unplanned absence.
 * Unlike the generic sick_call_report wizard, this does NOT depend on
 * the attendance.php data config groups — all fields are self-contained.
 *
 * Modeled after leave.wizards.employee_self_service for consistency.
 */

return [
    'id'          => 'absence_report',
    'title'       => 'Report Absence',
    'description' => 'Quickly notify your manager of an unplanned absence',
    'returnPath'  => '/hr/leave-hub?tab=overview',

    'steps' => [
        0 => [
            'title' => 'Reason & Duration',
            'customComponent' => 'attendance.absence-report-form',
            'fields' => [
                0 => [
                    'absence_type' => [
                        'type'    => 'select',
                        'label'   => 'Absence Type',
                        'options' => [
                            'sick_leave'          => 'Sick Leave',
                            'emergency'           => 'Family Emergency',
                            'personal'            => 'Personal Matter',
                            'official_assignment' => 'Official Assignment / Company Duty',
                            'other'               => 'Other',
                        ],
                        'default'    => 'sick_leave',
                        'validation' => ['required'],
                    ],
                ],
                1 => [
                    'start_date' => [
                        'type'       => 'datepicker',
                        'label'      => 'Start Date',
                        'default'    => 'today',
                        'validation' => ['required', 'date', 'before_or_equal:today'],
                    ],
                ],
                2 => [
                    'expected_duration' => [
                        'type'    => 'select',
                        'label'   => 'Expected Duration',
                        'options' => [
                            1  => '1 day',
                            3  => '2–3 days',
                            5  => '4–5 days (about a week)',
                            14 => '1–2 weeks',
                            28 => '3–4 weeks (about a month)',
                            60 => '1–2 months',
                            0  => 'Uncertain',
                        ],
                        'default' => 1,
                    ],
                ],
                3 => [
                    'absence_reason' => [
                        'type'        => 'textarea',
                        'label'       => 'Reason',
                        'placeholder' => 'Brief explanation for your absence...',
                        'validation'  => ['required', 'string', 'min:10', 'max:500'],
                    ],
                ],
            ],
        ],
    ],

    'completion' => [
        'title'   => 'Absence Reported Successfully',
        'message' => 'Your manager has been notified. If you selected "Create draft leave request", you can complete it from My Leaves.',
        'actions' => [
            0 => [
                'label'   => 'View My Attendance',
                'url'     => '/hr/my-profile?tab=attendance',
                'primary' => true,
            ],
            1 => [
                'label' => 'Complete Leave Request',
                'url'   => '/hr/leave-hub?tab=my-leaves',
            ],
            2 => [
                'label' => 'Back to Leave Hub',
                'url'   => '/hr/leave-hub?tab=overview',
            ],
        ],
    ],

    'linkFields' => [
        'userField'     => 'employee_number',
        'databaseField' => 'employee_id',
    ],

    'roles' => ['employee', 'manager'],

    'models' => [
        'primary' => 'App\\Modules\\Attendance\\Models\\Attendance',
        'related' => [],
    ],
];
