<?php

/**
 * Policies Module — Notification Templates
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

        // ─── Policy Published ──────────────────────────────────────
        [
            'code'    => 'policy_published',
            'channel' => 'database',
            'subject' => 'New Company Policy: {policy_title}',
            'body'    => 'A new company policy "{policy_title}" (v{policy_version}) has been published. Please review and acknowledge it.',
            'locale'  => 'en',
        ],

        // ─── Policy Updated ────────────────────────────────────────
        [
            'code'    => 'policy_updated',
            'channel' => 'database',
            'subject' => 'Policy Updated: {policy_title} v{policy_version}',
            'body'    => 'The company policy "{policy_title}" has been updated to version {policy_version}. Please review the changes.',
            'locale'  => 'en',
        ],

        // ─── Acknowledgment Reminder ───────────────────────────────
        [
            'code'    => 'policy_acknowledgment_reminder',
            'channel' => 'database',
            'subject' => 'Reminder: Acknowledge {policy_title}',
            'body'    => 'This is a reminder to review and acknowledge the company policy "{policy_title}". The deadline is {deadline}.',
            'locale'  => 'en',
        ],

        // ─── Acknowledgment Overdue ────────────────────────────────
        [
            'code'    => 'policy_acknowledgment_overdue',
            'channel' => 'database',
            'subject' => 'Overdue: {policy_title} Acknowledgment',
            'body'    => 'The deadline to acknowledge "{policy_title}" has passed. Please review and acknowledge this policy as soon as possible.',
            'locale'  => 'en',
        ],

    ],

];
