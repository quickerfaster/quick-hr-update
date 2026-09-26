<?php

/**
 * Leave Module — Permission Configuration
 *
 * Declares domain-specific permissions beyond the auto-generated
 * model-based CRUD permissions (view_leave_request, create_leave_type, etc.).
 *
 * The 'extra' key is read by AccessControlPermissionService::seedPermissionNames()
 * and these permissions appear in the AccessControlManager UI for role assignment.
 *
 * @see \QuickerFaster\UILibrary\Services\AccessControl\AccessControlPermissionService
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Extra Permissions
    |--------------------------------------------------------------------------
    |
    | Permission names listed here are seeded in addition to the standard
    | view_*, create_*, edit_*, delete_*, print_*, export_*, import_* CRUD
    | permissions auto-generated from discovered models.
    |
    */
    'extra' => [
        'approve_leave_request',
        'cancel_leave_request',
    ],

];
