# HR Dashboard Audit Report

**Date**: 2026-11-26
**Auditor**: AI-assisted systematic review
**Scope**: All 7 HR module dashboards (configs, widgets, data accuracy, UX, gaps)

---

## Dashboard-to-Config Mapping

| # | URL | Config File | Config Key | Module |
|---|-----|------------|------------|--------|
| 1 | `/hr/dashboard` | `Data/dashboards/dashboard.php` | `hr.dashboards.dashboard` | Hr |
| 2 | `/hr/onboarding-overview` | `Data/dashboards/onboarding_overview.php` | `hr.dashboards.onboarding_overview` | Hr |
| 3 | `/hr/dashboard-people-overview` | `Data/dashboards/people_overview.php` | `hr.dashboards.people_overview` | Hr |
| 4 | `/hr/dashboard-manage-overview` | `Data/dashboards/dashboard_manage_overview.php` | `hr.dashboards.dashboard_manage_overview` | Hr |
| 5 | `/hr/dashboard-organization-overview` | `Data/dashboards/dashboard_organization_overview.php` | `hr.dashboards.dashboard_organization_overview` | Hr |
| 6 | `/hr/dashboard-policies-overview` | `Policies/Data/dashboards/dashboard_policies_overview.php` | `policies.dashboards.dashboard_policies_overview` | Policies |
| 7 | `/hr/dashboard-loans-overview` | `Loans/Data/dashboards/dashboard_loans_overview.php` | `loans.dashboards.dashboard_loans_overview` | Loans |

---

## 1. Main HR Dashboard (`/hr/dashboard`)

**Config**: [`app/Modules/Hr/Data/dashboards/dashboard.php`](app/Modules/Hr/Data/dashboards/dashboard.php)
**Audience**: Admin, HR Manager, HR Admin (full access)
**Widgets**: 20

### Widget-by-Widget Analysis

| # | Widget | Type | Status | Recommendation | Reason |
|---|--------|------|--------|---------------|--------|
| 0 | Total Employees | stat | ✅ KEEP | — | Core HR KPI. Correct model (`Employee`), correct aggregate (`count`). |
| 1 | Active Employees | stat | ⚠️ FIX | Fix model reference | Uses `EmployeePosition` with `employment_status=Active`. This counts positions, not unique employees. An employee with 2 active positions counts twice. Should use `Employee` with a subquery or `EmployeePosition` with `distinct employee_id`. |
| 2 | New Hires (This Month) | stat | ✅ KEEP | — | Correct. Uses `hire_date >= first day of this month`. |
| 3 | Departments | stat | ✅ KEEP | — | Core org metric. |
| 4 | Job Titles | stat | 🔄 SWAP | Move to Organization or Manage dashboard | Redundant here — already on Organization and Manage dashboards. Replace with "Onboarding Pending" stat. |
| 5 | Pending Leave Requests | stat | ✅ KEEP | — | Cross-module awareness is valuable for HR managers. |
| 6 | Recent Leave Requests | list | ✅ KEEP | — | Good snapshot. |
| 7 | Present Today | stat | ✅ KEEP | — | Daily attendance pulse. |
| 8 | Absent Today | stat | ✅ KEEP | — | Complements Present Today. |
| 9 | Attendance Trend (6M) | trend | ✅ KEEP | — | Useful trend. |
| 10 | Upcoming Pay Runs | stat | ⚠️ FIX | Model reference | References `App\Modules\Hr\Models\PayrollRun` — this model likely lives in the Payroll module, not Hr. Cross-module model reference. |
| 11 | Recent Payroll Runs | list | ⚠️ FIX | Model reference | Same cross-module issue as #10. |
| 12 | Active Attendance Policies | stat | ⚠️ FIX | Model reference | References `App\Modules\Hr\Models\AttendancePolicy` — likely in Attendance module. |
| 13 | Active Work Patterns | stat | ⚠️ FIX | Model reference | References `App\Modules\Hr\Models\WorkPattern` — likely in Attendance module. |
| 14 | Active Locations | stat | 🔄 SWAP | Move to Organization dashboard | Redundant with Organization dashboard. Replace with "Turnover Rate" or "Onboarding Completion %". |
| 15 | Top Locations by Employee Count | list | 🔄 SWAP | Move to Organization dashboard | Redundant. Replace with "Recent Onboarding Completions" list. |
| 16 | Request Leave | action_card | ❌ REMOVE | — | This is an employee self-service action. Does not belong on an HR admin dashboard. |
| 17 | Process Payroll | action_card | ⚠️ FIX | Change to navigate to Payroll module | The `openPayrollWizard` event may not be handled on this page. Should navigate to `/payroll` instead. |
| 18 | Clock In/Out | action_card | ❌ REMOVE | — | Employee self-service action. Does not belong on an HR admin dashboard. |
| 19 | View My Team | action_card | 🔄 SWAP | Replace with "View Onboarding" | "My Team" is manager-focused. Replace with link to Onboarding overview. |

### Cross-Module Model References (CRITICAL)

The following models are referenced from `App\Modules\Hr\Models\*` but likely belong to other modules:

| Widget # | Model Referenced | Likely Correct Module |
|----------|-----------------|----------------------|
| 5, 6 | `LeaveRequest` | `App\Modules\Leave\Models\LeaveRequest` |
| 7, 8, 9 | `Attendance` | `App\Modules\Attendance\Models\Attendance` |
| 10, 11 | `PayrollRun` | `App\Modules\Payroll\Models\PayrollRun` |
| 12 | `AttendancePolicy` | `App\Modules\Attendance\Models\AttendancePolicy` |
| 13 | `WorkPattern` | `App\Modules\Attendance\Models\WorkPattern` |

**Verdict**: These cross-module references will cause 500 errors if the models don't exist at those paths. The dashboard config must use the correct FQCN for each model. If the consuming app has these models under `Hr\Models` as proxies, that's an architectural smell — the library boundary rule says models should live in their owning module.

### Gaps

| Missing Widget | Why Needed |
|---------------|------------|
| Onboarding Completion % | HR managers need to see onboarding health at a glance |
| Pending Invitations count | Critical for HR workflow — how many invites are outstanding |
| Employee Turnover Rate | Key workforce metric (separations this month / avg headcount) |
| Gender Diversity ratio | DEI metric expected in modern HR dashboards |
| Department Headcount chart | Visual distribution of workforce |
| Recent Onboarding Completions | Celebrate new hires who finished onboarding |

---

## 2. Onboarding Overview (`/hr/onboarding-overview`)

**Config**: [`app/Modules/Hr/Data/dashboards/onboarding_overview.php`](app/Modules/Hr/Data/dashboards/onboarding_overview.php)
**Audience**: HR Manager, HR Officer, Recruiter
**Widgets**: 10

### Widget-by-Widget Analysis

| # | Widget | Type | Status | Recommendation | Reason |
|---|--------|------|--------|---------------|--------|
| 0 | Pending Invitations | stat | ✅ KEEP | — | Core onboarding metric. |
| 1 | Recent Hires This Month | stat | 🐛 BUG | Add date condition | **BUG**: No `conditions` array — counts ALL employees, not just recent hires. Missing `hire_date >= first day of this month`. |
| 2 | Missing Position | stat | ✅ KEEP | — | Good gap tracker. Uses `onboarding_status=position_pending`. |
| 3 | Missing Company | stat | ✅ KEEP | — | Good gap tracker. Uses `onboarding_status=company_pending`. |
| 4 | Incomplete Onboarding | stat | ✅ KEEP | — | Aggregate count of all non-complete. |
| 5 | Incomplete Onboarding | list | ✅ KEEP | — | Actionable list with row actions (Assign Company, Add Job Info). |
| 6 | Recent Invitations | list | ✅ KEEP | — | Good visibility into invitation activity. |
| 7 | Send Invitation | action_card | ✅ KEEP | — | Primary onboarding action. |
| 8 | Assign Companies | action_card | ⚠️ FIX | Change color from `danger` to `warning` | `danger` implies crisis. `warning` is more appropriate for a routine task. |
| 9 | Onboard New Hire | action_card | ✅ KEEP | — | Links to the HR-facing wizard. |

### Gaps

| Missing Widget | Why Needed |
|---------------|------------|
| Invitation Acceptance Rate | % of invitations accepted vs. sent — measures invitation effectiveness |
| Avg. Time-to-Onboard | Days from invitation sent to onboarding complete — process efficiency metric |
| Onboarding Funnel | Chart: Invited → Accepted → Company Assigned → Position Assigned → Complete |
| Expiring Invitations (next 2 days) | Proactive alert for invitations about to expire |
| Onboarding by Department | Chart showing which departments have pending onboarding |

---

## 3. People Overview (`/hr/dashboard-people-overview`)

**Config**: [`app/Modules/Hr/Data/dashboards/people_overview.php`](app/Modules/Hr/Data/dashboards/people_overview.php)
**Audience**: HR Manager, HR Officer, Manager, Supervisor, Recruiter
**Widgets**: 10

### Widget-by-Widget Analysis

| # | Widget | Type | Status | Recommendation | Reason |
|---|--------|------|--------|---------------|--------|
| 0 | Total Employees | stat | ✅ KEEP | — | Core metric. |
| 1 | Employee Groups | stat | ✅ KEEP | — | Useful for segmented workforce view. |
| 2 | Active Teams | stat | ✅ KEEP | — | Team health indicator. |
| 3 | Profile Completion | progress | 🐛 BUG | Fix target logic | Uses `EmployeeProfile` count vs `target=100`. This shows "X profiles out of 100" which is meaningless. Should be: `count` of profiles with complete data / `count` of total employees × 100. Or use a `target_model` with conditions for "complete" profiles. |
| 4 | Recent Hires | list | ✅ KEEP | — | Good list with department and hire date. |
| 5 | New Hires Trend (6M) | trend | ✅ KEEP | — | Hiring velocity trend. |
| 6 | Employees by Department | chart | ✅ KEEP | — | Key workforce distribution. |
| 7 | Process Payroll | action_card | ❌ REMOVE | — | Does not belong on a People dashboard. This is a Payroll action. |
| 8 | Pending Invitations | stat | 🔄 SWAP | Move to Onboarding dashboard | Onboarding concern, not people management. Replace with "Turnover This Month". |
| 9 | Send Invitation | action_card | 🔄 SWAP | Move to Onboarding dashboard | Onboarding action. Replace with "Add Employee" action card. |

### Gaps

| Missing Widget | Why Needed |
|---------------|------------|
| Employee Turnover Rate | Separations / avg headcount — critical workforce health metric |
| Headcount by Location | Where are people located? |
| Headcount by Employment Status | Active / On Leave / Terminated distribution (pie chart) |
| Gender Distribution | DEI metric (pie or bar chart) |
| Average Tenure | Years of service metric |
| Employees by Job Title | Top job titles chart |
| New Hires vs. Separations | Net headcount change trend |

---

## 4. Manage Overview (`/hr/dashboard-manage-overview`)

**Config**: [`app/Modules/Hr/Data/dashboards/dashboard_manage_overview.php`](app/Modules/Hr/Data/dashboards/dashboard_manage_overview.php)
**Audience**: HR Manager, HR Officer, Recruiter
**Widgets**: 11

### Widget-by-Widget Analysis

| # | Widget | Type | Status | Recommendation | Reason |
|---|--------|------|--------|---------------|--------|
| 0 | Job Titles | stat | ✅ KEEP | — | Reference data count. |
| 1 | Tags | stat | ✅ KEEP | — | Reference data count. |
| 2 | Job History Records | stat | ✅ KEEP | — | Transactional data count. |
| 3 | Documents | stat | ⚠️ FIX | Split into "Total" + "Expiring Soon" | Raw document count is not actionable. Add a stat for documents expiring within 30 days. |
| 4 | Recent Job Titles | list | ✅ KEEP | — | Good. |
| 5 | Tags (A–Z) | list | ✅ KEEP | — | Good. |
| 6 | Recent Job History | list | ✅ KEEP | — | Good. |
| 7 | Recent Documents | list | ⚠️ FIX | Add document type filter | Show only employee documents (not policy docs). Add a "View All" link. |
| 8 | Job Titles (Manage) | action_card | ✅ KEEP | — | Quick access to create job titles. |
| 9 | Tags (Manage) | action_card | ✅ KEEP | — | Quick access to manage tags. |
| 10 | Job History (Add Entry) | action_card | ✅ KEEP | — | Quick access to add job history. |

### Gaps

| Missing Widget | Why Needed |
|---------------|------------|
| Expiring Documents (next 30 days) | Proactive compliance — alerts HR to renew documents before expiry |
| Documents by Type | Chart showing distribution of document types |
| Job History by Month | Trend of job changes (promotions, transfers) |
| Missing Documents Alert | Employees with no documents uploaded |
| Employee Groups list | Reference data that's missing from this dashboard |

---

## 5. Organization Overview (`/hr/dashboard-organization-overview`)

**Config**: [`app/Modules/Hr/Data/dashboards/dashboard_organization_overview.php`](app/Modules/Hr/Data/dashboards/dashboard_organization_overview.php)
**Audience**: HR Manager, HR Officer, Recruiter
**Widgets**: 16

### Widget-by-Widget Analysis

| # | Widget | Type | Status | Recommendation | Reason |
|---|--------|------|--------|---------------|--------|
| 0 | Total Locations | stat | ✅ KEEP | — | Core org metric. |
| 1 | Active Locations | stat | ✅ KEEP | — | Complements total. |
| 2 | Departments | stat | ✅ KEEP | — | Core org metric. |
| 3 | Job Titles | stat | 🔄 SWAP | Replace with "Companies" stat | Job Titles already on Manage dashboard. Companies count is more relevant here. |
| 4 | Locations by Country | chart | ✅ KEEP | — | Good geographic distribution. |
| 5 | Locations by Type | chart | ✅ KEEP | — | Physical vs Remote split. |
| 6 | Departments by Company | chart | ✅ KEEP | — | Multi-company structure. |
| 7 | Recent Locations | list | ✅ KEEP | — | Good. |
| 8 | Departments (A–Z) | list | ✅ KEEP | — | Good. |
| 9 | Add New Location | action_card | ✅ KEEP | — | Quick action. |
| 10 | Add Department | action_card | ✅ KEEP | — | Quick action. |
| 11 | Manage Companies | action_card | ✅ KEEP | — | Quick action. |
| 12 | Job Titles | action_card | 🔄 SWAP | Replace with "Manage Locations" | Job Titles action already on Manage dashboard. |
| 13 | Active Companies | list | ✅ KEEP | — | Good. |
| 14 | Recent Job Titles | list | 🔄 SWAP | Move to Manage dashboard | Redundant. Replace with "Employee Count by Location" list. |
| 15 | Organization Setup Completion | progress | ⚠️ FIX | Fix target logic | Uses Location count with address / total Locations. This is a creative metric but the `target_model` should be the same model without conditions for a proper ratio. Currently it shows "X of Y locations have complete addresses" which is actually correct — but the `target_model` and `target_aggregate` should be verified to work correctly. |

### Gaps

| Missing Widget | Why Needed |
|---------------|------------|
| Employee Distribution by Location | Headcount per location — critical for org planning |
| Companies count stat | Multi-company structure overview |
| Department Hierarchy Depth | Max hierarchy levels — indicates org complexity |
| Locations Map | Visual map of office locations (future enhancement) |

---

## 6. Policies Overview (`/hr/dashboard-policies-overview`)

**Config**: [`app/Modules/Policies/Data/dashboards/dashboard_policies_overview.php`](app/Modules/Policies/Data/dashboards/dashboard_policies_overview.php)
**Audience**: HR Manager, HR Officer
**Widgets**: 5

### Widget-by-Widget Analysis

| # | Widget | Type | Status | Recommendation | Reason |
|---|--------|------|--------|---------------|--------|
| 0 | Total Policies | metric | ✅ KEEP | — | Core metric. |
| 1 | Published | metric | ✅ KEEP | — | Good status breakdown. |
| 2 | Requiring Acknowledgment | metric | ✅ KEEP | — | Compliance metric. |
| 3 | Drafts | metric | ✅ KEEP | — | Work-in-progress visibility. |
| 4 | Recent Policies | list | ✅ KEEP | — | Good. |

### Gaps (CRITICAL — this dashboard is severely underbuilt)

| Missing Widget | Why Needed |
|---------------|------------|
| **Acknowledgment Compliance Rate** | % of employees who have acknowledged required policies — THE key compliance metric |
| **Pending Acknowledgments count** | How many employees still need to acknowledge |
| **Policies by Category** | Chart showing policy distribution across categories |
| **Expiring/Review-due Policies** | Policies nearing review date |
| **Recent Acknowledgments** | List of recent policy acknowledgments |
| **Create Policy** action_card | Quick access to create new policies |
| **View Acknowledgments** action_card | Quick access to acknowledgment report |
| **Acknowledgment Trend** | Month-over-month acknowledgment completion |
| **Top Acknowledged Policies** | Which policies have highest/lowest compliance |

**Severity**: This dashboard has only 5 widgets and is missing the core compliance metrics that a policies dashboard exists to show. The `metric` widget type is also inconsistent with other dashboards that use `stat`.

---

## 7. Loans Overview (`/hr/dashboard-loans-overview`)

**Config**: [`app/Modules/Loans/Data/dashboards/dashboard_loans_overview.php`](app/Modules/Loans/Data/dashboards/dashboard_loans_overview.php)
**Audience**: HR Manager, HR Officer, Finance Manager
**Widgets**: 8

### Widget-by-Widget Analysis

| # | Widget | Type | Status | Recommendation | Reason |
|---|--------|------|--------|---------------|--------|
| 0 | Active Loans | stat | ✅ KEEP | — | Core metric. |
| 1 | Pending Approval | stat | ✅ KEEP | — | Workflow visibility. |
| 2 | Total Outstanding | stat | ✅ KEEP | — | Financial exposure. Good use of `sum` aggregate with `currency` format. |
| 3 | Overdue Repayments | stat | ✅ KEEP | — | Risk indicator. |
| 4 | Recent Loan Requests | list | ✅ KEEP | — | Good. |
| 5 | Loan Requests Trend (6M) | trend | ✅ KEEP | — | Volume trend. |
| 6 | Loans by Type | chart | ✅ KEEP | — | Distribution. |
| 7 | Repayment Progress | progress | ✅ KEEP | — | Good use of `sum` aggregates for ratio. |

### Gaps

| Missing Widget | Why Needed |
|---------------|------------|
| Loans by Status | Pie chart: Active / Paid / Defaulted / Pending |
| Average Loan Amount | Mean loan size metric |
| Top Borrowers | List of employees with highest outstanding balances |
| Approve Loan | action_card for quick approval access |
| Manage Loan Types | action_card for reference data management |
| Default Rate | % of loans in default — risk metric |
| Total Disbursed (This Month) | Monthly loan volume |

---

## Cross-Dashboard Gap Analysis

### Systemic Issues

| Issue | Affected Dashboards | Severity |
|-------|-------------------|----------|
| **Cross-module model references** | Dashboard #1 | 🔴 CRITICAL |
| **Employee self-service actions on admin dashboards** | Dashboard #1 | 🟡 MEDIUM |
| **Redundant widgets across dashboards** | #1, #3, #4, #5 | 🟡 MEDIUM |
| **Missing role-based widget visibility** | All dashboards | 🟡 MEDIUM |
| **Inconsistent widget types** (metric vs stat) | #6 vs others | 🟢 LOW |
| **No empty-state handling in configs** | All dashboards | 🟢 LOW |

### Missing Cross-Cutting Widgets

| Widget | Should Appear On | Priority |
|--------|-----------------|----------|
| Onboarding Completion % | #1 (Main), #2 (Onboarding) | HIGH |
| Employee Turnover Rate | #1 (Main), #3 (People) | HIGH |
| Pending Invitations | #1 (Main) | HIGH |
| Gender Diversity | #3 (People) | MEDIUM |
| Document Expiry Alerts | #4 (Manage) | MEDIUM |
| Acknowledgment Compliance | #6 (Policies) | CRITICAL |
| Loans by Status Chart | #7 (Loans) | MEDIUM |

### Widgets to Remove (Misplaced)

| Widget | Current Dashboard | Why Remove |
|--------|------------------|------------|
| Request Leave (action_card) | #1 Main | ESS action on admin dashboard |
| Clock In/Out (action_card) | #1 Main | ESS action on admin dashboard |
| Process Payroll (action_card) | #3 People | Payroll action on People dashboard |
| Pending Invitations (stat) | #3 People | Onboarding concern |
| Send Invitation (action_card) | #3 People | Onboarding concern |

---

## Implementation Plan

### Phase 1: Critical Fixes (Bugs)

1. **Fix "Recent Hires This Month" on Onboarding dashboard** — add missing `hire_date >= first day of this month` condition
2. **Fix "Profile Completion" on People dashboard** — change to proper ratio (complete profiles / total employees)
3. **Fix cross-module model references on Main dashboard** — update FQCNs to correct module paths
4. **Fix "Assign Companies" color** — change from `danger` to `warning`

### Phase 2: Remove Misplaced Widgets

5. Remove "Request Leave" action_card from Main dashboard
6. Remove "Clock In/Out" action_card from Main dashboard
7. Remove "Process Payroll" action_card from People dashboard
8. Move "Pending Invitations" stat from People to Onboarding
9. Move "Send Invitation" action_card from People to Onboarding

### Phase 3: Add Missing Widgets

10. Add "Onboarding Completion %" to Main dashboard
11. Add "Pending Invitations" stat to Main dashboard
12. Add "Employee Turnover Rate" to Main and People dashboards
13. Add "Acknowledgment Compliance Rate" to Policies dashboard
14. Add "Pending Acknowledgments" to Policies dashboard
15. Add "Policies by Category" chart to Policies dashboard
16. Add "Create Policy" and "View Acknowledgments" action cards to Policies dashboard
17. Add "Loans by Status" chart to Loans dashboard
18. Add "Expiring Documents" stat to Manage dashboard
19. Add "Gender Distribution" chart to People dashboard
20. Add "Headcount by Location" chart to People dashboard

### Phase 4: Reduce Redundancy

21. Remove "Job Titles" stat from Main dashboard (keep on Organization and Manage)
22. Remove "Active Locations" stat from Main dashboard (keep on Organization)
23. Remove "Top Locations" list from Main dashboard (keep on Organization)
24. Remove "Job Titles" action_card from Organization dashboard (keep on Manage)
25. Remove "Recent Job Titles" list from Organization dashboard (keep on Manage)

---

## Pre-Coding Checklist

- [ ] No library `src/` files reference `App\Modules\*` (two-domain test)
- [ ] Dashboard configs live in `app/Modules/{Module}/Data/dashboards/`
- [ ] Widget configs follow the library's widget schema
- [ ] Data queries are scoped to the current company
- [ ] All labels/text use translation keys or are human-readable

## Post-Coding Checklist

- [ ] `php -l` on every changed PHP file
- [ ] `php artisan optimize:clear`
- [ ] Refresh each dashboard in the browser — no blank pages, no 500s
- [ ] No `App\Modules\*` references leak into the library `src/`
- [ ] Run `php artisan test` — all existing tests still pass
