<?php

/**
 * Loans Module — Notification Templates
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

        // ─── Loan Submitted ──────────────────────────────────────
        [
            'code'    => 'loan_submitted',
            'channel' => 'database',
            'subject' => 'Loan Request Submitted: {loan_number}',
            'body'    => 'Your loan request {loan_number} for {amount} has been submitted and is pending approval.',
            'locale'  => 'en',
        ],

        // ─── Loan Approved ───────────────────────────────────────
        [
            'code'    => 'loan_approved',
            'channel' => 'database',
            'subject' => 'Loan Request Approved: {loan_number}',
            'body'    => 'Your loan request {loan_number} for {amount} has been approved. Repayments will begin on {start_date}.',
            'locale'  => 'en',
        ],

        // ─── Loan Rejected ───────────────────────────────────────
        [
            'code'    => 'loan_rejected',
            'channel' => 'database',
            'subject' => 'Loan Request Rejected: {loan_number}',
            'body'    => 'Your loan request {loan_number} for {amount} has been rejected.',
            'locale'  => 'en',
        ],

        // ─── Repayment Due ───────────────────────────────────────
        [
            'code'    => 'repayment_due',
            'channel' => 'database',
            'subject' => 'Loan Repayment Due: {loan_number}',
            'body'    => 'Your loan repayment of {installment_amount} for {loan_number} is due on {due_date}.',
            'locale'  => 'en',
        ],

        // ─── Repayment Overdue ───────────────────────────────────
        [
            'code'    => 'repayment_overdue',
            'channel' => 'database',
            'subject' => 'Overdue Loan Repayment: {loan_number}',
            'body'    => 'Your loan repayment of {installment_amount} for {loan_number} was due on {due_date} and is now overdue.',
            'locale'  => 'en',
        ],

        // ─── Loan Completed ──────────────────────────────────────
        [
            'code'    => 'loan_completed',
            'channel' => 'database',
            'subject' => 'Loan Fully Repaid: {loan_number}',
            'body'    => 'Your loan {loan_number} has been fully repaid. Thank you.',
            'locale'  => 'en',
        ],

    ],

];
