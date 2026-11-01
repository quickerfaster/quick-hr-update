# Company Policies Module

> **Module**: `app/Modules/Policies/`
> **Status**: Complete (Phases 1-5 implemented)
> **Last Updated**: 2026-10-14

## Overview

The Policies module manages company policy documents (handbook, code of conduct, leave policy, etc.) with employee acknowledgment tracking. It integrates with the HR module's navigation and the onboarding wizard.

## Architecture

Business logic lives in `app/Modules/Policies/` (self-contained, portable). Navigation is integrated into the HR module as a `policies` context group (admin) and a `my-portal` context item (ESS).

## Features

### Admin (HR Managers)
- **CRUD**: Full-page create/edit/show (not drawers — `crudType: pages`) at `/hr/company-policies`
- **Richtext editor**: Trix WYSIWYG editor for policy content with fullscreen overlay
- **Publish/Archive**: Row actions with status validation
- **Soft delete**: Trash view with restore/force delete
- **Compliance dashboard**: Metric cards (Total, Published, Requiring Ack, Drafts) + Recent Policies list at `/hr/dashboard-policies-overview`
- **Acknowledgment report**: Read-only DataTable at `/hr/policy-acknowledgments`

### ESS (Employees)
- **Policy hub**: Card-based layout with Pending/Acknowledged sections at My Portal → Company Policies
- **Acknowledgment modal**: Read policy content → type name → acknowledge with audit trail (IP, timestamp, user agent)
- **Print/Download**: Opens clean printable page in new tab
- **Already-acknowledged detection**: Shows "Already Acknowledged" instead of signature field

### Onboarding (New Hires)
- **Step 6**: Company Policies step in the onboarding wizard
- Lists mandatory policies requiring acknowledgment
- "Save & Finish" disabled until all policies acknowledged

### Notifications
- **Policy Published**: Sent to all affected employees when a policy is published
- **Policy Updated**: Sent when published policy content changes (re-acknowledgment)
- **Acknowledgment Reminder**: Daily at 8 AM for policies approaching deadline
- **Acknowledgment Overdue**: Daily at 8 AM for past-deadline policies
- **Clickable**: Notifications link to `/hr/my-policies`

## Data Model

### CompanyPolicy
- `title`, `description`, `category`, `content` (HTML/richtext), `version`, `status` (draft/published/archived)
- `requires_acknowledgment`, `acknowledgment_required_by`, `priority`
- `distribution_scope` (JSON): target departments, roles, locations — null = all
- Implements `Documentable` for PDF attachments
- Uses `HasCompanyScope` for multi-tenant isolation

### PolicyAcknowledgment
- `company_policy_id`, `employee_id`, `user_id`, `status` (pending/acknowledged/declined)
- `acknowledged_at`, `ip_address`, `user_agent`, `signature`
- Unique constraint: `(company_policy_id, employee_id)`

## Permissions

| Permission | Roles |
|---|---|
| `view_company_policy` | All authenticated users |
| `create_company_policy`, `edit_company_policy`, `delete_company_policy` | super_admin, admin, company_admin, hr_manager, hr_officer |
| `manage_policy_distribution` | super_admin, admin, company_admin, hr_manager |
| `view_policy_compliance_report` | super_admin, admin, company_admin, hr_manager, hr_officer |

## Routes

| URL | Method | Purpose |
|---|---|---|
| `/hr/company-policies` | GET | List view (DataTable) |
| `/company-policies/create` | GET | Create form (full page) |
| `/company-policies/{id}` | GET | Detail view |
| `/company-policies/{id}/edit` | GET | Edit form (full page) |
| `/hr/dashboard-policies-overview` | GET | Compliance dashboard |
| `/hr/policy-acknowledgments` | GET | Acknowledgment report |
| `/hr/my-policies` | GET | ESS policy hub |
| `/policies/{id}/print` | GET | Print-friendly view |
| `/policies/company-policies` | GET | Redirect → `/hr/company-policies` |

## Console Commands

```bash
# Dry-run (preview without sending)
php artisan policies:send-reminders --dry-run

# Custom reminder window
php artisan policies:send-reminders --days-before=7

# Send reminders
php artisan policies:send-reminders
```

Scheduled daily at 8 AM via `app/Console/Kernel.php`.

## Notification Templates

| Type | Channels |
|---|---|
| `policy_published` | database, mail |
| `policy_updated` | database, mail |
| `policy_acknowledgment_reminder` | database, mail |
| `policy_acknowledgment_overdue` | database, mail |

Seed: `php artisan db:seed --class=App\\Modules\\Policies\\Database\\Seeders\\PolicyNotificationTemplateSeeder`

## Key Design Decisions

1. **Hybrid architecture**: Business logic in Policies module, navigation in HR module
2. **`crudType: pages`**: Full-page forms for richtext content editing
3. **Event-driven notifications**: Uses `DataTableRecordSaved` event listener (not inline in DataTable)
4. **PolicyDistributionService**: Resolves which policies apply to an employee based on `distribution_scope`
5. **Inline acknowledgment modal**: Built into MyPoliciesHub component (no separate component needed)
