<?php

namespace App\Modules\Hr\Database\Seeders;

use Illuminate\Database\Seeder;
use QuickerFaster\UILibrary\Models\NotificationTemplate;

/**
 * EssNotificationTemplateSeeder — seeds notification templates for
 * Employee Self-Service events.
 *
 * Templates follow the same pattern as the library's
 * NotificationTemplateSeeder: firstOrCreate by type + channel + locale.
 */
class EssNotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // ─── Payslip Ready ───────────────────────────────────
            [
                'type'          => 'payslip_ready',
                'channel'       => 'database',
                'subject'       => 'Payslip Ready',
                'body_template' => 'Your payslip for {period} is ready for download.',
                'locale'        => 'en',
            ],
            [
                'type'          => 'payslip_ready',
                'channel'       => 'mail',
                'subject'       => 'Your Payslip for {period} is Ready',
                'body_template' => "Dear {employee_name},\n\nYour payslip for {period} is now available. You can view and download it from the My Payslips section of your portal.\n\nView Payslip: {payslip_url}\n\nThank you.",
                'locale'        => 'en',
            ],

            // ─── Leave Request Approved ──────────────────────────
            [
                'type'          => 'leave_approved',
                'channel'       => 'database',
                'subject'       => 'Leave Approved',
                'body_template' => 'Your leave request ({start_date} to {end_date}) was approved by {approver_name}.',
                'locale'        => 'en',
            ],
            [
                'type'          => 'leave_approved',
                'channel'       => 'mail',
                'subject'       => 'Leave Request Approved',
                'body_template' => "Dear {employee_name},\n\nYour leave request for {leave_type} from {start_date} to {end_date} has been approved by {approver_name}.\n\nDuration: {days} day(s)\n\nThank you.",
                'locale'        => 'en',
            ],

            // ─── Leave Request Denied ────────────────────────────
            [
                'type'          => 'leave_denied',
                'channel'       => 'database',
                'subject'       => 'Leave Denied',
                'body_template' => 'Your leave request ({start_date} to {end_date}) was denied by {approver_name}. Reason: {reason}',
                'locale'        => 'en',
            ],
            [
                'type'          => 'leave_denied',
                'channel'       => 'mail',
                'subject'       => 'Leave Request Denied',
                'body_template' => "Dear {employee_name},\n\nYour leave request for {leave_type} from {start_date} to {end_date} has been denied by {approver_name}.\n\nReason: {reason}\n\nPlease contact your manager if you have questions.",
                'locale'        => 'en',
            ],

            // ─── Pending Approval Reminder ───────────────────────
            [
                'type'          => 'leave_submitted',
                'channel'       => 'database',
                'subject'       => 'Leave Request Submitted',
                'body_template' => 'Your leave request ({start_date} to {end_date}) is pending approval from {approver_name}.',
                'locale'        => 'en',
            ],
            [
                'type'          => 'leave_submitted',
                'channel'       => 'mail',
                'subject'       => 'Leave Request Submitted for Approval',
                'body_template' => "Dear {employee_name},\n\nYour leave request for {leave_type} from {start_date} to {end_date} has been submitted and is pending approval from {approver_name}.\n\nYou will be notified once a decision is made.",
                'locale'        => 'en',
            ],

            // ─── Upcoming Holiday ────────────────────────────────
            [
                'type'          => 'upcoming_holiday',
                'channel'       => 'database',
                'subject'       => 'Upcoming Holiday',
                'body_template' => 'Upcoming holiday: {holiday_name} on {holiday_date}.',
                'locale'        => 'en',
            ],
            [
                'type'          => 'upcoming_holiday',
                'channel'       => 'mail',
                'subject'       => 'Upcoming Holiday: {holiday_name}',
                'body_template' => "Dear {employee_name},\n\nThis is a reminder that {holiday_name} is coming up on {holiday_date}.\n\nEnjoy the holiday!",
                'locale'        => 'en',
            ],

            // ─── Clock-Out Reminder ──────────────────────────────
            [
                'type'          => 'clock_out_reminder',
                'channel'       => 'database',
                'subject'       => 'Clock-Out Reminder',
                'body_template' => 'Reminder: You are still clocked in since {clock_in_time}. Please clock out when you finish.',
                'locale'        => 'en',
            ],
            [
                'type'          => 'clock_out_reminder',
                'channel'       => 'mail',
                'subject'       => 'Clock-Out Reminder',
                'body_template' => "Dear {employee_name},\n\nThis is a reminder that you are still clocked in since {clock_in_time}. Please remember to clock out when you finish your work.\n\nClock Out: {clock_out_url}",
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