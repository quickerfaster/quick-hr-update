# Roles & Permissions — Administrator Guide

> **Audience**: HR Administrators, System Administrators  
> **Last Updated**: September 2026  
> **Related**: [Attendance User Guide](../attendance/README.md) · [Leave User Guide](../leave/README.md)

---

## Table of Contents

1. [Overview](#1-overview)
2. [Default Roles](#2-default-roles)
3. [What Each Role Can Do](#3-what-each-role-can-do)
4. [What Users See (Role-Based Visibility)](#4-what-users-see-role-based-visibility)
5. [Managing Permissions](#5-managing-permissions)
   - [5.1 Viewing a Role's Permissions](#51-viewing-a-roles-permissions)
   - [5.2 Adding Permissions to a Role](#52-adding-permissions-to-a-role)
   - [5.3 Removing Permissions from a Role](#53-removing-permissions-from-a-role)
   - [5.4 Assigning Roles to Users](#54-assigning-roles-to-users)
   - [5.5 Creating a Custom Role](#55-creating-a-custom-role)
6. [What Happens After Permission Changes](#6-what-happens-after-permission-changes)
7. [Special Roles (Super Admin & Company Admin)](#7-special-roles-super-admin--company-admin)
8. [Troubleshooting](#8-troubleshooting)
9. [Glossary](#9-glossary)

---

## 1. Overview

The HR system uses a **role-based access control** system. This means:

- Every user is assigned one or more **roles** (like "HR Manager" or "Employee").
- Each role has a set of **permissions** (like "View Employees" or "Approve Leave").
- What a user can see and do depends on the permissions granted to their role.

This guide explains the default roles, what they can do, and how to manage permissions as your organization's needs change.

### Key Concepts

| Term | Meaning |
|---|---|
| **Role** | A named set of permissions (e.g., "HR Manager", "Payroll Officer") |
| **Permission** | A specific action a user can perform (e.g., "View Employees", "Create Leave Request") |
| **Super Admin** | A special role that can do **everything** — bypasses all permission checks |
| **Company Admin** | Like Super Admin, but scoped to company-level administration |
| **Module** | A section of the system (HR, Attendance, Leave, Payroll, Holiday, Organization) |

---

## 2. Default Roles

The system comes with the following roles pre-configured. Each role is designed for a specific job function.

| # | Role | Typical User | Purpose |
|---|---|---|---|
| 1 | **Super Admin** | IT Administrator, System Owner | Full system access — can do everything, see everything |
| 2 | **Company Admin** | Senior Executive, Company Owner | Company-level administration — manage users, settings, view all data |
| 3 | **HR Manager** | Head of HR, HR Director | Full HR module access — manage employees, attendance, leave, view payroll |
| 4 | **HR Officer** | HR Staff, HR Coordinator | Operational HR — manage employees, attendance, leave (no deletions) |
| 5 | **Payroll Officer** | Payroll Specialist, Payroll Clerk | Payroll processing — create runs, generate payslips, manage pay schedules |
| 6 | **Accountant** | Finance Staff, Auditor | Financial oversight — view payroll data, export reports (read-only) |
| 7 | **Manager** | Department Head, Team Lead | Team management — view team data, approve leave requests |
| 8 | **Supervisor** | Shift Supervisor, Floor Manager | Team oversight — view team attendance, approve leave |
| 9 | **Recruiter** | Talent Acquisition, Hiring Manager | Onboarding — send invitations, create employee records |
| 10 | **Employee** | All Staff | Self-service — view own data, request leave, clock in/out |

---

## 3. What Each Role Can Do

### 3.1 HR Manager

The HR Manager has the broadest access after Super Admin and Company Admin.

**Can do:**
- View, create, edit, and delete employee records, profiles, and positions
- Manage job titles, teams, tags, and employee groups
- View and manage company, department, and location information
- Send and manage employee invitations
- View and manage all attendance records, clock events, and sessions
- Create and edit attendance policies, shifts, work patterns, and schedules
- Recalculate attendance when corrections are needed
- View, create, edit, and delete leave requests for any employee
- Manage leave types, leave balances, and leave approvers
- Approve or cancel leave requests
- View and manage holidays and holiday calendars
- Create holidays in batch
- View payroll runs, payslips, policies, and schedules (oversight only — cannot process payroll)
- View organization structure, reports, and charts
- See data across all companies

**Cannot do:**
- Process or approve payroll runs (this is the Payroll Officer's job)
- Access system-level settings (reserved for Super Admin)

---

### 3.2 HR Officer

The HR Officer handles day-to-day HR operations.

**Can do:**
- View, create, and edit employee records and profiles (cannot delete)
- View and create employee job history entries
- View job titles, teams, and tags
- View company, department, and location information
- Send and edit employee invitations
- View and create attendance records and adjustments
- View clock events, sessions, policies, shifts, and work patterns
- View and manage leave requests
- View and create leave balances
- Approve leave requests
- View holidays and holiday calendars
- View payroll runs and payslips (read-only)
- View organization structure

**Cannot do:**
- Delete employee records, profiles, or positions
- Delete attendance policies, shifts, or work patterns
- Delete leave types or leave requests
- Create or edit holidays
- Process payroll

---

### 3.3 Payroll Officer

The Payroll Officer is responsible for all payroll processing.

**Can do:**
- View, create, edit, and delete payroll runs
- Generate, view, edit, and export payslips
- Create and manage payroll policies, pay schedules, and payslip items
- Manage employee payroll profiles and bank details
- Create and manage one-time and recurring payroll adjustments
- Process payroll runs (calculate and generate payslips)
- Approve payroll runs
- View employee records (needed for payroll processing)
- View attendance records (needed for hourly pay calculations)
- View leave requests and balances (needed for leave deductions)

**Cannot do:**
- Edit or delete employee records
- Manage attendance policies or shifts
- Approve leave requests

---

### 3.4 Accountant

The Accountant has read-only access to financial data for reporting and auditing.

**Can do:**
- View payroll runs, payslips, policies, and schedules
- Export payroll data and download payslip PDFs
- View employee records (basic information only)
- View attendance records
- View company and department reports
- View financial reports and summaries

**Cannot do:**
- Create, edit, or delete any records
- Process or approve payroll
- Manage employees

---

### 3.5 Manager

The Manager oversees their team's data and approves leave.

**Can do:**
- View employee records, profiles, and positions in their team
- View team job history
- View teams and employee groups
- View department and location information
- View team attendance records, clock events, and sessions
- View shifts, schedules, and work patterns
- View and create leave requests
- **Approve leave requests** for team members
- View leave types and balances
- View holidays and holiday calendars
- View team payslips

**Cannot do:**
- Create or edit employee records
- Edit attendance records
- Delete leave requests
- Process payroll

---

### 3.6 Supervisor

The Supervisor has similar access to the Manager but more limited.

**Can do:**
- View employee records and profiles in their team
- View teams
- View team attendance and clock events
- View shifts and work patterns
- View and create leave requests
- **Approve leave requests** for team members
- View leave types and balances
- View holidays and holiday calendars

**Cannot do:**
- Create or edit employee records
- View employee job history
- View payslips
- Access organization reports

---

### 3.7 Recruiter

The Recruiter focuses on bringing new employees into the system.

**Can do:**
- View, create, edit, and delete employee invitations
- View and create employee records and profiles
- View and create employee positions
- View job titles, departments, locations, and companies
- View and upload documents
- View organization structure

**Cannot do:**
- Delete employee records
- Access attendance, leave, or payroll modules
- Manage existing employee data beyond initial creation

---

### 3.8 Employee

The Employee role is for self-service — every staff member should have this role.

**Can do:**
- View the My Portal dashboard
- View the Leave Hub (request leave, see own leave history)
- View the Team Calendar
- **Clock in and clock out** (web or mobile)
- View own attendance overview
- View and create leave requests
- View leave types and available balances
- View holidays and holiday calendars
- View own payslips
- Export own data (attendance, documents, payslips) and retrieve files from the history panel

**What they see in the top navigation:**
- Only the **My Portal** tab (other tabs like Dashboard, People, Manage are hidden)
- No module switcher dropdown
- Notifications bell, quick search, quick actions, language switcher, and profile menu
- Background jobs history icon (to retrieve exported files)

**Cannot do:**
- View other employees' data
- Edit or delete any records
- Approve leave requests
- Access admin dashboards or configuration pages
- Switch between modules (the module switcher is hidden)
- See other users' exports or imports in the history panel

---

## 4. What Users See (Role-Based Visibility)

### 4.1 Module Switcher

The **module switcher** dropdown (top-left corner) lets users switch between modules like HR, Payroll, Attendance, etc. It is only visible to users with administrative or specialist roles:

| Role | Sees Module Switcher? | Available Modules |
|---|---|---|
| Super Admin, Admin, Company Admin | ✅ Yes | All modules |
| HR Manager, HR Officer | ✅ Yes | HR, Organization, Attendance, Leave, Holiday, Payroll |
| Payroll Officer | ✅ Yes | Payroll, HR (employees), Attendance (view) |
| Manager, Supervisor | ✅ Yes | HR (My Portal + People), Attendance, Leave, Holiday |
| Accountant | ✅ Yes | Payroll (view), HR (view), Organization |
| Recruiter | ✅ Yes | HR (Onboarding + People), Organization |
| **Employee** | ❌ No | N/A — all self-service is in the HR sidebar |

Employees do not see the module switcher at all. All their functionality (My Portal, Leave Hub, Team Calendar, attendance, payslips) is available through the sidebar links within the HR module.

### 4.2 Top Navigation Tabs (Context Groups)

The tabs in the top navigation bar (Dashboard, My Portal, People, Manage, etc.) are **permission-gated**. Each tab is only visible if your role has the corresponding permission:

| Tab | Required Permission | Who Sees It |
|---|---|---|
| **My Portal** | `view_my_portal` | All roles |
| **Dashboard** | `view_hidden_dashboard` | Super Admin, Admin, Company Admin only |
| **Organization** | `view_organization_overview` | HR Manager, HR Officer, Recruiter, Admins |
| **People** | `view_people_overview` | HR Manager, HR Officer, Manager, Supervisor, Recruiter, Admins |
| **Manage** | `view_manage_overview` | HR Manager, HR Officer, Recruiter, Admins |
| **Onboarding** | `view_invitation` | HR Manager, HR Officer, Recruiter, Admins |

**For employees**: Only the **My Portal** tab is visible. All other tabs are hidden because the employee role does not have those permissions. This keeps the interface clean and prevents confusion from clicking tabs that would redirect them back to My Portal.

### 4.3 Top Navigation Right-Side Icons

The icons on the right side of the top bar are also role-controlled:

| Icon | Purpose | Who Sees It |
|---|---|---|
| 🏢 **Company Switcher** | Switch between companies | All users (if multi-company) |
| 🔔 **Notifications** | View in-app notifications | All roles |
| 🔍 **Quick Search (Cmd+K)** | Search actions, records, pages | All roles (results are permission-filtered) |
| ⚡ **Quick Actions** | Frequently used actions dropdown | All roles (actions are permission-filtered) |
| 🕐 **Background Jobs** | View export/import history | All roles (see §4.5 for details) |
| 🌐 **Language** | Switch interface language | All users |
| 👤 **Profile Menu** | Account settings, logout | All users |

### 4.4 Data Tables

Within each module, the data tables (lists of records) automatically show or hide action buttons based on permissions:

- **View button** — shown if the user has `view_{resource}` permission
- **Edit button** — shown if the user has `edit_{resource}` permission
- **Delete button** — shown if the user has `delete_{resource}` permission
- **Create button** — shown if the user has `create_{resource}` permission
- **Export/Print buttons** — shown if the user has `export_{resource}` or `print_{resource}` permission

### 4.5 Background Jobs (Exports & Imports)

When you export data (e.g., download an employee list as Excel) or import data, the file is generated in the background. A popup appears when the job is complete with a **Download** button.

**If you accidentally close the popup**, you can retrieve your file by clicking the 🕐 **history icon** in the top navigation bar. This opens a panel showing your recent exports and imports.

**User-scoped filtering**:
- **Regular users** (employees, managers, HR officers, etc.) see **only their own** exports and imports
- **Administrators** (Super Admin, Admin, Company Admin) see **all users'** exports and imports — useful for monitoring and troubleshooting

This means every user who can export data also has access to the history icon to retrieve their files.

### 4.6 Approval Workflows

When a record (like a leave request or payroll run) goes through an approval workflow:

- Users with the `approve_leave_request` permission can approve leave requests
- Users with the `approve_payroll_run` permission can approve payroll runs
- Super Admins and Company Admins can approve anything
- The system also checks if the user is listed as an approver in the workflow definition

---

## 5. Managing Permissions

### 5.1 Viewing a Role's Permissions

1. Log in as a **Super Admin** or **Company Admin**.
2. Navigate to **Administration → Access Control**.
3. In the "Scope" dropdown, select **Role**.
4. Choose the role you want to inspect from the second dropdown.
5. Select a module from the "Module" dropdown.
6. The page displays all permissions for that module, with toggles showing which are **on** (green) or **off** (grey) for the selected role.

![Access Control Manager — toggle switches for each permission grouped by resource]

### 5.2 Adding Permissions to a Role

1. Follow steps 1–5 in [§5.1](#51-viewing-a-roles-permissions) to navigate to the role's permissions.
2. Find the permission you want to add. Permissions are grouped by resource (e.g., "Employee", "Leave Request").
3. Click the toggle switch next to the permission to turn it **on** (green).
4. The change is saved immediately. A success message appears at the top of the page.

**Example**: To let HR Officers delete leave requests:
1. Select **Role** → **hr_officer** → **Leave** module
2. Find the "Leave Request" card
3. Turn on the **Delete** toggle
4. Done — all HR Officers can now delete leave requests

### 5.3 Removing Permissions from a Role

1. Follow steps 1–5 in [§5.1](#51-viewing-a-roles-permissions).
2. Find the permission you want to remove.
3. Click the toggle switch to turn it **off** (grey).
4. The change is saved immediately.

**⚠️ Caution**: Removing a permission takes effect immediately for all users with that role. If a user is currently on a page that requires the removed permission, they will see an "Access Denied" message on their next action.

### 5.4 Assigning Roles to Users

1. Navigate to **Administration → User Management** (or **Administration → Assign User Roles**).
2. Find the user in the list and click their name.
3. In the "Roles" section, check or uncheck roles to assign or remove them.
4. Click **Save**.

**Best Practice**: Most users should have the **Employee** role plus one additional role:
- HR staff: Employee + HR Officer (or HR Manager)
- Payroll staff: Employee + Payroll Officer
- Managers: Employee + Manager
- Recruiters: Employee + Recruiter

A user can have multiple roles. Their effective permissions are the **union** of all permissions from all their roles.

### 5.5 Creating a Custom Role

If the default roles don't fit your organization's structure, you can create custom roles.

**Via the UI (recommended for most users):**

1. Navigate to **Administration → Roles**.
2. Click the **Add Role** button.
3. Enter a **Role Name** (use lowercase with underscores, e.g., `shift_manager`).
4. Click **Save**.
5. The new role appears in the roles list. It starts with **no permissions**.
6. Follow [§5.2](#52-adding-permissions-to-a-role) to assign permissions to your new role.
7. Follow [§5.4](#54-assigning-roles-to-users) to assign the role to users.

**Via the command line (for developers):**

```bash
# Seed all permissions from module configs
php artisan db:seed --class=AccessControlPermissionSeeder

# Verify what was discovered
php artisan ui-library:discover
```

---

## 6. What Happens After Permission Changes

### Immediate Effects

- **Navigation menu updates**: If you remove a module-access permission, that module disappears from the user's sidebar the next time they load a page.
- **Action buttons hide/show**: Create, Edit, and Delete buttons appear or disappear immediately on data tables.
- **Page access is blocked**: If a user navigates to a page they no longer have permission for, they see an "Access Denied" message and are redirected to their dashboard.

### Delayed Effects

- **Active sessions**: Users who are already logged in will see changes on their next page load or action. They do not need to log out and back in.
- **Cached permissions**: The system caches permissions for performance. Changes take effect within a few seconds. If a change doesn't appear to take effect, wait 30 seconds and refresh the page.

### What Does NOT Happen

- **Data is never deleted**: Removing a permission only hides data and blocks actions. No records are deleted.
- **Other users are not affected**: Changing one role's permissions only affects users with that role.
- **Super Admins are never restricted**: The Super Admin and Company Admin roles always bypass all permission checks. You cannot accidentally lock yourself out.

---

## 7. Special Roles (Super Admin & Company Admin)

### Super Admin

The Super Admin role is created during system installation. It has **unrestricted access** to everything:

- All modules, all pages, all actions
- All companies (in multi-company setups)
- System settings and configuration
- User and role management

**Important**: There should be at least one Super Admin user at all times. The system ensures the Super Admin role can never be deleted or have its permissions reduced through the UI.

### Company Admin

The Company Admin role is similar to Super Admin but intended for company-level administration:

- Full access within their company
- Can manage users and assign roles (except Super Admin and other Company Admins)
- Can view all company data
- Cannot access system-level settings

### Admin Bypass

Both Super Admin and Company Admin **bypass all permission checks**. This means:

- They always see all navigation items
- They always see all action buttons (Create, Edit, Delete)
- They can always view, edit, and delete any record
- They can always approve any workflow

This bypass is built into the system and cannot be disabled. It ensures administrators are never locked out of any part of the system.

### Role Assignment Hierarchy

When inviting new users or creating employee records, the system restricts which roles can be assigned based on a **role hierarchy**. This prevents privilege escalation — for example, an HR Officer cannot accidentally (or maliciously) assign the `super_admin` role to a new user.

| Your Role | Roles You Can Assign |
|---|---|
| **Super Admin** | All roles (no restrictions) |
| **Admin / Company Admin** | All roles except `super_admin` |
| **HR Manager** | `hr_officer`, `manager`, `supervisor`, `recruiter`, `employee` |
| **HR Officer** | `manager`, `supervisor`, `employee` |
| **Recruiter** | `employee` only |
| **Payroll Officer, Accountant, Manager, Supervisor** | `employee` only |

**How it works**:
- The role dropdown in invitation forms and employee creation screens only shows roles you are allowed to assign
- If someone tries to bypass the UI (e.g., via a direct API call), the system validates the role against the hierarchy and falls back to `employee` if the role is not allowed
- The hierarchy is defined in the system configuration and can be adjusted by a Super Admin if your organization's structure changes

**Why this matters**: It ensures that only senior administrators can create other administrators, maintaining the security integrity of your system.

### Wizard & Special Page Access

Some pages use wizards or special components that require specific permissions beyond module access:

| Page | Required Permission | Who Can Access |
|---|---|---|
| `/hr/employee-onboarding` | `view_invitation` | HR Manager, HR Officer, Recruiter, Admins |
| `/payroll/payroll-wizard` | `create_payroll_run` | Payroll Officer, Admins |
| `/holiday/holiday-batch-creation` | `create_holiday` | HR Manager, Admins |

If you get a 403 error on these pages, your role lacks the specific permission — even if you can access the parent module. Contact your administrator to have the permission added to your role.

---

## 8. Troubleshooting

### "I can't see a module in the sidebar"

**Cause**: Your role doesn't have access to that module.

**Solution**:
1. Check which roles you have (ask your administrator or check your profile).
2. An administrator can add the module to your role's permissions via **Administration → Access Control**.
3. Alternatively, an administrator can assign you an additional role that has access to that module.

### "The Create/Edit/Delete button is missing"

**Cause**: Your role doesn't have the specific action permission for that resource.

**Solution**: An administrator needs to enable the specific permission (e.g., `create_employee`, `edit_leave_request`) for your role.

### "I get 'Access Denied' when trying to view a page"

**Cause**: Your role doesn't have the `view_{resource}` permission for that page, or the page's module is not in your allowed modules.

**Solution**: An administrator needs to grant the appropriate view permission or module access.

### "I dismissed the export download popup — how do I get my file?"

**Cause**: When you export data, a popup appears with a Download button when the file is ready. If you close it by mistake, the popup is gone.

**Solution**: Click the 🕐 **history icon** (clock icon) in the top navigation bar. This opens the Background Jobs panel showing all your recent exports and imports. Find your file in the list and click **Download**. Administrators can see all users' exports here; regular users see only their own.

### "I see a 'More' button in the top navigation with nothing in it"

**Cause**: The "More" dropdown only shows tabs you have permission to access. If all overflow tabs are hidden (because your role lacks their permissions), the "More" button hides automatically.

**Solution**: This is expected behavior — no action needed. The button disappears when there are no accessible tabs to show.

### "I changed permissions but the user still sees the old buttons"

**Cause**: Permission cache hasn't refreshed yet.

**Solution**: Wait 30 seconds and ask the user to refresh their browser page (F5 or Ctrl+R). If the issue persists, the administrator can run:
```bash
php artisan cache:clear
```

### "I accidentally removed all permissions from a role"

**Solution**: The system keeps a record of default permissions. An administrator can:
1. Go to **Administration → Access Control**
2. Select the role
3. Manually re-enable the needed permissions, OR
4. Run the database seeder to reset to defaults:
   ```bash
   php artisan db:seed --class="App\Modules\Hr\Database\Seeders\HrRoleSeeder"
   ```

### "I see roles I shouldn't be able to assign in a dropdown"

**Cause**: The role dropdown isn't respecting the assignment hierarchy.

**Solution**: This is a known configuration point. Clear the caches first:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```
If the roles still appear, contact a developer — the dropdown may be using a stale published view or a field type that bypasses the hierarchy filter.

### "An admin gets '403 This action is unauthorized'"

**Cause**: The page uses a permission check that doesn't respect the admin bypass.

**Solution**: Admins (`super_admin`, `admin`, `company_admin`) should bypass all permission checks. If they hit a 403, the route may be using a raw permission check instead of the library's [`AuthorizationService`](../../../ui-library/src/Services/AccessControl/AuthorizationService.php). Contact a developer to review the route's middleware.

---

## 9. Glossary

| Term | Definition |
|---|---|
| **Access Control** | The system that determines who can see and do what |
| **Role** | A named collection of permissions (e.g., "HR Manager") |
| **Permission** | A specific allowed action (e.g., "view_employee", "create_leave_request") |
| **Resource** | A type of data in the system (e.g., Employee, Leave Request, Payroll Run) |
| **CRUD** | Create, Read (View), Update (Edit), Delete — the four basic operations |
| **Module** | A major section of the system (HR, Attendance, Leave, Payroll, Holiday, Organization) |
| **Super Admin** | A role with unrestricted access to everything |
| **Bypass** | When a user is allowed to skip permission checks (Super Admins and Company Admins) |
| **Self-Service (ESS)** | Employee Self-Service — employees viewing and managing their own data |
| **Workflow** | An approval process where records go through multiple reviewers before being finalized |
| **Seeder** | A script that populates the database with default data (roles, permissions, etc.) |

---

## Need More Help?

- For technical details about how permissions work in the code, see the [Permissions & Notifications](../../docs/consuming-app/permissions-and-notifications.md) developer guide.
- For questions about specific module features, see the [Attendance User Guide](../attendance/README.md) or [Leave User Guide](../leave/README.md).
- Contact your system administrator for role and permission changes.
