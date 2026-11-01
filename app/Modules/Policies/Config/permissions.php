<?php

/**
 * Policies Module — Permission Configuration
 *
 * Declares domain-specific permissions beyond the auto-generated
 * model-based CRUD permissions.
 *
 * @see \QuickerFaster\UILibrary\Services\AccessControl\AccessControlPermissionService
 */
return [

    'extra' => [
        'manage_policy_distribution',
        'view_policy_compliance_report',
    ],

];
