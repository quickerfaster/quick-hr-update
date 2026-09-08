<?php

namespace App\Modules\Hr\Database\Seeders;

use Illuminate\Database\Seeder;
use QuickerFaster\UILibrary\Models\NotificationTemplate;

/**
 * InvitationNotificationTemplateSeeder — seeds notification templates for
 * invitation lifecycle events (accepted, expired, revoked).
 *
 * Templates follow the same pattern as the library's
 * NotificationTemplateSeeder: firstOrCreate by type + channel + locale.
 */
class InvitationNotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // ─── Invitation Accepted ──────────────────────────────
            [
                'type'          => 'invitation_accepted',
                'channel'       => 'database',
                'subject'       => '{email} accepted their invitation',
                'body_template' => '{email} has accepted the invitation sent on {sent_date} and created their account.',
                'locale'        => 'en',
            ],

            // ─── Invitation Expired ───────────────────────────────
            [
                'type'          => 'invitation_expired',
                'channel'       => 'database',
                'subject'       => 'Invitation to {email} has expired',
                'body_template' => 'The invitation sent to {email} on {sent_date} has expired without being accepted.',
                'locale'        => 'en',
            ],

            // ─── Invitation Revoked ───────────────────────────────
            [
                'type'          => 'invitation_revoked',
                'channel'       => 'database',
                'subject'       => 'Invitation to {email} has been revoked',
                'body_template' => 'The invitation sent to {email} on {sent_date} has been revoked.',
                'locale'        => 'en',
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::firstOrCreate(
                [
                    'type'    => $template['type'],
                    'channel' => $template['channel'],
                    'locale'  => $template['locale'],
                ],
                $template
            );
        }
    }
}
