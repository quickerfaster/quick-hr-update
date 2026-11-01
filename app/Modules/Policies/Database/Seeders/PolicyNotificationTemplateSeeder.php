<?php

namespace App\Modules\Policies\Database\Seeders;

use Illuminate\Database\Seeder;
use QuickerFaster\UILibrary\Models\NotificationTemplate;

/**
 * Seeds notification templates for company policy events.
 *
 * Database channel uses plain text (rendered with {{ }} which escapes HTML).
 * Mail channel uses plain text with raw URLs.
 */
class PolicyNotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // ─── Policy Published ──────────────────────────────────
            [
                'type'          => 'policy_published',
                'channel'       => 'database',
                'subject'       => 'New Company Policy: {policy_title}',
                'body_template' => 'A new company policy "{policy_title}" (v{policy_version}) has been published. Please review and acknowledge it by {deadline}.',
                'locale'        => 'en',
            ],
            [
                'type'          => 'policy_published',
                'channel'       => 'mail',
                'subject'       => 'New Company Policy Requires Your Review: {policy_title}',
                'body_template' => "Dear {employee_name},\n\nA new company policy \"{policy_title}\" (v{policy_version}) has been published and requires your acknowledgment.\n\nCategory: {policy_category}\nEffective Date: {effective_date}\nAcknowledgment Deadline: {deadline}\n\nPlease log in to review and acknowledge this policy:\n{policy_url}\n\nThank you.",
                'locale'        => 'en',
            ],

            // ─── Policy Updated (Re-acknowledge Required) ──────────
            [
                'type'          => 'policy_updated',
                'channel'       => 'database',
                'subject'       => 'Policy Updated: {policy_title} v{policy_version}',
                'body_template' => 'The company policy "{policy_title}" has been updated to version {policy_version}. Please review the changes and re-acknowledge by {deadline}.',
                'locale'        => 'en',
            ],
            [
                'type'          => 'policy_updated',
                'channel'       => 'mail',
                'subject'       => 'Policy Updated — Re-acknowledgment Required: {policy_title}',
                'body_template' => "Dear {employee_name},\n\nThe company policy \"{policy_title}\" has been updated to version {policy_version}.\n\nCategory: {policy_category}\nEffective Date: {effective_date}\nAcknowledgment Deadline: {deadline}\n\nPlease log in to review the updated policy and re-acknowledge it:\n{policy_url}\n\nThank you.",
                'locale'        => 'en',
            ],

            // ─── Acknowledgment Reminder (Before Deadline) ─────────
            [
                'type'          => 'policy_acknowledgment_reminder',
                'channel'       => 'database',
                'subject'       => 'Reminder: Acknowledge {policy_title}',
                'body_template' => 'Reminder: Please review and acknowledge the company policy "{policy_title}". Deadline: {deadline}.',
                'locale'        => 'en',
            ],
            [
                'type'          => 'policy_acknowledgment_reminder',
                'channel'       => 'mail',
                'subject'       => 'Reminder: Policy Acknowledgment Due — {policy_title}',
                'body_template' => "Dear {employee_name},\n\nThis is a reminder that you have not yet acknowledged the company policy \"{policy_title}\" (v{policy_version}).\n\nCategory: {policy_category}\nDeadline: {deadline}\n\nPlease log in to review and acknowledge this policy:\n{policy_url}\n\nThank you.",
                'locale'        => 'en',
            ],

            // ─── Acknowledgment Overdue (Past Deadline) ────────────
            [
                'type'          => 'policy_acknowledgment_overdue',
                'channel'       => 'database',
                'subject'       => 'Overdue: {policy_title} Acknowledgment',
                'body_template' => 'The deadline to acknowledge "{policy_title}" has passed. Please review and acknowledge this policy as soon as possible.',
                'locale'        => 'en',
            ],
            [
                'type'          => 'policy_acknowledgment_overdue',
                'channel'       => 'mail',
                'subject'       => 'Overdue: Policy Acknowledgment — {policy_title}',
                'body_template' => "Dear {employee_name},\n\nThe deadline to acknowledge the company policy \"{policy_title}\" (v{policy_version}) has passed.\n\nCategory: {policy_category}\nDeadline was: {deadline}\n\nPlease log in to review and acknowledge this policy as soon as possible:\n{policy_url}\n\nThank you.",
                'locale'        => 'en',
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::updateOrCreate(
                [
                    'type'   => $template['type'],
                    'channel' => $template['channel'],
                    'locale'  => $template['locale'],
                ],
                [
                    'subject'       => $template['subject'],
                    'body_template' => $template['body_template'],
                ]
            );
        }
    }
}
