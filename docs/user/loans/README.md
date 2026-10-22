# Loans Module — User Guide

## Overview

The Loans module allows employees to request loans (salary advances, emergency loans, or other company loans) and administrators to configure loan types, manage approvals, track repayments, and integrate deductions with payroll.

---

## Table of Contents

1. [Roles & Permissions](#1-roles--permissions)
2. [Key Concepts](#2-key-concepts)
3. [For Employees](#3-for-employees)
4. [For Administrators](#4-for-administrators)
5. [Loan Approval Workflow](#5-loan-approval-workflow)
6. [Loan Repayments](#6-loan-repayments)
7. [Payroll Integration — How Deductions Work](#7-payroll-integration--how-deductions-work)
8. [Supporting Documents](#8-supporting-documents)
9. [Dashboards & Reports](#9-dashboards--reports)
10. [Troubleshooting](#10-troubleshooting)

---

## 1. Roles & Permissions

| Role | Typical Loan Access |
|------|---------------------|
| **Employee** | Self-service — apply for loans, view own loans and repayment status, upload supporting documents |
| **HR Manager / Company Admin** | Full oversight — view all loans, approve/reject loan requests, activate loans, manage loan types, view repayments |
| **Super Admin** | Complete access — all of the above plus configure loan types and eligibility rules |
| **Payroll Officer** | Read-only — view loans and repayments that affect payroll deductions |
| **Accountant** | Read-only — view loan portfolio for reconciliation |

Key permissions:

| Permission | What it gates |
|------------|---------------|
| `view_loan` | View loan list and details |
| `create_loan` | Apply for a loan |
| `edit_loan` | Edit loan details, activate loans, mark repayments as paid or waived |
| `delete_loan` | Delete draft loans |
| `view_loan_type` | View available loan types |
| `create_loan_type`, `edit_loan_type` | Configure loan types and eligibility rules |
| `view_loan_repayment` | View repayment schedule |

> See the [Roles & Permissions Guide](../roles-and-permissions/README.md) for the complete matrix.

---

## 2. Key Concepts

| Term | Meaning |
|------|---------|
| **Loan Type** | A category of loan (e.g., "Salary Advance", "Emergency Loan") with defined min/max amounts, interest rate, repayment terms, and eligibility rules. |
| **Loan Request** | An employee's application for a loan. Goes through a 5-step wizard: select type → enter amount & reason → choose repayment terms → upload documents → review & submit. |
| **Loan Number** | A unique identifier auto-generated for each loan (e.g., `LN-2026-00002`). |
| **EMI** (Equated Monthly Installment) | The fixed monthly payment amount calculated using the reducing balance method. `EMI = P × r × (1+r)^n / ((1+r)^n − 1)` |
| **Principal** | The original loan amount borrowed. |
| **Repayment Schedule** | The list of monthly installments (due date, EMI amount, principal/interest split) generated when a loan is activated. |
| **Activation** | The step after approval where the loan becomes `active`, funds are considered disbursed, and the repayment schedule is generated. |
| **Payroll Integration** | Active loans automatically create payroll deductions for the monthly EMI. Deductions appear as a `deduction` line item on the employee's payslip. |
| **Waive** | Marking a repayment as waived — no payment required, but the amount is still tracked. |
| **Draft** | A loan request saved but not yet submitted. Can be resumed later. |

### Loan Statuses

```
draft → pending_approval → approved → active → completed
                           ↓                   ↓
                       rejected           (fully repaid)
                           ↓
                       cancelled
```

| Status | Meaning |
|--------|---------|
| **Draft** | Saved but not yet submitted. Editable by the employee. |
| **Pending Approval** | Submitted and waiting for manager/admin review. |
| **Approved** | Approved but not yet activated (funds not transferred). |
| **Active** | Activated — repayment schedule generated, deductions begin on next payroll. |
| **Completed** | All repayments made — loan fully paid off. |
| **Rejected** | Declined by an approver. |
| **Cancelled** | Cancelled by employee or admin before approval. |

---

## 3. For Employees

### Applying for a Loan

1. Navigate to **My Loans** from the sidebar or employee portal.
2. Click the **Request** tab.
3. The 5-step wizard guides you through:

**Step 1 — Loan Type**: Select from available loan types. Each card shows the min/max amount, repayment period, and interest rate. Loan types greyed out or missing mean you don't meet the eligibility criteria (e.g., minimum tenure not met, maximum active loans reached).

**Step 2 — Amount & Reason**: Enter the loan amount (must be within the selected type's min/max range) and a brief reason for the loan.

**Step 3 — Repayment Terms**: Choose how many months to repay. The system calculates and shows:
- **Monthly EMI**: Your fixed monthly payment
- **Total Interest**: Total interest payable over the loan term
- **Total Repayable**: Principal + total interest

**Step 4 — Documents**: Optionally upload supporting documents (PDF, JPG, PNG, DOC, DOCX). Max 10MB per file, up to 10 files. Documents upload immediately and survive page refreshes.

**Step 5 — Review & Submit**: Review all details, agree to the loan terms, and click **Submit Request**.

> **Tip**: You can save progress as a draft at any time by clicking **Cancel → Keep Data**. Your draft loan will be available to resume later from the My Loans tab.

### Viewing Your Loans

1. Navigate to **My Loans** → **My Loans** tab.
2. The table shows all your loans with current status, amount, and repayment progress.
3. Click a loan row to see full details including:
   - Loan information (number, type, status, dates)
   - Financial details (principal, interest rate, EMI, total repayable, paid amount, balance)
   - Repayment schedule (installment list with due dates and status)
   - Supporting documents

### Understanding Your Repayments

- Repayments are due monthly starting one month after activation.
- Each repayment shows: due date, total amount, principal portion, interest portion, and status.
- Statuses:
  - **Pending**: Not yet due or not yet paid
  - **Paid**: Paid via payroll deduction or manual payment
  - **Overdue**: Past due date and not yet paid
  - **Waived**: Waved by an administrator

### Loan & Your Pay

- When your loan is **active**, the monthly EMI is automatically deducted from your salary through payroll.
- The deduction appears as "Loan Repayment — LN-XXXX-XXXXX" on your payslip.
- Each deduction pays one installment. The repayment is marked as paid when the payroll run is finalized.
- If there's no active payroll run covering your due date, the repayment stays pending until the next run.

---

## 4. For Administrators

### Setting Up Loan Types

1. Navigate to **Loans → Loan Types** (`/hr/loan-types`).
2. Click **New Loan Type** to create one.
3. Configure:

| Setting | Description |
|---------|-------------|
| **Name** | Display name (e.g., "Salary Advance", "Emergency Loan") |
| **Description** | Purpose and terms |
| **Min Amount / Max Amount** | The allowed loan amount range |
| **Default Interest Rate** | Annual percentage rate (e.g., 5.0 = 5% p.a.). Set to 0 for interest-free loans. |
| **Max Repayment Months** | The longest allowed repayment period |
| **Requires Approval** | If checked, loan requests go through the approval workflow |
| **Is Active** | If unchecked, the loan type won't appear for employees |
| **Eligibility Rules** (JSON) | Optional rules to gate access. Supported keys: `min_tenure_months`, `max_active_loans`, `max_salary_percent`. See [§4 — Eligibility Rules](#eligibility-rules). |

#### Eligibility Rules

Eligibility rules are configured as a JSON object on the loan type. Example:

```json
{
    "min_tenure_months": 6,
    "max_active_loans": 2,
    "max_salary_percent": 30
}
```

| Rule | Effect |
|------|--------|
| `min_tenure_months` | Employee must have been employed for at least this many months. |
| `max_active_loans` | Maximum number of active + pending_approval loans allowed. |
| `max_salary_percent` | Loan amount cannot exceed this percentage of monthly salary (e.g., 30 = max loan is 30% of monthly pay). |

### Managing Loans

1. Navigate to **Loans → Loans** (`/hr/loans`).
2. The table shows all loans with filters for status, employee, loan type.
3. **Row actions** (⋮ menu on each row):
   - **View**: Opens the loan detail page
   - **Edit**: Edit loan details (draft loans only)
   - **Activate**: Available on `approved` loans — generates the repayment schedule and sets status to `active`

### Activating a Loan

Activation is the step that transitions a loan from `approved` to `active`:

1. Find the approved loan in the Loans table.
2. Click ⋮ → **Activate**.
3. The system:
   - Sets status to `active` and records the start date
   - Generates the monthly repayment schedule (EMI amount, due dates, principal/interest split)
   - Loan repayments now appear in the **Loan Repayments** list

> **Important**: Loans do NOT appear on payslips until activated. Activation generates the repayment schedule, which is what the payroll integration reads.

### Managing Repayments

1. Navigate to **Loans → Loan Repayments** (`/hr/loan-repayments`).
2. Filter by status (pending, paid, overdue, waived) or loan.
3. **Row actions**:
   - **Mark as Paid**: For manual payments outside payroll. Sets status to `paid`, updates the loan balance.
   - **Waive**: Waives the repayment — no payment required. The loan balance is recalculated.

---

## 5. Loan Approval Workflow

Loans follow a configurable workflow similar to leave requests:

1. **Employee submits** the loan request → status: `pending_approval`
2. **Approver reviews** → can approve or reject
3. If **approved** → status changes to `approved`
4. Administrator **activates** the loan → status: `active`, repayment schedule generated
5. Payroll deductions begin on the next payroll run covering the first due date

Employees receive notifications at each stage:
- When the request is **submitted** — confirmation
- When **approved** — notification with loan details
- When **rejected** — with reason
- When **activated** — with repayment schedule summary

---

## 6. Loan Repayments

### How Repayments Are Generated

When a loan is activated, the system:

1. Calculates the EMI using the reducing balance formula.
2. Creates a `LoanRepayment` record for each month.
3. First due date is one month after activation.
4. Each repayment stores the EMI amount and the principal/interest split.

### Repayment Status Transitions

```
pending → paid          (via payroll or manual payment)
pending → overdue       (automatic — when past due date and not paid)
pending → waived        (admin action)
```

### Marking Repayments as Paid (Manual)

1. Navigate to **Loans → Loan Repayments**.
2. Find the pending or overdue repayment.
3. Click ⋮ → **Mark as Paid**.
4. The system updates:
   - Repayment status → `paid`, with `paid_date` and `payment_method = 'manual'`
   - Loan `paid_amount` and `balance` are recalculated
   - If fully paid, loan status → `completed`

### Waiving Repayments

1. Navigate to **Loans → Loan Repayments**.
2. Find the pending repayment.
3. Click ⋮ → **Waive**.
4. The repayment is marked `waived` — no payment required. The loan balance is recalculated to exclude the waived amount.

---

## 7. Payroll Integration — How Deductions Work

### Automatic Deduction Flow

```
Loan activated → Repayment schedule generated
                        ↓
Payroll run created (covers due date)
                        ↓
ProcessPayrollRun job starts
                        ↓
createAdjustmentsForRun() scans for pending repayments
  due within the payroll period for active loans
                        ↓
PayrollRunAdjustment records created
  { type: 'deduction', amount: EMI, source: loan_repayment }
                        ↓
PayrollCalculator reads adjustments during payslip computation
                        ↓
Deduction appears on payslip:
  "Loan Repayment — LN-2026-00002   (₦100.08)"
                        ↓
Payroll run finalized → repayment marked as 'paid'
```

### Key Rules

- Only **active** loans are included (not draft, pending_approval, or approved).
- Only **pending** repayments with `due_date` within the payroll run's `period_start` to `period_end` are deducted.
- Each repayment is deducted only once (idempotency check prevents duplicates).
- The deduction amount equals the EMI (monthly installment).
- When the payroll run is finalized, the linked repayment is automatically marked as `paid` with `payment_method = 'payroll_deduction'`.

### Troubleshooting Missing Deductions

If a loan deduction doesn't appear on a payslip, check:

1. **Loan status**: Must be `active` (not just `approved`). Use the **Activate** action.
2. **Repayment schedule**: Must have been generated. Check the loan detail page → Repayment Schedule tab.
3. **Due date vs payroll period**: The repayment `due_date` must fall within the payroll run's `period_start` to `period_end`. If the due date is November 19 but the payroll covers October 1-31, the deduction won't appear — create a payroll run covering November instead.
4. **Employee payroll profile**: The employee must have an active payroll profile linked to the correct pay schedule.

---

## 8. Supporting Documents

Employees can upload supporting documents during the loan request wizard (Step 4) or from the loan detail page.

### Uploading Documents

- **Supported formats**: PDF, JPG, PNG, DOC, DOCX
- **Max size**: 10MB per file
- **Max files**: 10
- **Upload**: Drag-and-drop or click the upload zone. Files are uploaded immediately.
- **Persistence**: Uploaded documents survive page refreshes. They remain associated with the loan request through submission and approval.

### Viewing Documents

- On the loan detail page, scroll to the **Supporting Documents** section below the tabs.
- Click a file name to open the document preview modal.
- Click the download icon to save a copy.

---

## 9. Dashboards & Reports

### Loans Overview Dashboard

Navigate to `/hr/dashboard-loans-overview` for a portfolio snapshot:

- **Active Loans** — count of currently active loans
- **Pending Approval** — count awaiting review
- **Total Outstanding** — sum of all unpaid balances (with currency)
- **Overdue Repayments** — count of past-due installments
- **Recent Loan Requests** — latest 5 requests with employee, amount, and status
- **Loan Requests Trend** — monthly request volume over the last 6 months
- **Loans by Type** — distribution bar chart
- **Repayment Progress** — total repaid vs total repayable

### My Loans Hub (Employee)

Navigate to `/hr/my-loans` for the employee self-service portal:
- **Overview tab** — personalized dashboard with loan summary
- **My Loans tab** — table of all your loans
- **Request tab** — the loan application wizard

---

## 10. Troubleshooting

### I can't see a loan type I expect

- The loan type may be **inactive** (`is_active = false`) — ask an admin to check.
- You may not meet the **eligibility rules** (e.g., minimum tenure, max active loans exceeded, salary percentage cap). Check with HR.

### My loan is approved but not being deducted from payroll

- The loan must be **activated** by an administrator. `approved` ≠ `active`. Ask your admin to click **Activate** on the loan.
- The repayment `due_date` may fall outside the payroll period. The first due date is one month after activation.
- Ensure you have an active payroll profile linked to the correct pay schedule.

### I uploaded documents but they disappeared after refresh

This has been fixed. Uploaded documents now persist correctly. If you experience this issue, please report it — it may indicate a cache that needs clearing.

### A repayment shows as "overdue"

- Overdue means the due date has passed and the repayment hasn't been paid or waived.
- If the amount was deducted via payroll, the status will update when the payroll run is finalized.
- For manual payments, an admin can use **Mark as Paid**.

### The DataTable shows an ID number instead of a name

- If the Loans table shows a raw employee ID instead of a name, or a loan ID instead of a loan number — this is a configuration issue. Contact the system administrator.
- This has been fixed for employee names (shows full name) and loan numbers in the repayments table.
