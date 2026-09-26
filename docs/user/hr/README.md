# HR Module — User Guide

## Overview

The HR module is the core of the employee-management lifecycle. It lets HR staff manage employees, job titles, positions, teams, departments, companies, locations, documents, and employee profiles — and it drives the entire **invitation and onboarding** flow that brings new people into the system.

This guide covers three broad audiences:

1. **HR Administrators** (HR Manager, HR Officer, Recruiter) — managing employees and organization data, inviting new users, and completing onboarding for new hires.
2. **Employees** — the self-service "My Portal", which lets invited employees complete their own details without HR hand-holding.
3. **Managers & Supervisors** — team-level visibility and leave approvals (covered here and in the [Leave Guide](../leave/README.md)).

> **Related**: [Roles & Permissions](../roles-and-permissions/README.md) · [Attendance Guide](../attendance/README.md) · [Leave Guide](../leave/README.md)

---

## Table of Contents

1. [Roles & Permissions](#1-roles--permissions)
2. [Key Concepts](#2-key-concepts)
3. [The Invitation Lifecycle](#3-the-invitation-lifecycle)
4. [Employee Onboarding](#4-employee-onboarding)
5. [Managing Employees & Organization Data](#5-managing-employees--organization-data)
6. [Employee Self-Service (My Portal)](#6-employee-self-service-my-portal)
7. [Validation Rules](#7-validation-rules)
8. [Troubleshooting & Error Recovery](#8-troubleshooting--error-recovery)
9. [Glossary](#9-glossary)

---

## 1. Roles & Permissions

The HR module is permission-gated. What you can see and do depends on your role.

| Role | Typical Access | Can Invite? |
|------|----------------|-------------|
| **HR Manager** | Full HR — manage employees, positions, profiles, job titles, teams, departments, companies, locations, documents; view payroll (oversight) | ✅ Yes |
| **HR Officer** | Operational HR — create/edit employees and positions (no deletions), manage leave and attendance | ✅ Yes |
| **Recruiter** | Onboarding — send invitations, create employee records and positions, upload documents | ✅ Yes |
| **Manager / Supervisor** | Team-level view — view team data, approve leave | ❌ No |
| **Employee** | Self-service — My Portal, own records only | ❌ No |

Key permissions you'll encounter:

| Permission | What it gates |
|------------|---------------|
| `view_employee`, `create_employee`, `edit_employee`, `delete_employee` | Employee record actions |
| `view_employee_position`, `create_employee_position`, `edit_employee_position` | Job/position actions |
| `view_invitation`, `create_invitation` | Invitation management and the onboarding wizard |
| `view_job_title`, `view_department`, `view_company`, `view_location` | Organization reference data |

> See the [Roles & Permissions Guide](../roles-and-permissions/README.md) for the complete matrix and how to adjust permissions.

---

## 2. Key Concepts

| Term | Meaning |
|------|---------|
| **Employee** | The core person record (`employees` table) with name, email, phone, hire date, company, and tags. |
| **Employee Position** | A job assignment — job title, department, reporting line, compensation. One employee can hold multiple positions over time (job history). |
| **Employee Profile** | Extended personal record — personal details, emergency contacts, documents. |
| **Employee Job History** | The timeline of an employee's past and current positions. |
| **Job Title / Department / Company / Location** | Organization reference data used to scaffold employee records. |
| **Invitation** | A time-limited, tokenized email that invites a person to create their account. |
| **Onboarding Status** | A per-employee field (`company_pending`, `position_pending`, or `complete`) tracking how far a new hire has progressed. |

---

## 3. The Invitation Lifecycle

The invitation flow is the entry point into the employee-management lifecycle. It underpins everything else because accepting an invitation creates the **user account** that the employee record is later linked to.

### 3.1 How an Invitation Is Created

There are three ways an invitation gets created:

1. **"Send Invitation" from the Onboarding overview** — the Onboarding dashboard (`/hr/onboarding-overview`) has a "Send Invitation" action card that opens the invitation form.
2. **The Invitations page** (`/hr/invitations`) — a data table with "New Invitation" and "Invite Employee" buttons.
3. **Auto-invite on employee creation** — when you create an employee via `/employees/create` and check "Send Invitation", the system automatically creates a pre-linked invitation for that employee.

> **"Invite Employee" vs "New Invitation"**: The "Invite Employee" button opens the invitation form with a searchable employee dropdown so you can link the invitation to an existing employee record. "New Invitation" is for inviting someone whose employee record doesn't exist yet.

### 3.2 The Role Dropdown Is Restricted by Your Hierarchy

When you select a role to assign to the invited user, the dropdown only shows roles **you are allowed to assign** — not every role in the system.

| Your Role | Roles You Can Assign |
|-----------|----------------------|
| **Super Admin** | All roles |
| **Admin / Company Admin** | All roles except `super_admin` |
| **HR Manager** | `hr_officer`, `manager`, `supervisor`, `recruiter`, `employee` |
| **HR Officer** | `manager`, `supervisor`, `employee` |
| **Recruiter** | `employee` only |
| **Payroll Officer, Accountant, Manager, Supervisor** | `employee` only |

This **prevents privilege escalation** — an HR Officer cannot accidentally assign `super_admin` to a new hire.

### 3.3 Step-by-Step: Sending an Invitation

1. Navigate to **Onboarding → Send Invitation** (or **/hr/invitations**).
2. Enter the **email address** of the person to invite.
3. Select the **role** to assign (restricted by your hierarchy — see §3.2).
4. Optionally add a personal **message**.
5. If inviting an existing employee, use **Invite Employee** and search for their record.
6. Click **Send**.

**What happens next**:
- An `Invitation` record is created with a unique 64-character token and a `pending` status.
- The invitation **expires in 7 days** by default (configurable via `ui-library.invitations.expiration_days`).
- The email is queued and sent with a department-specific subject/greeting if one is configured (see `Config/invitation_templates.php`).

### 3.4 The Invitation Email

The email contains a secure, tokenized acceptance link. The subject line and greeting are personalized by department when the invite is linked to an employee with a known department:

| Placeholder | Replaced With |
|-------------|---------------|
| `{company_name}` | The app name |
| `{employee_name}` | The invited employee's full name |
| `{department}` | The employee's department name |
| `{role}` | The assigned role |

### 3.5 Acceptance & Account Activation

1. The invited person clicks the link in the email.
2. They set a password (and optionally confirm their name).
3. The system **creates or activates their user account**:
   - Sets the password (hashed), name, and marks the account `active` with `email_verified_at`.
   - **Assigns the role** from the invitation.
   - **Assigns the company** from the invitation (both the `company_id` and the `company_user` pivot).
4. The invitation is marked `accepted` with an `accepted_at` timestamp.

### 3.6 Role Assignment & Fallback Behavior

Role assignment is **validated at acceptance time**, not just at creation time. This is a security layer against direct API bypass:

1. The invitation stores a role (either a role name or numeric role ID).
2. On acceptance, the role is resolved and checked against the **inviter's** assignable roles.
3. If the role is **outside the inviter's hierarchy**, the system logs a warning and **falls back** to the default assignable role (`employee`).

**Why this matters**: Even if someone tampers with the invitation role after creation, the inviter's original hierarchy is enforced at the moment the account is actually created.

### 3.7 Expiry, Resend & Revoke

| Action | What it does |
|--------|--------------|
| **Expire** | Pending invitations past their `expires_at` are automatically marked `expired`. A scheduled job counts them out. |
| **Resend** | Re-sends the email and **resets the expiry** to another 7 days. |
| **Revoke** | Marks the invitation `revoked` — the token becomes invalid, and the link stops working. |
| **Reminder** | For pending invitations expiring within 2 days that haven't already been reminded, a reminder email is queued. |

Status transitions: `pending` → `accepted` | `expired` | `revoked`.

### 3.8 Notifications to HR Staff

The HR module seeds database notifications that alert administrators to invitation events:

| Event | Notification |
|-------|--------------|
| Invitation accepted | `invitation_accepted` — "{email} has accepted the invitation…" |
| Invitation expired | `invitation_expired` — "{email} has expired without being accepted" |
| Invitation revoked | `invitation_revoked` — "{email} has been revoked" |

These appear in the notifications bell for the administrator (typically the recruiter or HR manager who sent the invite).

### 3.9 Linking the User to an Employee Record

On acceptance, the system links the newly created user to an employee record using a best-effort strategy:

1. **Pre-linked employee** — if the invitation was created with an `invitable` employee (via "Invite Employee" or auto-invite), that employee's `user_id` is set directly.
2. **Email matching** — otherwise the system looks for an employee with the same email (must be exactly one match).
3. **Manual linking** — if neither works, the account and employee remain unlinked; an administrator must link them manually.

---

## 4. Employee Onboarding

The HR module has **two distinct onboarding flows** that serve different people and goals.

### 4.1 HR-Facing Onboarding Wizard vs Self-Service Onboarding

| | **HR-Facing Wizard** | **Self-Service Onboarding** |
|---|---|---|
| **URL** | `/hr/employee-onboarding` | `/onboarding` (post-acceptance) |
| **User** | HR staff (Manager, Officer, Recruiter) | The invited employee |
| **Purpose** | Create employee + job info + position + related data **at once** | Let the employee complete their **own** details |
| **Permission** | `view_invitation` | Any authenticated user with an employee link |
| **Steps** | 3 (Personal & Employment → Job Setup → Review) | 4 (Employee Record → Profile → Payroll/Banking → Preferences) |

### 4.2 HR-Facing Onboarding Wizard

**Purpose**: Quick, batch-style creation of a new hire's core records in a single wizard — eliminating the need to create the employee, then separately create the position, then the profile.

**Prerequisites**:
- Your role must have `view_invitation` (HR Manager, HR Officer, Recruiter, or an admin).
- The employee's job title, department, company, and location should already exist as reference data.

**Steps**:
1. **Personal & Employment** — creates the `Employee` record (identity + employment details).
2. **Job Setup** — creates the `EmployeePosition` record (job information, employment details, compensation).
3. **Review & Confirm** — review everything before finalizing.

**On completion**, you can:
- **View Employee** — open the new employee's detail page.
- **Upload Documents** — attach a contract or other documents.
- **Add Profile Data** — open the employee profile form.
- **Employee Directory** — go to the full employee list.

### 4.3 Self-Service Onboarding (Employee-Facing)

**Purpose**: After an invited employee accepts their invitation and creates their account, they complete their **own** details through a guided wizard — dramatically reducing HR workload by removing the manual, one-by-one entry of employee, profile, job, and payroll records.

**How it works**:
- The wizard at `/onboarding` detects the authenticated user's linked employee record (matched by `user_id` or email).
- Each step saves independently (no single multi-step form submission).
- Completed steps are tracked so the employee can resume where they left off.

**Steps**:

| # | Step | What it does | Required? |
|---|------|--------------|-----------|
| 1 | **Employee Record** | Sets the employee record for the user. Pre-completed if the employee was pre-linked. | ✅ Required |
| 2 | **Employee Profile** | Personal details + emergency contacts. | Optional |
| 3 | **Payroll & Banking** | Bank details for salary payments. Excluded if the Payroll module is not installed. | Optional |
| 4 | **Notification Preferences** | Chooses how the user wants to be notified. | Optional |

### 4.4 Onboarding Status Tracking

Each employee has an `onboarding_status` field that an observer (`EmployeeOnboardingObserver`) keeps in sync automatically:

| Status | Meaning |
|--------|---------|
| `complete` | The employee has a company and a position — onboarding is done. |
| `position_pending` | The employee has a company but **no position** yet — HR must add job/position data. |
| `company_pending` | The employee has **no company** assigned — a critical gap that must be resolved. |
| *(null)* | The employee's account doesn't exist yet (invitation not accepted). |

This status drives the **Onboarding overview** dashboard widgets: "Missing Position", "Missing Company", and "Incomplete Onboarding".

### 4.5 Completing Onboarding as an Administrator

After an employee finishes self-service onboarding, HR typically still needs to assign the **employee position** (the "job info") because that's the one thing the self-service wizard doesn't handle. From the Onboarding overview:

1. Find the employee in the **Incomplete Onboarding** list.
2. If status is `company_pending`, click **Assign Company**.
3. If status is `position_pending`, click **Add Job Info** to open the position form (prefilled with the employee and company).

Once the position is added, the status automatically flips to `complete`.

---

## 5. Managing Employees & Organization Data

### 5.1 Employees

Navigate to **People → Employees** (`/hr/employees`) to view the employee directory. Create, edit, view, and delete employees as your permissions allow.

**Employee fields** include: employee number (auto-generated), first/last name, email, phone, company, employee group, hire date, and tags.

### 5.2 Employee Positions

Navigate to **People → Current Jobs** (`/hr/employee-positions`) to manage job assignments. Each position links an employee to a job title, department, and compensation.

### 5.3 Employee Profiles

Navigate to **People → Profiles** (`/hr/employee-profiles`) to manage extended personal details and emergency contacts.

### 5.4 Job History

Navigate to `/hr/employee-job-histories` to view the timeline of an employee's positions.

### 5.5 Organization Reference Data

| Resource | Route | Purpose |
|----------|-------|---------|
| **Companies** | `/hr/companies` | Multi-company structure (gated by `manage-system`) |
| **Departments** | `/hr/departments` | Department hierarchy |
| **Locations** | `/hr/locations` | Physical offices (for attendance geofencing) |
| **Job Titles** | `/hr/job-titles` | Role/job-title dictionary |
| **Teams** | `/hr/teams` | Reporting teams |
| **Employee Groups** | `/hr/employee-groups` | Grouping categories |
| **Tags** | `/hr/tags` | Free-form labels |

### 5.6 Documents

Navigate to `/hr/documents` to manage employee documents (contracts, IDs, etc.).

---

## 6. Employee Self-Service (My Portal)

Employees access everything through **My Portal** (`/hr/my-portal`). The sidebar shows:

| Link | What it does |
|------|--------------|
| **Overview** | The My Portal dashboard with your key cards. |
| **My Profile** | View and edit your own profile. |
| **Leave** | Request leave and see your leave history. |
| **Team Calendar** | See team absences. |

Other self-service pages (attendance, clock events, payslips, documents) are reachable via the My Portal dashboard cards or the sidebar, and are scoped to the employee's **own** records only.

> Employees do **not** see the module switcher, and only see the **My Portal** tab in the top navigation. All other HR context tabs (People, Manage, Organization, Onboarding) are hidden.

---

## 7. Validation Rules

| Field/Form | Rule |
|------------|------|
| **Invitation email** | Required, valid email, max 255 chars |
| **Invitation role** | Required; must be within the inviter's assignable hierarchy (else falls back to `employee`) |
| **Employee email** | Used for user linking — must match exactly for auto-link |
| **Onboarding status** | Auto-computed (`company_pending` → `position_pending` → `complete`); never manually set |

---

## 8. Troubleshooting & Error Recovery

### "I see roles I shouldn't be able to assign in the invitation form"

The role dropdown should be filtered by your hierarchy. If all roles appear, clear the caches:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### "I get a 403 on the onboarding wizard"

The HR-facing wizard requires `view_invitation`. If you can access the Onboarding overview but not the wizard, your role lacks this specific permission.

### "An invited employee can't log in"

Check the invitation status on `/hr/invitations`:
- `pending` — they haven't clicked the link yet; use **Resend**.
- `expired` — the 7-day window lapsed; **Resend** to reset the clock.
- `revoked` — an admin revoked it; create a new invitation.

### "An accepted employee is missing from the directory"

The employee may have `onboarding_status = company_pending` (no company). Assign a company via the Onboarding overview's "Assign Company" action.

### "An employee completed onboarding but still shows incomplete"

The employee is likely missing a **position**. The self-service wizard doesn't create positions — HR must add the job info via the Onboarding overview's "Add Job Info" action.

### "A user and employee record aren't linked"

If an invitation was sent to an email that doesn't match exactly one employee record, the auto-link fails. Link them manually by editing the employee and setting its `user_id`.

---

## 9. Glossary

| Term | Definition |
|------|-----------|
| **Invitation** | A time-limited, tokenized email that lets a person create their account. |
| **Onboarding status** | `company_pending`, `position_pending`, or `complete` — tracks new-hire progress. |
| **Pre-linked employee** | An employee record created before (or linked to) a user account, matched on email. |
| **Assignable roles** | The roles a given user is allowed to assign, defined by the role hierarchy. |
| **Employee Position** | A job assignment (title + department + compensation) for an employee. |
| **Self-service onboarding** | The `/onboarding` wizard that lets employees complete their own details. |
