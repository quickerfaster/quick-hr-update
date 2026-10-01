# Payroll Module — User Guide

## Overview

The Payroll module handles the complete payroll lifecycle: configuring pay schedules and policies, running payroll calculations, reviewing and approving payroll runs, managing payslips, and generating reports. It supports three pay types (salaried full, salaried daily, hourly), multi-currency, multi-company payroll, progressive tax calculation, pension and benefit policies, and a two-step approval workflow.

This guide covers three audiences:

1. **Payroll Officers** — the primary users who configure pay schedules, run payroll, and manage payslips.
2. **HR Managers** — who review and approve payroll runs before payment.
3. **Employees** — who view their own payslips through self-service.

> **Related**: [Roles & Permissions](../roles-and-permissions/README.md) · [HR Module Guide](../hr/README.md) · [Attendance Guide](../attendance/README.md)

---

## Table of Contents

1. [Roles & Permissions](#1-roles--permissions)
2. [Key Concepts](#2-key-concepts)
3. [Setting Up Payroll](#3-setting-up-payroll)
4. [Running Payroll — The Payroll Wizard](#4-running-payroll--the-payroll-wizard)
5. [Payroll Approval Workflow](#5-payroll-approval-workflow)
6. [Payslips & Reports](#6-payslips--reports)
7. [Policies & Adjustments](#7-policies--adjustments)
8. [How Payroll Is Calculated](#8-how-payroll-is-calculated)
9. [Multi-Company Payroll](#9-multi-company-payroll)
10. [Employee Self-Service](#10-employee-self-service)
11. [Troubleshooting & Error Recovery](#11-troubleshooting--error-recovery)
12. [Glossary](#12-glossary)

---

## 1. Roles & Permissions

| Role | Typical Payroll Access |
|------|------------------------|
| **Payroll Officer** | Full payroll — create/edit/delete payroll runs, process payroll, generate payslips, manage policies and schedules, export reports |
| **HR Manager** | Oversight — view payroll runs and payslips, approve payroll runs (workflow step 2), view reports. Cannot create or process payroll. |
| **Accountant** | Read-only — view payroll runs, payslips, policies, schedules; export data and download PDFs |
| **Employee** | Self-service — view own payslips only |

Key permissions:

| Permission | What it gates |
|------------|---------------|
| `view_payroll_run`, `create_payroll_run`, `edit_payroll_run`, `delete_payroll_run` | Payroll run CRUD |
| `process_payroll_run` | Triggering payroll calculation |
| `approve_payroll_run` | Approving a payroll run |
| `view_payroll_payslip`, `export_payslip` | Payslip viewing and export |
| `view_pay_schedule`, `create_pay_schedule`, `edit_pay_schedule` | Pay schedule management |
| `view_payroll_policy`, `create_payroll_policy` | Policy management |
| `view_payroll_run_adjustment`, `create_payroll_run_adjustment` | One-time adjustments |
| `view_employee_adjustment_profile`, `create_employee_adjustment_profile` | Recurring employee adjustments |

> See the [Roles & Permissions Guide](../roles-and-permissions/README.md) for the complete matrix.

---

## 2. Key Concepts

| Term | Meaning |
|------|---------|
| **Pay Schedule** | Defines the pay frequency (monthly, bi-weekly, weekly, etc.), period dates, and currency for a group of employees. |
| **Payroll Run** | A single payroll processing instance — covers a specific period for a specific pay schedule. |
| **Payslip** | An individual employee's pay statement within a payroll run, showing gross pay, deductions (itemized by tax, pension, insurance), employer contributions, and net pay. |
| **Payroll Policy** | A recurring rule (tax, pension, insurance, benefit, bonus, deduction) that applies every pay period. Policies can be versioned by `tax_year` for correct annual tax application. |
| **Policy Assignment** | Links a policy to specific companies, departments, locations, or employee groups. |
| **Payroll Run Adjustment** | A one-time addition or deduction applied to a specific payroll run (bonus, commission, reimbursement, correction, deduction). |
| **Employee Adjustment Profile** | A recurring, employee-specific override or standalone adjustment. |
| **Pay Type** | How an employee is paid: `salaried_full` (fixed), `salaried_daily` (per day worked), or `hourly` (per hour worked). |
| **Proration** | Automatic adjustment of pay when an employee joins or leaves mid-period (based on `hire_date` and termination date). |
| **Finalization** | Locking a payroll run after calculation — finalized payslips are immutable and cannot be recalculated. Corrections use adjustment records instead. |
| **Payroll Wizard** | The 3-step guided process for creating and processing a payroll run. |

---

## 3. Setting Up Payroll

Before running payroll, you need to configure the foundational data.

### 3.1 Pay Schedules

Pay schedules define **when** and **how often** employees are paid.

1. Navigate to **Payroll → Configuration → Pay Schedules** (`/payroll/pay-schedules`).
2. Click **New Pay Schedule**.
3. Configure:

| Field | Description |
|-------|-------------|
| **Name** | Display name (e.g., "Monthly Staff", "Weekly Contractors") |
| **Frequency** | Monthly, Semi-monthly, Bi-weekly, Weekly, or Daily |
| **Period Start / End** | The date range for the first pay period |
| **Currency** | USD, GBP, EUR, NGN, CAD, AUD |
| **Company** | The company this schedule belongs to |

**Pay periods per year** (used for tax annualisation):

| Frequency | Periods/Year |
|-----------|-------------|
| Monthly | 12 |
| Semi-monthly | 24 |
| Bi-weekly | 26 |
| Weekly | 52 |
| Daily | 260 |

### 3.2 Employee Payroll Profiles

Each employee who will be paid through payroll needs a payroll profile linked to a pay schedule. **Without this profile, the employee will not appear in any payroll run** — even if they have an active position with a salary.

> **Proration**: Employees hired or terminated mid-period automatically receive prorated pay based on `hire_date` and termination date. See [§8.6 — Hire & Termination Proration](#86-hire--termination-proration).

1. Navigate to **Payroll → Configuration → Employee Profiles** (`/payroll/employee-payroll-profiles`).
2. Click **New Employee Profile**.
3. Select the **employee**, assign a **pay schedule**, and set the profile to **active**.

> **Important**: An employee must meet ALL three conditions to appear in a payroll run:
> 1. An **active Employee Payroll Profile** linked to the correct pay schedule
> 2. An **active employment status** (`Active`) on their position record
> 3. The profile's **pay schedule** must match the payroll run's pay schedule
>
> If any of these is missing, the employee will be silently excluded from the run.

### 3.3 Employee Positions (Pay Data)

The employee's **position** record (managed in the HR module at `/hr/employee-positions`) carries the pay-critical fields:

| Field | Used For |
|-------|----------|
| `base_salary` | The fixed monthly salary (all pay types) |
| `hourly_rate` | The hourly rate (hourly employees) |
| `pay_type` | `salaried_full`, `salaried_daily`, or `hourly` |
| `pay_frequency` | Monthly, Semi-monthly, Bi-weekly, Weekly, Daily |

### 3.4 Payroll Policies

Policies are recurring rules that apply every pay period. See [§7 — Policies & Adjustments](#7-policies--adjustments) for the complete guide.

---

## 4. Running Payroll — The Payroll Wizard

The Payroll Wizard is a 3-step guided process at `/payroll/payroll-wizard`. It requires the `create_payroll_run` permission (Payroll Officer or admin).

### 4.1 Step 1 — Verification

1. Select the **Pay Schedule** to run payroll for.
2. The system shows the **eligible companies** and **employee count**.
3. Enter a **Payroll Title** (e.g., "July 2026 — Monthly Staff").
4. Set the **Period Start** and **Period End** dates.
5. The wizard validates:
   - No conflicting payroll run exists for the same schedule and period.
   - Employees on the schedule have bank accounts configured.

**Multi-company mode**: Toggle "All Companies" to run payroll across all companies simultaneously. See [§9 — Multi-Company Payroll](#9-multi-company-payroll).

### 4.2 Step 2 — Adjustments

Add one-time adjustments that apply to this specific payroll run:

| Adjustment Type | Description | Example |
|-----------------|-------------|---------|
| **Bonus** | One-time bonus payment | ₦50,000 performance bonus |
| **Commission** | Sales or performance commission | 5% commission |
| **Reimbursement** | Expense reimbursement | ₦15,000 travel |
| **Correction** | Pay correction from a previous period | -₦5,000 overpayment correction |
| **Deduction** | One-time deduction | ₦10,000 loan repayment |

Adjustments can be added per-employee or as bulk entries.

### 4.3 Step 3 — Review & Preview

The system calculates all payslips and displays a preview:

- **Per-employee breakdown**: gross pay, deductions, taxes, net pay.
- **Run totals**: total gross, total deductions, total taxes, total cash required.
- **Grouped views**: by department, location, or company.

From the preview you can:
- **Finalize** — lock the run and submit it for approval.
- **Go Back** — return to adjustments.
- **Cancel** — discard the run.

### 4.4 After Finalization

Once finalized, the payroll run enters the **approval workflow** (see [§5](#5-payroll-approval-workflow)). The run status progresses through:

```
draft → verification_complete → adjustments_pending → ready_for_review → approved → paid
```

---

## 5. Payroll Approval Workflow

Payroll runs require a **two-step approval** before payment:

| Step | Approver Role | What Happens |
|------|---------------|--------------|
| **Step 1** | Payroll Officer | Reviews calculations, confirms accuracy |
| **Step 2** | HR Manager | Authorizes the run for payment |

### 5.1 How to Approve

1. Navigate to **Payroll → Processing → Approvals** (`/payroll/approvals`).
2. Find the pending payroll run.
3. Click **Approve** or **Reject**.
4. If rejected, the run returns to draft status for corrections.

### 5.2 Notifications

The workflow sends database notifications at each step:
- `workflow_submitted` — when the run is submitted for approval.
- `workflow_approved` — when a step is approved.
- `workflow_rejected` — when a step is rejected.
- `workflow_recalled` — when the submitter recalls the run.

### 5.3 After Approval

Once fully approved (`status = approved`), the Payroll Officer can:
- **Mark as Paid** — finalize payment and update the pay schedule.
- **Generate Bank File** — download a bank-ready payment file.
- **Print Summary** — generate a printable summary grouped by department, location, or company.
- **Download Summary PDF** — export the run summary as a PDF.

---

## 6. Payslips & Reports

### 6.1 Viewing Payslips

Navigate to **Payroll → Processing → Payslips** (`/payroll/payroll-payslips`) to see all payslips across all runs.

Each payslip shows:

| Field | Description |
|-------|-------------|
| **Payslip Number** | Auto-generated unique identifier (e.g., `PAYSLIP-2026-09-000042`) |
| **Employee** | The employee's name and number |
| **Base Salary** | The period base salary (prorated for mid-period hires/terminations) |
| **Gross Pay** | Total earnings (base + adjustments) |
| **Income Tax** | Federal/national income tax |
| **Social Security Tax** | Social security / national insurance contribution |
| **Medicare Tax** | Medicare / national health insurance |
| **Pension (Employee)** | Employee pension contribution |
| **Pension (Employer)** | Employer pension contribution |
| **Health Insurance (Employee)** | Employee health insurance premium |
| **Health Insurance (Employer)** | Employer health insurance premium |
| **Other Earnings** | Benefits, bonuses, commissions |
| **Other Deductions** | Loan repayments, union dues, etc. |
| **Employer Contributions** | Total employer share of all policies |
| **Total Deductions** | Sum of all deductions |
| **Total Taxes** | Sum of all taxes |
| **Net Pay** | Take-home pay (gross − deductions − taxes) |
| **Currency** | The pay currency |

### 6.2 Downloading & Printing Payslips

- **Individual payslip PDF**: Click the download icon on any payslip row.
- **Run summary PDF**: From the payroll run detail page, click "Download Summary PDF" (A4 landscape).
- **Grouped print view**: `/payroll/payroll-run/{run}/summary-grouped/{group_by}` — group by `department`, `location`, or `company`.
- **Executive summary**: `/payroll/payroll-run/{run}/executive-summary` — a high-level overview.

### 6.3 Payroll Reports

From a payroll run, you can generate:

| Report | Format | Route |
|--------|--------|-------|
| **Run Report** | Web view | `/payroll/payroll-runs/{run}/report` |
| **Run Report PDF** | PDF download | `/payroll/payroll-runs/{run}/report/download/pdf` |
| **Run Report Excel** | Excel download | `/payroll/payroll-runs/{run}/report/download/excel` |

### 6.4 Bank File

Generate a bank-ready payment file for the approved run at `/payroll/payroll-run/{run}/bank-file`.

---

## 7. Policies & Adjustments

### 7.1 Policy Types

| Type | Purpose | Example |
|------|---------|---------|
| **Tax** | Progressive tax based on income brackets. Tax policies are auto-categorized: income tax, social security (SSS/NSITF), or medicare (NHIS/NHF) based on policy name. | Income Tax, Social Security, Medicare |
| **Pension** | Employee and/or employer pension contributions. Both shares appear separately on payslips. | 8% Employee / 10% Employer |
| **Insurance** | Health, life, or other insurance premiums. Both employee and employer shares appear separately. | Health Insurance Deduction |
| **Benefit** | Recurring earnings or deductions | Car Allowance, Meal Vouchers |
| **Bonus** | Recurring bonus | 3% of Base Salary |
| **Commission** | Recurring commission | 2% of Base Salary |
| **Deduction** | Other recurring deductions | Loan Repayment, Union Dues |

> **Tax Year**: Set the `tax_year` field on tax policies to apply the correct annual tax bands for each fiscal year. This ensures payroll always uses the right tax rates even when rates change between years.

### 7.2 Calculation Methods

| Method | Description |
|--------|-------------|
| **Fixed** | A fixed amount every pay period (e.g., ₦10,000) |
| **Percentage** | Percentage of the employee's base salary or gross pay |
| **Tax Brackets** | Progressive tax rates applied to annualised income |

### 7.3 Policy Base (`base_salary` vs `gross_pay`)

Each policy can be configured to apply to either:
- **`base_salary`** — the employee's fixed salary or attendance-based base pay (before adjustments).
- **`gross_pay`** — total earnings after all adjustments (bonuses, commissions, etc.).

> **Example**: A pension policy with `base: gross_pay` deducts 8% of the total gross (including bonuses). With `base: base_salary`, it deducts 8% of only the base salary.

### 7.4 Policy Assignments

Policies are linked to employees through **assignments** that can target:
- Company
- Location
- Department
- Shift
- Employee Group
- **Global** (all employees, optionally filtered by country/state)

> **Priority Rule**: If an employee matches multiple assignments for the same policy, the assignment with the **highest priority** takes effect.

### 7.5 Parent/Child Policy Inheritance

A child policy inherits all settings from its parent unless explicitly overridden. This lets you create a company-wide standard and customize it for specific regions.

**Date merging rules**:
- Effective Date: uses the **later** of parent and child dates.
- Expiry Date: uses the **earlier** of parent and child dates.

### 7.6 One-Time Adjustments (Payroll Run Adjustments)

Applied to a specific payroll run during the wizard's Step 2. These are **not recurring** — they only affect the current run.

### 7.7 Recurring Adjustments (Employee Adjustment Profiles)

Employee-specific overrides that apply **every pay period**. They can be:
- **Standalone** — a custom amount added/deducted each period.
- **Policy overrides** — modify an existing policy for a specific employee (e.g., different pension rate).

Navigate to **Payroll → Processing → Recurring Adjustments** (`/payroll/employee-adjustment-profiles`).

---

## 8. How Payroll Is Calculated

The `PayrollCalculator` service processes each employee based on their **pay type**.

### 8.1 Salaried Full (Monthly)

```
Gross Pay = base_salary + adjustments
Deductions = sum of policy amounts (based on base_salary or gross_pay)
Net Pay = Gross Pay − Deductions
```

No attendance data is required. The full monthly salary is paid regardless of days worked.

### 8.2 Salaried Daily

```
Total Working Days = number of days in period matching the work pattern
Daily Rate = base_salary ÷ Total Working Days
Base Pay = Daily Rate × Worked Days (from approved attendance)
Gross Pay = Base Pay + adjustments
Deductions = sum of policy amounts
Net Pay = Gross Pay − Deductions
```

**Work patterns** define which days are working days (e.g., Monday–Friday). If no work pattern is assigned, the system defaults to weekdays.

**Atendance filtering**: Only **approved** attendance records (`is_approved = true`) are counted for payroll. Unapproved records, even with hours logged, are excluded. A day counts as "worked" if any of: `net_hours > 0`, `status` is not `'absent'`, or `is_paid_absence` is `true`.

### 8.3 Hourly

```
Regular Pay = regular_hours × hourly_rate
Overtime Pay = overtime_hours × hourly_rate × overtime_multiplier (default 1.5×)
Double-Time Pay = double_time_hours × hourly_rate × double_time_multiplier (default 2.0×)
Base Pay = Regular + Overtime + Double-Time
Gross Pay = Base Pay + adjustments
Deductions = sum of policy amounts
Net Pay = Gross Pay − Deductions
```

Overtime multipliers come from the employee's attendance policy (or fall back to config defaults).

**Leave & holiday hours**: Paid leave days credit standard shift hours as `regular_hours`. Unpaid leave days credit 0 hours. Paid holidays credit `minimum_hours_for_pay` (default 8h) as `regular_hours`. Unpaid holidays credit 0 hours. Half-day leave/holidays credit half the standard hours.

### 8.4 Tax Calculation (Progressive)

```
Period Gross = the base amount (base_salary or gross_pay, per policy)
Periods Per Year = based on pay_frequency (12, 24, 26, 52, or 260)
Annualised Gross = Period Gross × Periods Per Year

Tax = sum over bands:
    if Annualised Gross > band_start:
        taxable = min(Annualised Gross, band_end) − band_start
        tax += taxable × band_rate

Period Tax = Annual Tax ÷ Periods Per Year
```

### 8.5 Attendance Integration

The system can be configured to **use or ignore** attendance data via `PAYROLL_ATTENDANCE_INTEGRATION_ENABLED`:
- **Enabled (default)**: Attendance records are used for `salaried_daily` and `hourly` pay types. Only records with `is_approved = true` are counted.
- **Disabled**: All employees are treated as `salaried_full`, regardless of their assigned pay type.

### 8.6 Hire & Termination Proration

For employees who join or leave mid-period, the base salary is automatically prorated:

```
Proration Factor = days employed ÷ total days in period
Prorated Base Salary = base_salary × Proration Factor
```

- **Mid-period hires**: Pay is prorated from the employee's `hire_date` to the period end.
- **Mid-period terminations**: Pay is prorated from the period start to the termination date (`employment_status = 'Terminated'` and `deleted_at` on the position record).
- **Same-period hire+termination**: Pay is prorated to only the days between hire and termination dates.

> **Note**: Terminated employees now appear in payroll runs for their final period, ensuring statutory final-pay compliance.

### 8.7 Processing Architecture

Payroll runs are processed in **batches** (default: 100 employees per batch) via queued jobs:
1. `ProcessPayrollRun` dispatches `ProcessEmployeeBatch` jobs.
2. Each batch processes up to `batch_size` employees.
3. Progress is tracked in the `payroll_run_progress` table.
4. `FinalizePayrollRun` updates run totals after all batches complete.

**Finalization guard**: Once a payroll run is finalized (`finalized_at` is set), the calculator will **not** recalculate it. Payslips from finalized runs are immutable. To correct a finalized run, use payroll run adjustments (corrections).

Configurable in `config/quick_hr_payroll.php`:
- `batch_size` (default: 100)
- `batch_timeout` (default: 60 seconds)
- `batch_tries` (default: 1)

---

## 9. Multi-Company Payroll

When "All Companies" mode is enabled in the Payroll Wizard, the system processes payroll across **all companies simultaneously**.

### 9.1 How It Works

1. Toggle **"All Companies"** in Step 1 of the wizard.
2. The system identifies all companies with employees on active payroll profiles.
3. A single `PayrollRun` record is created with `is_multi_company = true`.
4. The `PayrollCalculator::calculateMultiCompany()` method processes each company's employees separately.
5. Per-company summaries are stored in the `per_company_summaries` JSON field.

### 9.2 Viewing Multi-Company Results

- The run detail page shows per-company breakdowns.
- Grouped print views can split by `company`.
- Each payslip retains its employee's company context.

---

## 10. Employee Self-Service

Employees can view their own payslips through **My Portal** or directly at `/payroll/my-payslips`.

### 10.1 Viewing Payslips

1. Navigate to **My Portal → My Profile** (or the payslips card on the dashboard).
2. The payslip list shows all your payslips with period, gross pay, deductions, and net pay.
3. Click **Download** to get a PDF copy.

### 10.2 Access Control

Employees can **only** view their own payslips. The [`PayslipController`](app/Modules/Payroll/Http/Controllers/PayslipController.php) verifies ownership — if the payslip's `employee_id` doesn't match the authenticated user's linked employee record, access is denied. Administrators (super_admin, admin, company_admin) bypass this check.

---

## 11. Troubleshooting & Error Recovery

### "I get a 403 on the Payroll Wizard"

The wizard requires `create_payroll_run` permission. Only Payroll Officers and admins have this. If you're an HR Manager, you can view runs but not create them.

### "Payroll calculation is taking too long"

Large payroll runs are processed in batches. Check the progress on the run detail page. If a batch fails, it will be retried automatically (configurable `batch_tries`).

### "An employee is missing from the payroll run"

This is the most common payroll setup issue. An employee needs three things to appear in a run:

1. **An active Employee Payroll Profile** — Go to **Payroll → Configuration → Employee Profiles**. Search for the employee. If they don't have a profile, create one and link it to the correct pay schedule. Make sure `is_active` is checked.
2. **Active employment status** — Go to **HR → Employee Positions**. Find the employee's position and verify `employment_status` is "Active."
3. **Matching pay schedule** — The pay schedule on the employee's payroll profile must match the pay schedule selected in the payroll run.

> **Common mistake**: Creating an employee and position is not enough. The Employee Payroll Profile is a separate record that must be created manually. Without it, the employee is invisible to payroll.

### "Salaried daily employee received full pay instead of prorated"

Check:
1. `PAYROLL_ATTENDANCE_INTEGRATION_ENABLED` is `true`.
2. The employee has **approved attendance records** (`is_approved = true`) for the pay period — unapproved records are excluded.
3. If the employee was hired mid-period, the base salary is automatically prorated — verify the `hire_date` is correct.

### "Tax calculation seems wrong"

Tax is calculated on the **annualised** gross pay:
1. Period gross × periods per year = annualised gross.
2. Tax bands are applied to the annualised amount.
3. Annual tax ÷ periods per year = period tax.

Verify the employee's `pay_frequency` is correct — it determines the periods-per-year multiplier.

### "The approval workflow is stuck"

Check the workflow status on the run detail page. If a step is pending, the assigned approver needs to act. If the approver is unavailable, a super_admin can bypass the workflow.

### "I dismissed the export download popup"

Click the 🕐 **history icon** in the top navigation bar to retrieve your exported file. See the [Roles & Permissions Guide](../roles-and-permissions/README.md) for details.

---

## 12. Glossary

| Term | Definition |
|------|-----------|
| **Payroll Run** | A single payroll processing instance for a specific period and pay schedule. |
| **Pay Schedule** | Defines the frequency, period, and currency for a group of employees. |
| **Payslip** | An individual employee's pay statement within a run. |
| **Pay Type** | How an employee is paid: `salaried_full`, `salaried_daily`, or `hourly`. |
| **Payroll Policy** | A recurring rule (tax, pension, insurance, etc.) applied every pay period. |
| **Policy Assignment** | Links a policy to specific organizations (company, department, etc.). |
| **Payroll Run Adjustment** | A one-time addition or deduction for a specific run. |
| **Employee Adjustment Profile** | A recurring, employee-specific override. |
| **Annualised Gross** | Period gross × periods per year — used for progressive tax calculation. |
| **Work Pattern** | Defines which days are working days (used for salaried daily calculations). |
| **Bank File** | A bank-ready payment file generated from an approved payroll run. |
