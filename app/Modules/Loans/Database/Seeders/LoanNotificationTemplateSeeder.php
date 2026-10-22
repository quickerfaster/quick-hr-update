<?php

namespace App\Modules\Loans\Database\Seeders;

use Illuminate\Database\Seeder;
use QuickerFaster\UILibrary\Models\NotificationTemplate;

/**
 * Seeds notification templates for loan events.
 *
 * Database channel uses plain text (rendered with {{ }} which escapes HTML).
 * Mail channel uses plain text with raw URLs.
 */
class LoanNotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // ─── Loan Submitted ──────────────────────────────────
            [
                'type'          => 'loan_submitted',
                'channel'       => 'database',
                'subject'       => 'Loan Request Submitted: {loan_number}',
                'body_template' => 'Your loan request {loan_number} for {amount} has been submitted and is pending approval.',
                'locale'        => 'en',
            ],
            [
                'type'          => 'loan_submitted',
                'channel'       => 'mail',
                'subject'       => 'Loan Request Submitted: {loan_number}',
                'body_template' => "Dear {employee_name},\n\nYour loan request {loan_number} for {amount} has been submitted and is now pending approval.\n\nYou will be notified once a decision has been made.\n\nView your loan status: {url}\n\nThank you.",
                'locale'        => 'en',
            ],

            // ─── Loan Approved ───────────────────────────────────
            [
                'type'          => 'loan_approved',
                'channel'       => 'database',
                'subject'       => 'Loan Request Approved: {loan_number}',
                'body_template' => 'Your loan request {loan_number} for {amount} has been approved. Repayments will begin on {start_date}.',
                'locale'        => 'en',
            ],
            [
                'type'          => 'loan_approved',
                'channel'       => 'mail',
                'subject'       => 'Loan Request Approved: {loan_number}',
                'body_template' => "Dear {employee_name},\n\nYour loan request {loan_number} for {amount} has been approved.\n\nMonthly Installment: {installment_amount}\nRepayment Start Date: {start_date}\n\nView your loan details: {url}\n\nThank you.",
                'locale'        => 'en',
            ],

            // ─── Loan Rejected ───────────────────────────────────
            [
                'type'          => 'loan_rejected',
                'channel'       => 'database',
                'subject'       => 'Loan Request Rejected: {loan_number}',
                'body_template' => 'Your loan request {loan_number} for {amount} has been rejected. Please contact HR for more information.',
                'locale'        => 'en',
            ],
            [
                'type'          => 'loan_rejected',
                'channel'       => 'mail',
                'subject'       => 'Loan Request Rejected: {loan_number}',
                'body_template' => "Dear {employee_name},\n\nYour loan request {loan_number} for {amount} has been rejected.\n\nPlease contact your HR department for more information.\n\nView details: {url}\n\nThank you.",
                'locale'        => 'en',
            ],

            // ─── Repayment Due ───────────────────────────────────
            [
                'type'          => 'repayment_due',
                'channel'       => 'database',
                'subject'       => 'Loan Repayment Due: {loan_number}',
                'body_template' => 'Your loan repayment of {installment_amount} for {loan_number} is due on {due_date}.',
                'locale'        => 'en',
            ],
            [
                'type'          => 'repayment_due',
                'channel'       => 'mail',
                'subject'       => 'Loan Repayment Due: {loan_number}',
                'body_template' => "Dear {employee_name},\n\nThis is a reminder that your loan repayment is due.\n\nLoan: {loan_number}\nAmount Due: {installment_amount}\nDue Date: {due_date}\nRemaining Balance: {balance}\n\nView your loan: {url}\n\nThank you.",
                'locale'        => 'en',
            ],

            // ─── Repayment Overdue ───────────────────────────────
            [
                'type'          => 'repayment_overdue',
                'channel'       => 'database',
                'subject'       => 'Overdue Loan Repayment: {loan_number}',
                'body_template' => 'Your loan repayment of {installment_amount} for {loan_number} was due on {due_date} and is now overdue.',
                'locale'        => 'en',
            ],
            [
                'type'          => 'repayment_overdue',
                'channel'       => 'mail',
                'subject'       => 'Overdue Loan Repayment: {loan_number}',
                'body_template' => "Dear {employee_name},\n\nYour loan repayment is now overdue.\n\nLoan: {loan_number}\nAmount Due: {installment_amount}\nWas Due On: {due_date}\nRemaining Balance: {balance}\n\nPlease arrange payment as soon as possible.\n\nView your loan: {url}\n\nThank you.",
                'locale'        => 'en',
            ],

            // ─── Loan Completed ──────────────────────────────────
            [
                'type'          => 'loan_completed',
                'channel'       => 'database',
                'subject'       => 'Loan Fully Repaid: {loan_number}',
                'body_template' => 'Congratulations! Your loan {loan_number} has been fully repaid.',
                'locale'        => 'en',
            ],
            [
                'type'          => 'loan_completed',
                'channel'       => 'mail',
                'subject'       => 'Loan Fully Repaid: {loan_number}',
                'body_template' => "Dear {employee_name},\n\nCongratulations! Your loan {loan_number} has been fully repaid.\n\nTotal Amount Repaid: {amount}\n\nThank you for your timely payments.\n\nView details: {url}\n\nThank you.",
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

        // ─── Workflow Notification Templates ─────────────────────
        // The library seeds four workflow types (workflow_submitted,
        // workflow_approved, workflow_rejected, workflow_recalled).
        // The consuming app must seed the three initiator-feedback types.
        $this->seedWorkflowTemplates();
    }

    /**
     * Seed the three initiator-feedback workflow notification templates
     * that the library does not seed.
     */
    protected function seedWorkflowTemplates(): void
    {
        // Note: The WorkflowEngine passes {title} (loan number), {url} (admin
        // detail page), {workflow_key}, and {workflow_status}. It does NOT pass
        // {employee_name}. For ESS/employee-facing notifications, we hardcode
        // the My Loans URL instead of using the admin {url}.
        $workflowTemplates = [
            [
                'type'          => 'workflow_submitted_initiator',
                'channel'       => 'database',
                'subject'       => 'Loan Request Submitted: {title}',
                'body_template' => 'Your loan request {title} has been submitted for approval. You will be notified when it is reviewed.',
                'locale'        => 'en',
            ],
            [
                'type'          => 'workflow_submitted_initiator',
                'channel'       => 'mail',
                'subject'       => 'Loan Request Submitted: {title}',
                'body_template' => "Your loan request {title} has been submitted for approval.\n\nYou will be notified when a decision has been made.\n\nView your loans: " . url('/hr/my-loans') . "\n\nThank you.",
                'locale'        => 'en',
            ],
            [
                'type'          => 'workflow_stage_advanced',
                'channel'       => 'database',
                'subject'       => 'Loan Request Progress: {title}',
                'body_template' => 'Your loan request {title} has advanced to the next approval stage.',
                'locale'        => 'en',
            ],
            [
                'type'          => 'workflow_stage_advanced',
                'channel'       => 'mail',
                'subject'       => 'Loan Request Update: {title}',
                'body_template' => "Your loan request {title} has advanced to the next stage of the approval process.\n\nView your loans: " . url('/hr/my-loans') . "\n\nThank you.",
                'locale'        => 'en',
            ],
            [
                'type'          => 'workflow_completed',
                'channel'       => 'database',
                'subject'       => 'Loan Request Decision: {title}',
                'body_template' => 'The approval process for your loan request {title} has been completed. Status: {workflow_status}.',
                'locale'        => 'en',
            ],
            [
                'type'          => 'workflow_completed',
                'channel'       => 'mail',
                'subject'       => 'Loan Request Decision: {title}',
                'body_template' => "The approval process for your loan request {title} has been completed.\n\nStatus: {workflow_status}\n\nView your loans: " . url('/hr/my-loans') . "\n\nThank you.",
                'locale'        => 'en',
            ],
        ];

        foreach ($workflowTemplates as $template) {
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
