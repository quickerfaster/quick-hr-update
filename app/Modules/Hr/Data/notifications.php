<?php

/**
 * HR Module — Notification Templates
 *
 * Auto-discovered by NotificationDiscoveryService.
 * Convention: app/Modules/{Module}/Data/notifications.php
 *
 * @see \QuickerFaster\UILibrary\Services\Notifications\NotificationDiscoveryService
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Notification Templates
    |--------------------------------------------------------------------------
    |
    | Each template requires: code, channel, subject, body.
    | Optional: locale (defaults to 'en').
    |
    */
    'templates' => [

        // ─── Invitation Accepted ──────────────────────────────────
        [
            'code'    => 'invitation_accepted',
            'channel' => 'database',
            'subject' => '{email} accepted their invitation',
            'body'    => '{email} has accepted the invitation sent on {sent_date} and created their account.',
            'locale'  => 'en',
        ],

        // ─── Invitation Expired ───────────────────────────────────
        [
            'code'    => 'invitation_expired',
            'channel' => 'database',
            'subject' => 'Invitation to {email} has expired',
            'body'    => 'The invitation sent to {email} on {sent_date} has expired without being accepted.',
            'locale'  => 'en',
        ],

        // ─── Invitation Revoked ───────────────────────────────────
        [
            'code'    => 'invitation_revoked',
            'channel' => 'database',
            'subject' => 'Invitation to {email} has been revoked',
            'body'    => 'The invitation sent to {email} on {sent_date} has been revoked.',
            'locale'  => 'en',
        ],

    ],

];
