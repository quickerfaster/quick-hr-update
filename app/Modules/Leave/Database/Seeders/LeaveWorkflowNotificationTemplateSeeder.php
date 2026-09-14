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
                'subject'       => 'Awaiting Review: "{title}"',
                'body_template' => '"{title}" is awaiting your review.',
                'locale'        => 'en',
            ],
            [
                'type'          => 'workflow_approved',
                'channel'       => 'mail',
                'subject'       => '"{title}" Awaiting Your Review',
                'body_template' => '"{title}" is awaiting your review. View details: {url}',
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

            // --- Stage Advanced ---
            [
                'type'          => 'workflow_stage_advanced',
                'channel'       => 'mail',
                'subject'       => 'Your Request "{title}" Has Been Updated',
                'body_template' => 'Your request "{title}" was approved by {approver_name}. It is now with {next_step_name} for review. View details: {url}',
                'locale'        => 'en',
            ],
            [
                'type'          => 'workflow_stage_advanced',
                'channel'       => 'database',
                'subject'       => 'Request Updated: "{title}"',
                'body_template' => 'Your request "{title}" was approved by {approver_name}. Now with {next_step_name}.',
                'locale'        => 'en',
            ],

            // --- Submitted Initiator ---
            [
                'type'          => 'workflow_submitted_initiator',
                'channel'       => 'mail',
                'subject'       => 'Your Request "{title}" Has Been Submitted',
                'body_template' => 'Your request "{title}" has been submitted for approval. You will be notified when it is reviewed. View details: {url}',
                'locale'        => 'en',
            ],
            [
                'type'          => 'workflow_submitted_initiator',
                'channel'       => 'database',
                'subject'       => 'Request Submitted: "{title}"',
                'body_template' => 'Your request "{title}" has been submitted for approval.',
                'locale'        => 'en',
            ],

            // --- Workflow Completed ---
            [
                'type'          => 'workflow_completed',
                'channel'       => 'mail',
                'subject'       => 'Your Request "{title}" Has Been Fully Approved',
                'body_template' => 'Great news! Your request "{title}" has been fully approved by all reviewers. View details: {url}',
                'locale'        => 'en',
            ],
            [
                'type'          => 'workflow_completed',
                'channel'       => 'database',
                'subject'       => 'Fully Approved: "{title}"',
                'body_template' => 'Your request "{title}" has been fully approved.',
                'locale'        => 'en',
            ],

            // --- Workflow Cancelled ---
            [
                'type'          => 'workflow_cancelled',
                'channel'       => 'mail',
                'subject'       => 'Your Request "{title}" Has Been Cancelled',
                'body_template' => 'Your approved request "{title}" has been cancelled. The leave days have been restored to your balance. View details: {url}',
                'locale'        => 'en',
            ],
            [
                'type'          => 'workflow_cancelled',
                'channel'       => 'database',
                'subject'       => 'Cancelled: "{title}"',
                'body_template' => 'Your approved request "{title}" has been cancelled. Leave days restored.',
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
