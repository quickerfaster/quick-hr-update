<?php

namespace App\Modules\Leave\Database\Seeders;

use Illuminate\Database\Seeder;
use QuickerFaster\UILibrary\Models\NotificationTemplate;

class LeaveWorkflowNotificationTemplateSeeder extends Seeder
{
    /**
     * Seed the notification templates required by the Leave Request workflow.
     *
     * These templates are consumed by WorkflowEngine::notifyTransition()
     * when the leave_request workflow definition's "notifications" section
     * is enabled. The engine resolves the template type from the config
     * mapping (e.g. 'submitted' → 'workflow_submitted') and passes it to
     * NotificationService::dispatch(), which looks up the template by
     * {type, channel, locale}.
     *
     * Idempotent — uses firstOrCreate so repeated runs are safe.
     */
    public function run(): void
    {
        $templates = [
            // --- Workflow Submitted ---
            [
                'type'          => 'workflow_submitted',
                'channel'       => 'database',
                'subject'       => '"{title}" Submitted for Approval',
                'body_template' => '"{title}" has been submitted and is awaiting your review. View details: {url}',
                'locale'        => 'en',
            ],
            [
                'type'          => 'workflow_submitted',
                'channel'       => 'mail',
                'subject'       => '"{title}" Submitted for Approval',
                'body_template' => '"{title}" has been submitted and is awaiting your review. View details: {url}',
                'locale'        => 'en',
            ],

            // --- Workflow Approved ---
            [
                'type'          => 'workflow_approved',
                'channel'       => 'database',
                'subject'       => '"{title}" Step Approved',
                'body_template' => 'Step "{step_name}" for "{title}" has been approved. View details: {url}',
                'locale'        => 'en',
            ],
            [
                'type'          => 'workflow_approved',
                'channel'       => 'mail',
                'subject'       => '"{title}" Step Approved',
                'body_template' => 'Step "{step_name}" for "{title}" has been approved. View details: {url}',
                'locale'        => 'en',
            ],

            // --- Workflow Rejected ---
            [
                'type'          => 'workflow_rejected',
                'channel'       => 'database',
                'subject'       => '"{title}" Rejected',
                'body_template' => '"{title}" was rejected at step "{step_name}". View details: {url}',
                'locale'        => 'en',
            ],
            [
                'type'          => 'workflow_rejected',
                'channel'       => 'mail',
                'subject'       => '"{title}" Rejected',
                'body_template' => '"{title}" was rejected at step "{step_name}". View details: {url}',
                'locale'        => 'en',
            ],

            // --- Workflow Recalled ---
            [
                'type'          => 'workflow_recalled',
                'channel'       => 'database',
                'subject'       => '"{title}" Recalled',
                'body_template' => '"{title}" has been recalled by the submitter. View details: {url}',
                'locale'        => 'en',
            ],
            [
                'type'          => 'workflow_recalled',
                'channel'       => 'mail',
                'subject'       => '"{title}" Recalled',
                'body_template' => '"{title}" has been recalled by the submitter. View details: {url}',
                'locale'        => 'en',
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::updateOrCreate(
                [
                    'type'    => $template['type'],
                    'channel' => $template['channel'],
                    'locale'  => $template['locale'],
                ],
                $template,
            );
        }
    }
}
