<?php

/**
 * Payroll Module — Permission Configuration
 *
 * Declares domain-specific permissions beyond the auto-generated
 * model-based CRUD permissions.
 *
 * @see \QuickerFaster\UILibrary\Services\AccessControl\AccessControlPermissionService
 */
return [

    'extra' => [
        'process_payroll_run',
        'approve_payroll_run',
        'export_payslip',
    ],

];
