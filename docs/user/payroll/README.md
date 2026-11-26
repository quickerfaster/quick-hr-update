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
    - [3.2a Salary Components (BHT Breakdown)](#32a-salary-components-bht-breakdown-)
4. [Running Payroll — The Payroll Wizard](#4-running-payroll--the-payroll-wizard)
5. [Payroll Approval Workflow](#5-payroll-approval-workflow)
6. [Payslips & Reports](#6-payslips--reports)
7. [Policies & Adjustments](#7-policies--adjustments)
8. [How Payroll Is Calculated](#8-how-payroll-is-calculated)
    - [8.1 Salaried Full](#81-salaried-full)
    - [8.2 Salaried Daily](#82-salaried-daily)
    - [8.3 Hourly](#83-hourly)
    - [8.4 Tax Calculation (PAYE)](#84-tax-calculation-paye)
    - [8.5 Attendance Integration](#85-attendance-integration)
    - [8.6 Hire & Termination Proration](#86-hire--termination-proration)
    - [8.7 Processing Architecture](#87-processing-architecture)
    - [8.8 Zero Earnings Diagnostic Checklist](#88-zero-earnings-diagnostic-checklist)
9. [Multi-Company Payroll](#9-multi-company-payroll)
10. [Employee Self-Service](#10-employee-self-service)
11. [Troubleshooting & Error Recovery](#11-troubleshooting--error-recovery)
12. [Glossary](#12-glossary)
13. [Nigerian Statutory Compliance](#13-nigerian-statutory-compliance)
    - [13.1 Overview](#131-overview)
    - [13.2 PAYE Income Tax (2026 Bands)](#132-paye-income-tax-2026-bands)
    - [13.3 Pension (Contributory Pension Scheme)](#133-pension-contributory-pension-scheme)
    - [13.4 National Housing Fund (NHF)](#134-national-housing-fund-nhf)
    - [13.5 NSITF (Employees' Compensation)](#135-nsitf-employees-compensation)
    - [13.6 ITF (Industrial Training Fund)](#136-itf-industrial-training-fund)
    - [13.7 NHIS (National Health Insurance)](#137-nhis-national-health-insurance)
    - [13.8 Group Life Assurance](#138-group-life-assurance)
    - [13.9 Lagos State Development Levy](#139-lagos-state-development-levy)
    - [13.10 Remittance Deadlines & Penalties](#1310-remittance-deadlines--penalties)
    - [13.11 Year-to-Date (YTD) Tracking](#1311-year-to-date-ytd-tracking)
    - [13.12 Statutory Identifiers on Payslips](#1312-statutory-identifiers-on-payslips)
    - [13.13 Country-Specific Calculation Engine](#1313-country-specific-calculation-engine)

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

**Key fields on the payroll profile**:

| Field | Purpose | Required for Nigeria? |
|-------|---------|:---:|
| **Pay Schedule** | Links employee to a pay frequency and period | ✅ |
| **Bank Details** | Account name, number, bank code, sort code | ✅ (for payment) |
| **Payment Method** | `bank_transfer` (default), `cash`, `cheque` | ✅ |
| **Annual Rent** 🇳🇬 | Employee's total annual rent paid — used for PAYE rent relief (20% of rent, capped at ₦500,000). Leave at 0 if not claiming. | Optional |
| **Tax Identification Number (TIN)** 🇳🇬 | FIRS-issued Tax Identification Number | Recommended |
| **Pension RSA Number** 🇳🇬 | Retirement Savings Account PIN with the employee's PFA | Recommended |
| **NHF Number** 🇳🇬 | National Housing Fund registration number (FMBN) | If enrolled |
| **NHIS Number** 🇳🇬 | NHIS/HMO enrollee number | If enrolled |
| **Tax Filing Status** | e.g., `single`, `married`, `head_of_household` | Optional |
| **Extra Withholding** | Additional flat tax withholding per period | Optional |
| **Is Active** | Must be checked for employee to appear in payroll runs | ✅ |

> 🇳🇬 **Statutory identifiers** (TIN, RSA PIN, NHF Number, NHIS Number) are snapshotted onto each payslip at calculation time. This ensures historical payslips retain the correct values even if the profile is later updated. See [§13.12](#1312-statutory-identifiers-on-payslips).

> **Important**: An employee must meet ALL three conditions to appear in a payroll run:
> 1. An **active Employee Payroll Profile** linked to the correct pay schedule
> 2. An **active employment status** (`Active`) on their position record
> 3. The profile's **pay schedule** must match the payroll run's pay schedule
>
> If any of these is missing, the employee will be silently excluded from the run.

#### 3.2a Salary Components (BHT Breakdown) 🇳🇬

For Nigerian employees, the pensionable base is calculated from three salary components collectively called **BHT** — **Basic**, **Housing**, and **Transport**. These are configured per employee via the **Salary Components** editor on the employee payroll profile.

**Why this matters**: Under the Pension Reform Act 2014, employee (8%) and employer (10%) pension contributions are calculated on the BHT total, not just the base salary. If no components are configured, the system falls back to `base_salary`.

**Available component types** (Nigeria):

| Component Key | Label | Part of BHT? |
|--------------|-------|:---:|
| `basic` | Basic Salary | ✅ |
| `housing` | Housing Allowance | ✅ |
| `transport` | Transport Allowance | ✅ |
| `other_allowance` | Other Allowance | ❌ |

**How to configure**:

1. Navigate to **Payroll → Configuration → Employee Profiles** (`/payroll/employee-payroll-profiles`).
2. Edit an employee's profile.
3. Locate the **Salary Components** section (embedded [`SalaryComponentsEditor`](app/Modules/Payroll/Http/Livewire/Payroll/SalaryComponentsEditor.php) Livewire component).
4. Add rows for Basic, Housing, Transport, and any other allowances.
5. Enter the **monthly amount** for each component.
6. Click **Save**.

The editor shows live totals:
- **Total Gross**: Sum of all component amounts
- **Pensionable BHT**: Sum of Basic + Housing + Transport only

**Example**:

| Component | Monthly Amount |
|-----------|---------------|
| Basic Salary | ₦500,000 |
| Housing Allowance | ₦200,000 |
| Transport Allowance | ₦100,000 |
| Other Allowance | ₦50,000 |
| **Total Gross** | **₦850,000** |
| **Pensionable BHT** | **₦800,000** |

> **Pension calculation**: 8% × ₦800,000 = ₦64,000 (employee) and 10% × ₦800,000 = ₦80,000 (employer). The Other Allowance (₦50,000) is included in gross pay but excluded from the pensionable base.

> **Fallback**: If no salary components are configured, the system uses `base_salary` from the employee's position as the pensionable base. This ensures pension is always calculated even before components are set up.

> **Country-agnostic**: The component keys (`basic`, `housing`, `transport`) are generic. The grouping into "pensionable" is defined per country in [`config/quick_hr_payroll.php → component_groups`](app/Modules/Payroll/Config/quick_hr_payroll.php:87). For Nigeria, the pensionable group is `['basic', 'housing', 'transport']`.

### 3.3 Employee Positions (Pay Data)

The employee's **position** record (managed in the HR module at `/hr/employee-positions`) carries the pay-critical fields:

| Field | Used For |
|-------|----------|
| `base_salary` | The fixed period salary (all pay types). Always treated as the per-period amount regardless of `pay_frequency`. |
| `hourly_rate` | The hourly rate (hourly employees) |
| `pay_type` | `salaried_full`, `salaried_daily`, or `hourly` — determines how pay is calculated |
| `pay_frequency` | Monthly, Semi-monthly, Bi-weekly, Weekly, Daily — used **only** for tax annualisation, not for salary proration |

> ⚠️ **Critical for `salaried_daily` and `hourly` employees**: These pay types require **approved attendance records** (`is_approved = true`) to compute pay. Without approved attendance, gross pay will be **₦0.00**. See [§8.2](#82-salaried-daily), [§8.3](#83-hourly), and the [Attendance Guide](../attendance/README.md) for setup requirements.
>
> Also see: [`EmployeePosition`](app/Modules/Hr/Models/EmployeePosition.php) model for all position fields and their payroll impact.

### 3.4 Payroll Policies

Policies are recurring rules that apply every pay period. See [§7 — Policies & Adjustments](#7-policies--adjustments) for the complete guide.

> **🇳🇬 Nigerian companies**: The system ships with pre-configured statutory policies for Nigeria (PAYE tax, Pension, NHF, NSITF, ITF, NHIS, Group Life). These are seeded automatically and apply to all Nigerian companies. See [§13 — Nigerian Statutory Compliance](#13-nigerian-statutory-compliance) for the complete reference.

### 3.5 Country-Specific Calculation

The payroll engine uses a **country strategy pattern** to handle jurisdiction-specific rules:

| Country | Calculator | Key Differences |
|---------|-----------|----------------|
| **Nigeria (NG)** | [`NigeriaPayrollCalculator`](app/Modules/Payroll/Services/Payroll/Countries/NigeriaPayrollCalculator.php) | BHT pensionable base, minimum-wage exemption (₦70k/month), rent relief, pension/NHF tax relief |
| **Default** | [`DefaultCountryPayrollCalculator`](app/Modules/Payroll/Services/Payroll/Countries/DefaultCountryPayrollCalculator.php) | Base salary only, no exemptions, no reliefs |

The country is resolved from the employee's **location → country_code**. See [§13.13](#1313-country-specific-calculation-engine) for details.

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
| **Base Salary** | The period base salary. For `salaried_daily`, this is the full monthly/weekly rate (NOT prorated). For `salaried_full` with mid-period hire, this shows the prorated amount. |
| **Worked Days** | For `salaried_daily` only: approved attendance days counted (e.g., "2 / 27"). Shows how many days the employee attended out of the total workdays in the period. |
| **Gross Pay** | Total earnings (base + adjustments) |
| **Gross Pay YTD** | 🇳🇬 Cumulative gross pay for the tax year (Jan 1 – Dec 31) |
| **Income Tax (PAYE)** | Federal/national income tax |
| **Income Tax YTD** | 🇳🇬 Cumulative PAYE for the tax year |
| **Social Security Tax** | Social security / national insurance contribution |
| **Medicare Tax** | Medicare / national health insurance |
| **Pension (Employee)** | Employee pension contribution |
| **Pension (Employee) YTD** | 🇳🇬 Cumulative employee pension for the tax year |
| **Pension (Employer)** | Employer pension contribution |
| **Pension (Employer) YTD** | 🇳🇬 Cumulative employer pension for the tax year |
| **NHF YTD** | 🇳🇬 Cumulative National Housing Fund contribution for the tax year |
| **Health Insurance (Employee)** | Employee health insurance premium |
| **NHIS (Employee) YTD** | 🇳🇬 Cumulative employee NHIS for the tax year |
| **Health Insurance (Employer)** | Employer health insurance premium |
| **NHIS (Employer) YTD** | 🇳🇬 Cumulative employer NHIS for the tax year |
| **Other Earnings** | Benefits, bonuses, commissions |
| **Other Deductions** | Loan repayments, union dues, etc. |
| **Employer Contributions** | Total employer share of all policies |
| **Total Deductions** | Sum of all deductions |
| **Total Taxes** | Sum of all taxes |
| **Net Pay** | Take-home pay (gross − deductions − taxes) |
| **Net Pay YTD** | 🇳🇬 Cumulative net pay for the tax year |
| **Currency** | The pay currency |
| **TIN** | 🇳🇬 Tax Identification Number (snapshot from employee profile) |
| **RSA PIN** | 🇳🇬 Retirement Savings Account number with PFA (snapshot) |
| **NHF Number** | 🇳🇬 National Housing Fund registration number (snapshot) |
| **NHIS Number** | 🇳🇬 National Health Insurance Scheme enrollee number (snapshot) |

> 🇳🇬 **Nigerian payslips**: Year-to-date (YTD) totals and statutory identifiers are automatically included for Nigerian employees. YTD values are computed by summing all previous payslips in the current tax year (January 1 – December 31). Statutory identifiers are snapshotted from the employee's payroll profile at the time of calculation. See [§13 — Nigerian Statutory Compliance](#13-nigerian-statutory-compliance).

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

### 7.1a Nigerian Statutory Policies (Pre-Seeded)

Nigerian companies automatically receive these statutory policies. They are **global** (`country_code = 'NG'`) and apply to all Nigerian employees unless overridden by a company-specific policy.

| Policy | Type | Employee | Employer | Base | Notes |
|--------|------|----------|----------|------|-------|
| **Nigerian PAYE (Income Tax)** | tax | banded | — | gross_pay | 2026 NTA bands: 0%–25%. ₦800k 0% band replaces old CRA. |
| **Pension (CPS)** | pension | 8% | 10% | pensionable (BHT) | Basic + Housing + Transport. Falls back to base_salary if no components. |
| **National Housing Fund (NHF)** | deduction | 2.5% | 0% | base_salary | Optional for private sector. `is_statutory = false`. |
| **NSITF (Employees' Compensation)** | deduction | 0% | 1% | gross_pay | Employer-only. Annual contribution. |
| **ITF (Industrial Training Fund)** | deduction | 0% | 1% | gross_pay | Employer-only. **Eligibility**: ≥5 employees OR ≥₦50m turnover. |
| **NHIS (National Health Insurance)** | insurance | 5% | 10% | base_salary | Under NHIA Act 2022. |
| **Group Life Assurance** | insurance | 0% | configurable (default 1.5%) | gross_pay | Employer-funded. Rate from `PAYROLL_GROUP_LIFE_EMPLOYER_RATE`. |

> **🇳🇬 ITF Eligibility**: The ITF policy is automatically skipped for companies with fewer than 5 employees AND less than ₦50 million annual revenue. See [§13.6](#136-itf-industrial-training-fund).

### 7.1b Lagos State Development Levy

A state-specific levy for employees whose **location** has `state_code = 'NG-LA'`:

| Monthly Gross | Annual Levy | Per Period (Monthly) |
|---|---|---|
| ≤ ₦50,000 | ₦100 | ₦8.33 |
| > ₦50,000 | ₦200 | ₦16.67 |

The levy is seeded via [`LagosStateDevelopmentLevySeeder`](app/Modules/Payroll/Database/Seeders/LagosStateDevelopmentLevySeeder.php) and applies automatically to Lagos-based employees. It is collected by the employer and remitted to the Lagos State Internal Revenue Service (LIRS).

### 7.1c Remittance Deadlines & Penalties

Each statutory policy carries remittance compliance metadata:

| Policy | Remittance Due | Late Penalty |
|--------|---------------|--------------|
| **PAYE** | 10th of following month | 10% penalty + CBN MPR interest |
| **Pension** | Within 7 working days of salary | 2% monthly interest (PRA 2014 §11(6)) |
| **NHF** | Within 30 days | Regulatory sanctions under NHF Act |
| **NSITF** | Annual (1st January) | Penalties under ECA 2010 |
| **ITF** | Annual | 5% penalty under ITF Act |
| **NHIS** | Within 30 days | Regulatory sanctions under NHIA Act 2022 |
| **Group Life** | Per insurer agreement | Policy lapse risk |

These fields (`remittance_due_days`, `late_penalty_rate`, `late_penalty_description`) are stored on each policy record and can be viewed in the policy detail screen. They are informational — the system does not currently auto-calculate penalties.

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

**Work patterns** define which days are working days (e.g., Monday–Friday). The resolution chain is:

1. **Employee-specific** [`EmployeeWorkPattern`](app/Modules/Attendance/Models/EmployeeWorkPattern.php) assignment overlapping the pay period
2. **Default work pattern** (`is_default = true`, `is_active = true`) scoped to the employee's company
3. **Fallback**: Weekdays (Monday–Friday) if no work pattern exists

If no work pattern is assigned and no default exists, the system counts weekdays in the period.

**Attendance filtering — critical for correct pay**:

Only attendance records meeting **all** of these conditions are counted:

| Condition | Why It Matters |
|-----------|---------------|
| `is_approved = true` | **Hard requirement.** Unapproved records — even with clock-in/out data and hours — are completely excluded. If an employee has attendance records but none are approved, `worked_days = 0` → **₦0.00 gross pay**. |
| Date within the pay period | Records outside `[period_start, period_end]` are ignored |
| Not explicitly excluded | Records with `affects_payroll = false` (if the column exists) are skipped |

A day counts as "worked" if **any** of these gate conditions pass:

| Gate | What It Means |
|------|--------------|
| `net_hours > 0` | Employee clocked hours that day |
| `status` is NOT `'absent'` | Includes: `present`, `late`, `half_day`, `incomplete`, `early_departure`, `unscheduled`, `holiday`, `leave` |
| `is_paid_absence = true` | Paid leave or paid holiday — counts as a worked day even with `net_hours = 0` |

> **⚠️ Common pitfall**: An attendance record with `status = 'unscheduled'` and `needs_review = true` will still count as a worked day if approved — but `is_approved = false` blocks it entirely. See [§11 — Troubleshooting](#11-troubleshooting--error-recovery) for diagnosis steps.

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

### 8.4 Tax Calculation (PAYE — Progressive)

Tax is calculated on **annualised** gross pay using progressive bands, then de-annualised back to the pay period.

```
Period Gross = the base amount (base_salary or gross_pay, per policy)
Periods Per Year = based on pay_frequency (12, 24, 26, 52, or 260)
Annualised Gross = Period Gross × Periods Per Year

🇳🇬 Nigeria-specific steps (applied before bands):
1. Minimum-wage check: if annual gross ≤ ₦840,000 (₦70,000 × 12) → tax = 0
2. Tax reliefs applied to annual gross:
   a. Employee pension (8% of BHT) — tax-exempt under PRA 2014
   b. Employee NHF (2.5% of basic) — tax-exempt under NHF Act
   c. Rent relief (20% of annual rent, capped at ₦500,000)
   → Annual Taxable = Annual Gross − Pension − NHF − Rent Relief

Tax = sum over bands:
    if Annual Taxable > band_start:
        taxable = min(Annual Taxable, band_end) − band_start
        tax += taxable × band_rate

Period Tax = Annual Tax ÷ Periods Per Year
```

**🇳🇬 Nigeria 2026 PAYE Bands** (Nigeria Tax Act 2025, effective 1 January 2026):

| Band | Annual Income Range | Rate | Max Tax in Band |
|------|-------------------|------|-----------------|
| 1 | First ₦800,000 | 0% | ₦0 |
| 2 | Next ₦2,200,000 (≤ ₦3,000,000) | 15% | ₦330,000 |
| 3 | Next ₦9,000,000 (≤ ₦12,000,000) | 18% | ₦1,620,000 |
| 4 | Next ₦13,000,000 (≤ ₦25,000,000) | 21% | ₦2,730,000 |
| 5 | Next ₦25,000,000 (≤ ₦50,000,000) | 23% | ₦5,750,000 |
| 6 | Above ₦50,000,000 | 25% | — |

> **Note**: The 2026 NTA removed the old Consolidated Relief Allowance (CRA). The ₦800,000 0% band replaces it. Rent relief (20% of annual rent, capped at ₦500,000) still applies.

**Worked Example — Monthly, ₦300,000 gross, full BHT, no rent**:

| Step | Calculation | Amount |
|------|------------|--------|
| Monthly gross | | ₦300,000 |
| Annual gross | ₦300,000 × 12 | ₦3,600,000 |
| Less: Employee pension | 8% × ₦800,000 BHT × 12 | −₦768,000 |
| Less: Employee NHF | 2.5% × ₦300,000 × 12 | −₦90,000 |
| Annual taxable | | ₦2,742,000 |
| Band 1 (0–800k @ 0%) | ₦800,000 × 0% | ₦0 |
| Band 2 (800k–2.742M @ 15%) | ₦1,942,000 × 15% | ₦291,300 |
| Annual tax | | ₦291,300 |
| **Monthly PAYE** | ₦291,300 ÷ 12 | **₦24,275** |

> Without pension/NHF relief, the monthly PAYE would be ₦36,500 — a difference of ₦12,225/month. The system automatically applies these reliefs for Nigerian employees.

### 8.5 Attendance Integration

The system can be configured to **use or ignore** attendance data via `PAYROLL_ATTENDANCE_INTEGRATION_ENABLED` (in [`quick_hr_payroll.php`](app/Modules/Payroll/Config/quick_hr_payroll.php:41)):

| Setting | Behavior | Pay Types Affected |
|---------|----------|-------------------|
| **Enabled** (`true`, default) | Attendance records drive pay calculation. Only records with `is_approved = true` are counted. Unapproved records → zero pay for attendance-based employees. | `salaried_daily`, `hourly` |
| **Disabled** (`false`) | All employees are treated as `salaried_full` — they receive their `base_salary` as-is, regardless of attendance. | All (forced to `salaried_full`) |

**Attendance data used per pay type**:

| Pay Type | Attendance Fields Used | Zero-Pay Risk If Missing |
|----------|----------------------|--------------------------|
| `salaried_daily` | `worked_days` (counted from status, net_hours, is_paid_absence) | 🔴 No approved records → `worked_days = 0` → ₦0.00 |
| `hourly` | `regular_hours`, `overtime_hours`, `double_time_hours` | 🔴 No approved records → all hours = 0 → ₦0.00 |
| `salaried_full` | None — attendance is irrelevant | 🟢 Not affected |

> **Cross-reference**: The attendance module's [`AttendanceCalculator`](app/Modules/Attendance/Services/AttendanceCalculator.php) computes `regular_hours`, `overtime_hours`, and `double_time_hours`. Overtime multipliers come from the employee's [`AttendancePolicy`](app/Modules/Attendance/Models/AttendancePolicy.php) via a 6-tier resolution chain (Employee → Shift → Department → Location → Company → System Default). See the [Attendance Guide](../attendance/README.md) for policy setup.

### 8.6 Hire & Termination Proration

Proration behaviour depends on the employee's **pay type**:

#### `salaried_full` — Calendar-Day Proration

For fixed-salary employees who join or leave mid-period, the base salary is automatically prorated based on calendar days:

```
Proration Factor = days employed ÷ total days in period
Prorated Base Salary = base_salary × Proration Factor
```

**Example — Salaried full, mid-period hire**:
- Period: October 1–31 (31 days)
- Hire date: October 24
- Days employed: 8 (Oct 24–31)
- Proration factor: 8 ÷ 31 = 0.258
- If `base_salary` = ₦1,000 → payslip shows `base_salary` = ₦258.06, gross = ₦258.06

#### `salaried_daily` and `hourly` — No Calendar-Day Proration

For attendance-based employees, the **worked days** (or **hours**) count already reflects the effective employment period — there are no attendance records before hire or after termination. Applying calendar-day proration on top of attendance would **double-penalise** the employee.

Instead, the full `base_salary` is used to compute the daily rate, and the employee is paid only for approved attendance days:

```
Daily Rate = base_salary ÷ Total Workdays in Period
Gross Pay = Daily Rate × Worked Days (attendance)
```

**Example — Salaried daily, mid-period hire (monthly)**:
- Period: October 1–31 (31 calendar days, 27 workdays Mon–Sat)
- Hire date: October 24
- `base_salary` = ₦1,000 (NOT prorated — this is the monthly rate)
- Daily rate: ₦1,000 ÷ 27 = ₦37.04
- Employee works 2 post-hire days → gross pay: 2 × ₦37.04 = ₦74.07
- Max possible (works all 7 post-hire workdays) → 7 × ₦37.04 = ₦259.26

**Example — Salaried daily, mid-period hire (weekly)**:
- Period: Monday–Sunday (7 calendar days, 6 workdays Mon–Sat)
- Hire date: Thursday (day 4 of the week)
- `base_salary` = ₦250 (NOT prorated — this is the weekly rate)
- Daily rate: ₦250 ÷ 6 = ₦41.67
- Works Thu and Fri → gross pay: 2 × ₦41.67 = ₦83.33
- The employee cannot be paid for Mon–Wed because no attendance records exist before hire

**Example — Salaried daily, mid-period hire (bi-weekly)**:
- Period: 14 days, ~12 workdays Mon–Sat
- Hire date: Day 9 of the period
- `base_salary` = ₦500 (NOT prorated — this is the bi-weekly rate)
- Daily rate: ₦500 ÷ 12 = ₦41.67
- Works 3 post-hire days → gross pay: 3 × ₦41.67 = ₦125.00

**Example — Salaried daily, mid-period hire (semi-monthly)**:
- Period: 15 days, ~13 workdays Mon–Sat
- Hire date: Day 10 of the period
- `base_salary` = ₦500 (NOT prorated — this is the semi-monthly rate)
- Daily rate: ₦500 ÷ 13 = ₦38.46
- Works 3 post-hire days → gross pay: 3 × ₦38.46 = ₦115.38

> **Key insight**: `base_salary` is always the **per-period** rate set by HR for the employee's pay frequency. A monthly worker has a monthly `base_salary`, a weekly worker has a weekly `base_salary`. The calculator does NOT multiply or divide by frequency — it uses `base_salary` as-is for the payroll run's period.
>
> The payslip shows the full `base_salary` (e.g., ₦1,000 monthly or ₦250 weekly) for `salaried_daily` — the effective pay reduction comes from the day count, not a salary proration.

- **Mid-period terminations**: Pay stops at the termination date because no approved attendance records exist after that date.
- **Salaried full employees**: Calendar-day proration applies as shown above.
- **Hourly employees**: Same as `salaried_daily` — no proration; pay = hours × rate.

> **Terminated employees** appear in payroll runs for their final period, ensuring statutory final-pay compliance.

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

### 8.8 Zero Earnings Diagnostic Checklist

If an employee's payslip shows **₦0.00 gross pay**, work through this checklist in order:

#### Step 1: Identify the Pay Type

Check the employee's position at **HR → Employee Positions** → `pay_type` field.

| Pay Type | Zero-Pay Causes | Skip To |
|----------|----------------|---------|
| `salaried_full` | `base_salary = 0`, proration reduced pay to 0 | Step 5 |
| `salaried_daily` | No approved attendance → `worked_days = 0` | Step 2 |
| `hourly` | No approved attendance → all hours = 0 | Step 2 |

#### Step 2: Check Attendance Approval Status

Navigate to **Attendance → Attendance Records**. Filter by the employee and pay period.

| Check | What to Look For | If Missing |
|-------|-----------------|-----------|
| **Any records exist?** | At least one record in the pay period | Employee never clocked in — no pay possible |
| **`is_approved` = true?** | Approved column shows "Yes" or checkbox is checked | 🔴 **This is the #1 cause of ₦0.00.** Unapproved records are completely excluded from payroll. Approve the records. |
| **`needs_review` = false?** | Review flag is cleared | 🟡 Review-flagged records may indicate clock-in issues (open sessions, violations). Resolve and approve. |

> **Most common cause**: An employee has clock-in data but the attendance record was never approved. The [`getAttendanceSummary()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:138) method requires `is_approved = true`. Without it, `worked_days = 0` and gross pay = ₦0.00.

#### Step 3: Check Attendance Record Quality (if approved)

Even approved records can produce low/zero pay if hours are missing:

| Field | Healthy Value | Problem Value |
|-------|--------------|---------------|
| `net_hours` | > 0 (e.g., 8.0) | 0.00 — employee clocked in but no payable hours computed |
| `status` | `present`, `late`, `half_day` | `absent`, `unscheduled` — may indicate incomplete clock-in/out pairing |
| `regular_hours` | > 0 for hourly employees | 0.00 — the [`AttendanceCalculator`](app/Modules/Attendance/Services/AttendanceCalculator.php) didn't compute regular hours |

For `salaried_daily`, a day counts as worked if **any** of:
- `net_hours > 0`
- `status` is not `'absent'`
- `is_paid_absence = true`

For `hourly`, the employee needs non-zero `regular_hours`, `overtime_hours`, or `double_time_hours`.

#### Step 4: Verify Work Pattern (salaried_daily only)

The daily rate depends on the work pattern:

```
Daily Rate = base_salary ÷ Total Workdays in Period
```

> **Note**: `base_salary` is the **full period salary** — it is NOT prorated for `salaried_daily`. Days before hire or after termination naturally have no attendance records and contribute ₦0.00. Proration only applies to `salaried_full` (see [§8.6](#86-hire--termination-proration)).

- If no [`EmployeeWorkPattern`](app/Modules/Attendance/Models/EmployeeWorkPattern.php) is assigned, the system uses the company's default work pattern
- If no default exists, it counts weekdays (Mon–Fri)
- Verify `applicable_days` on the work pattern covers the expected working days
- For mid-period hires, the daily rate uses the **full period's** workdays as divisor (e.g., 27 for October), not just the post-hire days. The employee's pay is `dailyRate × workedDays` — if they were hired late, they simply have fewer possible `worked_days`

#### Step 5: Check Proration

Proration behaviour depends on pay type:

- **`salaried_full`**: Calendar-day proration applies. A late-month hire gets a prorated `base_salary` on the payslip (e.g., ₦1,000 → ₦258.06 for Oct 24 hire).
- **`salaried_daily` and `hourly`**: No calendar-day proration. The `base_salary` on the payslip is the full amount (e.g., ₦1,000). The effective pay is the daily rate × worked days — days before hire naturally have no attendance records and contribute ₦0.00.

- Verify the employee's `hire_date` is correct at **HR → Employees**
- For `salaried_daily`, a late-month hire's pay is lower because they can only work fewer days (e.g., 7 post-hire workdays out of 27 in October), not because the salary value is prorated

#### Step 6: Verify Payroll Profile

The employee must have an active profile linked to the correct pay schedule:

1. Go to **Payroll → Configuration → Employee Profiles**
2. Confirm the employee has a profile with `is_active = 1`
3. Confirm the profile's `pay_schedule_id` matches the payroll run's pay schedule

> ⚠️ **Pay schedule mismatch**: If the employee's profile uses Pay Schedule A but the payroll run uses Pay Schedule B, the employee is silently excluded from the run.

#### Step 7: Check Attendance Integration Config

Verify `PAYROLL_ATTENDANCE_INTEGRATION_ENABLED` is `true` in your `.env` file. If set to `false`, all employees are treated as `salaried_full` — but if `base_salary = 0`, pay is still zero.

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

### "A salaried daily or hourly employee shows ₦0.00 gross pay"

🔴 **This is the most common payroll issue.** Follow the [§8.8 Zero Earnings Diagnostic Checklist](#88-zero-earnings-diagnostic-checklist) above for a step-by-step diagnosis.

**Quick checks (30 seconds)**:

1. **Go to Attendance → Attendance Records.** Filter by the employee and pay period. Is `is_approved` checked? **If no records are approved → ₦0.00.** This is the #1 cause.
2. **Check the employee's `pay_type`** at HR → Employee Positions. `salaried_daily` and `hourly` require approved attendance. `salaried_full` does not.
3. **Check `net_hours`** on approved records. If all approved records show `net_hours = 0.00`, the employee clocked in but no payable hours were computed (e.g., unpaired clock-in without clock-out, very short sessions, or `status = 'unscheduled'`). See the [Attendance Guide](../attendance/README.md) for fixing clock-in issues.

**Common root causes summary**:

| Cause | Fix |
|-------|-----|
| Attendance records `is_approved = false` | Approve the records in Attendance → Attendance Records |
| No attendance records exist for the period | Employee must clock in/out or have leave/holiday records |
| `status = 'unscheduled'` with `needs_review = true` | Resolve the review flag, ensure clock-in/out pairs are complete, then approve |
| `net_hours = 0` on all approved records | Clock sessions may be too short or unpaired — check clock events |
| Attendance integration disabled (`PAYROLL_ATTENDANCE_INTEGRATION_ENABLED=false`) | Enable it in `.env` or treat all employees as `salaried_full` |
| Pay schedule mismatch | Employee's payroll profile uses a different pay schedule than the run |

### "Salaried daily employee received full pay instead of attendance-based pay"

Check:
1. `PAYROLL_ATTENDANCE_INTEGRATION_ENABLED` is `true` — if disabled, all employees get full `base_salary` regardless of attendance.
2. The employee has **approved attendance records** (`is_approved = true`) for the pay period — unapproved records are excluded.
3. The employee's `pay_type` is actually `salaried_daily` (not `salaried_full`).
4. For mid-period hires: `base_salary` on the payslip shows the full period rate — this is correct. The actual pay is `dailyRate × workedDays`. The employee only gets paid for days with approved attendance. If `worked_days` is unexpectedly low, check that attendance records are approved.

### "Salaried full employee pay seems too low for mid-period hire"

For `salaried_full`, the payslip `base_salary` shows the **prorated** amount. A late-month hire sees a reduced `base_salary`. For example, hired Oct 24 → base_salary = ₦1,000 × (8/31) = ₦258.06. This is correct — the employee worked only 8 of 31 calendar days. Verify the `hire_date` at **HR → Employees**.

### "Tax calculation seems wrong"

Tax is calculated on the **annualised** gross pay:
1. Period gross × periods per year = annualised gross.
2. Tax bands are applied to the annualised amount.
3. Annual tax ÷ periods per year = period tax.

Verify the employee's `pay_frequency` is correct — it determines the periods-per-year multiplier. Note: `pay_frequency` only affects tax annualisation; it does NOT affect the base salary amount. A monthly employee and a weekly employee with the same `base_salary` receive the same gross pay in a monthly payroll run.

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
| **Work Pattern** | Defines which days are working days (Mon–Fri, Mon–Sat, etc.). Used for `salaried_daily` daily rate calculation. |
| **Proration** | Automatic pay reduction when an employee joins or leaves mid-period. Uses calendar days: `days employed ÷ total days in period`. |
| **Attendance Approval** | Attendance records must have `is_approved = true` to count toward payroll. Unapproved records → ₦0.00 for `salaried_daily` and `hourly` employees. |
| **Worked Day** | A day that counts toward `salaried_daily` pay. Defined as: `net_hours > 0` **or** `status ≠ 'absent'` **or** `is_paid_absence = true`. |
| **Bank File** | A bank-ready payment file generated from an approved payroll run. |
| **BHT** | 🇳🇬 Basic + Housing + Transport — the three salary components that form the pensionable base under Nigerian law. |
| **PAYE** | 🇳🇬 Pay As You Earn — Nigeria's progressive income tax on employment income. |
| **TIN** | 🇳🇬 Tax Identification Number — unique identifier issued by the Federal Inland Revenue Service (FIRS). |
| **RSA** | 🇳🇬 Retirement Savings Account — individual pension account managed by a PFA (Pension Fund Administrator). |
| **NHF** | 🇳🇬 National Housing Fund — 2.5% employee contribution to the Federal Mortgage Bank of Nigeria (optional for private sector). |
| **NHIS** | 🇳🇬 National Health Insurance Scheme — contributory health insurance under the NHIA Act 2022. |
| **NSITF** | 🇳🇬 Nigeria Social Insurance Trust Fund — employer-only 1% contribution under the Employees' Compensation Act 2010. |
| **ITF** | 🇳🇬 Industrial Training Fund — employer-only 1% contribution (applies to employers with ≥5 employees or ≥₦50m turnover). |
| **YTD** | Year-to-Date — cumulative totals from January 1 to the current pay period within the tax year. |
| **CRA** | 🇳🇬 Consolidated Relief Allowance — the old tax relief mechanism replaced by the ₦800,000 0% band under the Nigeria Tax Act 2025. |
| **Country Calculator** | A strategy class that implements jurisdiction-specific payroll rules (pensionable base, tax exemptions, reliefs). |
| **Form A** | 🇳🇬 Annual Tax Return filed with FIRS showing each employee's total emoluments and PAYE deducted. Deferred to Reports module. |

---

## 13. Nigerian Statutory Compliance

### 13.1 Overview

The payroll module includes comprehensive support for Nigerian statutory deductions and contributions, compliant with:

- **Nigeria Tax Act 2025** (effective 1 January 2026) — PAYE income tax
- **Pension Reform Act 2014** — Contributory Pension Scheme (CPS)
- **National Housing Fund Act** — NHF contributions
- **Employees' Compensation Act 2010** — NSITF
- **Industrial Training Fund Act** — ITF
- **NHIA Act 2022** — National Health Insurance Scheme

All statutory policies are pre-seeded via [`NigerianStatutoryPolicySeeder`](app/Modules/Payroll/Database/Seeders/NigerianStatutoryPolicySeeder.php) and [`NigerianPayeTaxPolicySeeder`](app/Modules/Payroll/Database/Seeders/NigerianPayeTaxPolicySeeder.php). They are **global** (`country_code = 'NG'`) and apply automatically to all Nigerian companies. Re-running the seeders updates existing policies in place (idempotent upsert).

### 13.2 PAYE Income Tax (2026 Bands)

**Legal basis**: Nigeria Tax Act 2025, effective 1 January 2026.

The 2026 NTA replaced the old Consolidated Relief Allowance (CRA) with a simpler ₦800,000 0% band. The tax bands are:

| Band | Annual Taxable Income | Rate |
|------|----------------------|------|
| 1 | First ₦800,000 | 0% |
| 2 | Next ₦2,200,000 (≤ ₦3,000,000) | 15% |
| 3 | Next ₦9,000,000 (≤ ₦12,000,000) | 18% |
| 4 | Next ₦13,000,000 (≤ ₦25,000,000) | 21% |
| 5 | Next ₦25,000,000 (≤ ₦50,000,000) | 23% |
| 6 | Above ₦50,000,000 | 25% |

**Tax reliefs applied before bands** (in order):
1. **Minimum-wage exemption**: Employees earning ≤ ₦70,000/month (₦840,000/year) pay zero PAYE.
2. **Employee pension contribution**: 8% of BHT is tax-exempt (PRA 2014).
3. **Employee NHF contribution**: 2.5% of basic salary is tax-exempt (NHF Act).
4. **Rent relief**: 20% of annual rent, capped at ₦500,000/year.

**Annualisation**: Tax is calculated on annualised income then de-annualised to the pay period. The periods-per-year multiplier depends on `pay_frequency`:

| Frequency | Periods/Year |
|-----------|-------------|
| Monthly | 12 |
| Semi-monthly | 24 |
| Bi-weekly | 26 |
| Weekly | 52 |

**Example**: See the worked example in [§8.4](#84-tax-calculation-paye--progressive).

> **📋 Form A (Annual Tax Return)**: Nigerian employers must file an annual return (Form A) with the FIRS showing each employee's total emoluments and tax deducted for the year. This report is **deferred** to the Reports module. All required data (annual gross, PAYE, pension, NHF, NHIS per employee) is already collected in the YTD columns on each payslip. See [Appendix B of the Gap Analysis](../../technical/payroll-gap-analysis-competitor-compliance.md#appendix-b-deferred-for-reports-module) for the complete deferred reports register.

### 13.3 Pension (Contributory Pension Scheme)

**Legal basis**: Pension Reform Act 2014.

| Item | Rate | Base |
|------|------|------|
| Employee contribution | 8% | Pensionable (BHT) |
| Employer contribution | 10% | Pensionable (BHT) |

**Pensionable base (BHT)**: The sum of three salary components — **Basic**, **Housing**, and **Transport**. These are configured per employee via [`EmployeeSalaryComponent`](app/Modules/Payroll/Models/EmployeeSalaryComponent.php) records. If no components exist, the system falls back to the employee's `base_salary`.

**Worked Example** — Employee with BHT components:

| Component | Monthly Amount |
|-----------|---------------|
| Basic Salary | ₦500,000 |
| Housing Allowance | ₦200,000 |
| Transport Allowance | ₦100,000 |
| **Pensionable Base (BHT)** | **₦800,000** |

| Deduction | Calculation | Monthly Amount |
|-----------|------------|----------------|
| Employee Pension (8%) | ₦800,000 × 8% | **₦64,000** |
| Employer Pension (10%) | ₦800,000 × 10% | **₦80,000** |

**Fallback example** — Employee without salary components:

| Scenario | Pensionable Base | Employee (8%) | Employer (10%) |
|----------|-----------------|---------------|----------------|
| No components, base_salary = ₦750,000 | ₦750,000 (fallback) | ₦60,000 | ₦75,000 |

**Remittance**: Employer must remit both shares to the employee's PFA within **7 working days** of salary payment. Late remittance attracts **2% monthly interest** (PRA 2014 §11(6)).

### 13.4 National Housing Fund (NHF)

**Legal basis**: National Housing Fund Act.

| Item | Rate | Base |
|------|------|------|
| Employee contribution | 2.5% | Basic salary |
| Employer contribution | 0% | — |

**Worked Example** — Employee with ₦300,000 monthly basic salary:

| Deduction | Calculation | Monthly Amount |
|-----------|------------|----------------|
| NHF (2.5%) | ₦300,000 × 2.5% | **₦7,500** |

**Important**: NHF is **mandatory for public-sector employees** but **optional for private-sector employees** (requires written consent). The seeded policy has `is_statutory = false` to reflect this. Companies can deactivate the NHF policy if they do not participate.

### 13.5 NSITF (Employees' Compensation)

**Legal basis**: Employees' Compensation Act 2010.

| Item | Rate | Base |
|------|------|------|
| Employee contribution | 0% | — |
| Employer contribution | 1% | Gross pay |

**Worked Example** — Employee with ₦300,000 monthly gross pay:

| Contribution | Calculation | Monthly Amount | Deducted from Employee? |
|-------------|------------|----------------|------------------------|
| NSITF (1%) | ₦300,000 × 1% | **₦3,000** | ❌ No — employer-only |

NSITF is an **employer-only** contribution. It appears on payslips as an informational employer contribution line item — it does **not** reduce the employee's net pay. The contribution is assessed on total monthly payroll and remitted annually.

### 13.6 ITF (Industrial Training Fund)

**Legal basis**: Industrial Training Fund Act.

| Item | Rate | Base |
|------|------|------|
| Employee contribution | 0% | — |
| Employer contribution | 1% | Gross pay |

**Eligibility threshold**: ITF applies only to employers meeting **either** of these criteria:
- **5 or more employees**, OR
- **₦50 million or more annual turnover**

**Worked Example** — Eligible employer (10 active employees), employee with ₦300,000 monthly gross:

| Contribution | Calculation | Monthly Amount | Deducted from Employee? |
|-------------|------------|----------------|------------------------|
| ITF (1%) | ₦300,000 × 1% | **₦3,000** | ❌ No — employer-only |

**Eligibility scenarios**:

| Company Profile | ITF Applied? |
|----------------|-------------|
| 10 employees, any turnover | ✅ Yes (meets employee threshold) |
| 3 employees, ₦60m turnover | ✅ Yes (meets turnover threshold) |
| 3 employees, ₦30m turnover | ❌ No (below both thresholds) |

The system automatically checks eligibility by counting active employees in the company. If the `companies` table has an `annual_revenue` column, it also checks turnover. Companies below both thresholds are excluded from ITF — the policy is silently skipped during payroll calculation.

### 13.7 NHIS (National Health Insurance)

**Legal basis**: NHIA Act 2022.

| Item | Rate | Base |
|------|------|------|
| Employee contribution | 5% | Basic salary |
| Employer contribution | 10% | Basic salary |

**Worked Example** — Employee with ₦300,000 monthly basic salary:

| Contribution | Calculation | Monthly Amount |
|-------------|------------|----------------|
| Employee NHIS (5%) | ₦300,000 × 5% | **₦15,000** |
| Employer NHIS (10%) | ₦300,000 × 10% | **₦30,000** |

Both shares appear on the payslip. The employer share is informational and does not reduce net pay.

### 13.8 Group Life Assurance

**Legal basis**: Pension Reform Act 2014 §4(5).

| Item | Rate | Base |
|------|------|------|
| Employee contribution | 0% | — |
| Employer contribution | Configurable (default 1.5%) | Gross pay |

**Worked Example** — Employee with ₦300,000 monthly gross, default 1.5% rate:

| Contribution | Calculation | Monthly Amount | Deducted from Employee? |
|-------------|------------|----------------|------------------------|
| Group Life (1.5%) | ₦300,000 × 1.5% | **₦4,500** | ❌ No — employer-only |

The premium rate is insurer-specific. The default rate is set via the `PAYROLL_GROUP_LIFE_EMPLOYER_RATE` environment variable (default: `1.5`). Companies can override this with a company-specific policy.

### 13.9 Lagos State Development Levy

**Legal basis**: Lagos State Local Government Administration Law.

A state-specific levy for employees whose **location** has `state_code = 'NG-LA'`:

| Monthly Gross Income | Annual Levy | Monthly Equivalent |
|---------------------|------------|-------------------|
| ≤ ₦50,000 | ₦100 | ₦8.33 |
| > ₦50,000 | ₦200 | ₦16.67 |

**Worked Examples**:

| Employee | Monthly Gross | Annual Levy | Monthly Deduction |
|----------|-------------|------------|-------------------|
| Junior staff | ₦45,000 | ₦100 | **₦8.33** |
| Senior staff | ₦300,000 | ₦200 | **₦16.67** |

The levy is seeded via [`LagosStateDevelopmentLevySeeder`](app/Modules/Payroll/Database/Seeders/LagosStateDevelopmentLevySeeder.php). It is collected by the employer and remitted to the Lagos State Internal Revenue Service (LIRS) alongside monthly PAYE.

> **Other states**: The policy framework supports additional state-level levies. To add a levy for another state, create a policy with the appropriate `state_code` and seed it following the Lagos pattern.

### 13.10 Remittance Deadlines & Penalties

Each statutory policy carries compliance metadata to help payroll officers track remittance obligations:

| Obligation | Remittance Due | Late Penalty |
|-----------|---------------|--------------|
| **PAYE** | 10th of following month | 10% of tax due + interest at CBN Monetary Policy Rate |
| **Pension** | Within 7 working days of salary payment | 2% monthly interest on unpaid contributions |
| **NHF** | Within 30 days of deduction | Regulatory sanctions under NHF Act |
| **NSITF** | Annual (by 1st January) | Penalties under Employees' Compensation Act 2010 |
| **ITF** | Annual | 5% penalty on unpaid assessment |
| **NHIS** | Within 30 days of deduction | Regulatory sanctions under NHIA Act 2022 |
| **Group Life** | Per insurer agreement | Policy lapse risk; PRA 2014 compliance required |

These fields (`remittance_due_days`, `late_penalty_rate`, `late_penalty_description`) are stored on each policy record. They are **informational** — the system does not currently auto-calculate penalties or send deadline reminders.

### 13.11 Year-to-Date (YTD) Tracking

Every Nigerian payslip includes **year-to-date (YTD)** cumulative totals for the tax year (January 1 – December 31):

| YTD Field | What It Tracks |
|-----------|---------------|
| `gross_pay_ytd` | Cumulative gross earnings |
| `income_tax_ytd` | Cumulative PAYE paid |
| `pension_employee_ytd` | Cumulative employee pension contributions |
| `pension_employer_ytd` | Cumulative employer pension contributions |
| `nhf_ytd` | Cumulative NHF contributions |
| `nhis_employee_ytd` | Cumulative employee NHIS contributions |
| `nhis_employer_ytd` | Cumulative employer NHIS contributions |
| `net_pay_ytd` | Cumulative net (take-home) pay |

YTD values are computed by summing all previous payslips in the same tax year (excluding the current payroll run) and adding the current period amounts. This gives employees and auditors a clear view of cumulative statutory compliance.

### 13.12 Statutory Identifiers on Payslips

Nigerian payslips include statutory identification numbers snapshotted from the employee's payroll profile at calculation time:

| Field | Source | Description |
|-------|--------|-------------|
| **TIN** | `employee_payroll_profiles.tax_identification_number` | Tax Identification Number (FIRS) |
| **RSA PIN** | `employee_payroll_profiles.pension_rsa_number` | Retirement Savings Account number with PFA |
| **NHF Number** | `employee_payroll_profiles.nhf_number` | National Housing Fund registration (FMBN) |
| **NHIS Number** | `employee_payroll_profiles.nhis_number` | NHIS/HMO enrollee number |

These are configured in the employee's payroll profile at **Payroll → Configuration → Employee Profiles**. They are snapshotted onto each payslip so that historical payslips retain the correct values even if the profile is later updated.

### 13.13 Country-Specific Calculation Engine

The payroll engine uses a **strategy pattern** to handle jurisdiction-specific rules without cluttering the core calculator:

```
PayrollCalculator
    → resolveCountryCalculator($position)
        → CountryPayrollCalculatorRegistry::resolve($countryCode)
            → NigeriaPayrollCalculator (NG)
            → DefaultCountryPayrollCalculator (fallback)
```

**Nigeria-specific logic** in [`NigeriaPayrollCalculator`](app/Modules/Payroll/Services/Payroll/Countries/NigeriaPayrollCalculator.php):

| Method | What It Does |
|--------|-------------|
| `resolvePensionableBase()` | Sums Basic + Housing + Transport components; falls back to `base_salary` |
| `isIncomeTaxExempt()` | Returns `true` if annual gross ≤ ₦840,000 (₦70k × 12 minimum wage) |
| `applyTaxReliefs()` | Applies rent relief (20% of rent, capped ₦500k), pension relief, and NHF relief |

**Adding a new country**: Register the calculator class in [`config/quick_hr_payroll.php → country_calculators`](app/Modules/Payroll/Config/quick_hr_payroll.php:76) and implement the [`CountryPayrollCalculator`](app/Modules/Payroll/Contracts/CountryPayrollCalculator.php) interface.

**Config-driven values** (in [`quick_hr_payroll.php`](app/Modules/Payroll/Config/quick_hr_payroll.php)):

| Config Key | Default | Description |
|-----------|---------|-------------|
| `country_tax_rules.NG.minimum_wage_exemption` | 70000 | Monthly minimum wage (₦) |
| `country_tax_rules.NG.rent_relief_rate` | 0.20 | Rent relief percentage |
| `country_tax_rules.NG.rent_relief_cap` | 500000 | Annual rent relief cap (₦) |
| `component_groups.NG.pensionable` | `['basic','housing','transport']` | BHT component keys |
| `group_life_employer_rate` | 1.5 | Default Group Life premium (%) |

### 13.14 Complete Payslip Example

This worked example ties together all Nigerian statutory deductions for a typical Lagos-based employee.

**Employee Profile**:
- Monthly base salary: ₦300,000
- BHT components: Basic ₦500,000 | Housing ₦200,000 | Transport ₦100,000
- Pensionable base (BHT): ₦800,000
- Location: Lagos (NG-LA)
- No annual rent
- Pay frequency: Monthly

**Step-by-Step Calculation**:

| # | Item | Calculation | Amount |
|---|------|------------|--------|
| 1 | **Gross Pay** | Base salary | **₦300,000.00** |
| | | | |
| 2 | **Employee Pension (8%)** | ₦800,000 × 8% | −₦64,000.00 |
| 3 | **NHF (2.5%)** | ₦300,000 × 2.5% | −₦7,500.00 |
| 4 | **NHIS Employee (5%)** | ₦300,000 × 5% | −₦15,000.00 |
| 5 | **Lagos Development Levy** | > ₦50k/month → ₦200/annum | −₦16.67 |
| | | | |
| 6 | **Annual Gross** | ₦300,000 × 12 | ₦3,600,000.00 |
| 7 | Less: Annual Pension | ₦64,000 × 12 | −₦768,000.00 |
| 8 | Less: Annual NHF | ₦7,500 × 12 | −₦90,000.00 |
| 9 | **Annual Taxable** | | **₦2,742,000.00** |
| | | | |
| 10 | Band 1 (0–800k @ 0%) | ₦800,000 × 0% | ₦0.00 |
| 11 | Band 2 (800k–2.742M @ 15%) | ₦1,942,000 × 15% | ₦291,300.00 |
| 12 | **Annual PAYE** | | **₦291,300.00** |
| 13 | **Monthly PAYE** | ₦291,300 ÷ 12 | **−₦24,275.00** |
| | | | |
| 14 | **Total Employee Deductions** | 64,000 + 7,500 + 15,000 + 16.67 + 24,275 | **−₦110,791.67** |
| 15 | **Net Pay (Take-Home)** | 300,000 − 110,791.67 | **₦189,208.33** |

**Employer Contributions** (informational — do not reduce net pay):

| # | Item | Calculation | Amount |
|---|------|------------|--------|
| E1 | Employer Pension (10%) | ₦800,000 × 10% | ₦80,000.00 |
| E2 | NSITF (1%) | ₦300,000 × 1% | ₦3,000.00 |
| E3 | ITF (1%) | ₦300,000 × 1% | ₦3,000.00 |
| E4 | NHIS Employer (10%) | ₦300,000 × 10% | ₦30,000.00 |
| E5 | Group Life (1.5%) | ₦300,000 × 1.5% | ₦4,500.00 |
| | **Total Employer Cost** | | **₦120,500.00** |

**Total Monthly Cost to Employer**: Gross Pay + Employer Contributions = ₦300,000 + ₦120,500 = **₦420,500.00**

**Payslip Summary** (as it appears to the employee):

| Field | Amount |
|-------|--------|
| Gross Pay | ₦300,000.00 |
| Gross Pay YTD | ₦300,000.00 |
| PAYE (Income Tax) | −₦24,275.00 |
| Income Tax YTD | ₦24,275.00 |
| Pension (Employee 8%) | −₦64,000.00 |
| Pension Employee YTD | ₦64,000.00 |
| NHF (2.5%) | −₦7,500.00 |
| NHF YTD | ₦7,500.00 |
| NHIS (Employee 5%) | −₦15,000.00 |
| NHIS Employee YTD | ₦15,000.00 |
| Lagos Development Levy | −₦16.67 |
| **Net Pay** | **₦189,208.33** |
| Net Pay YTD | ₦189,208.33 |
| | |
| Pension (Employer 10%) | ₦80,000.00 |
| NSITF (Employer 1%) | ₦3,000.00 |
| ITF (Employer 1%) | ₦3,000.00 |
| NHIS (Employer 10%) | ₦30,000.00 |
| Group Life (Employer 1.5%) | ₦4,500.00 |
| **Total Employer Contributions** | **₦120,500.00** |

> **YTD Note**: In this example, YTD equals the current period amounts because this is the first payslip of the tax year. For subsequent months, YTD values accumulate — e.g., the February payslip would show Gross Pay YTD = ₦600,000.00 (January + February).

> **Without pension/NHF tax relief**: If pension and NHF were not deducted before PAYE, the monthly tax would be ₦36,500.00 instead of ₦24,275.00 — the employee saves **₦12,225.00/month** through these tax-exempt contributions.
