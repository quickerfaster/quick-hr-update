<?php

/**
 * Attendance Module — Permission Configuration
 *
 * Declares domain-specific permissions beyond the auto-generated
 * model-based CRUD permissions.
 *
 * @see \QuickerFaster\UILibrary\Services\AccessControl\AccessControlPermissionService
 */
return [

    'extra' => [
        'clock_in',
        'clock_out',
        'recalculate_attendance',
    ],

];
