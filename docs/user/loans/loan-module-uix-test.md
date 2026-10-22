# User Acceptance Testing (UAT) Guide
## Loans Module — Phases 1–6

**Purpose:** Verify the Loans module across administration, employee self-service, approval workflows, repayments, payroll integration, and scheduled processing.

## Table of Contents

1. [Prerequisites](#1-prerequisites)
2. [Admin Testing](#2-admin-testing)
   - [Loan Type Management](#21-loan-type-management-phase-1)
   - [Loan CRUD and Approval Workflow](#22-loan-crud-and-approval-workflow-phases-1-and-3)
   - [Repayment Management](#23-repayment-management-phase-4)
   - [Dashboard Overview](#24-dashboard-overview-phase-6)
3. [Employee Self-Service Testing](#3-employee-self-service-testing)
   - [My Loans Hub](#31-my-loans-hub-phase-2)
   - [Loan Request Wizard](#32-loan-request-wizard-phase-2)
   - [Loan Status Tracking](#33-loan-status-tracking-phase-2)
   - [Eligibility Rules](#34-eligibility-rules-phase-6)
4. [Payroll Integration Testing](#4-payroll-integration-testing-phase-5)
5. [Scheduled Command Testing](#5-scheduled-command-testing-phase-4)
6. [Quick Smoke Test](#6-quick-smoke-test)
7. [Test Completion Record](#7-test-completion-record)

---

## 1. Prerequisites

Complete this one-time setup before testing.

### 1.1 Run Database Migrations

```bash
php artisan migrate
```

### 1.2 Seed Permissions and Notification Templates

```bash
php artisan db:seed --class="App\\Modules\\Loans\\Database\\Seeders\\LoanRoleSeeder"
php artisan db:seed --class="App\\Modules\\Loans\\Database\\Seeders\\LoanNotificationTemplateSeeder"

```

### 1.3 Confirm Test Data

- [ ] At least one active Loan Type exists.
- [ ] Test employees exist and have active positions.
- [ ] Test accounts are available for the required roles: `admin`, `hr_manager`, `manager`, `finance_manager`, payroll administrator, and employee.

### 1.4 Clear Application Cache

```bash
php artisan config:clear && php artisan cache:clear
```

---

## 2. Admin Testing

**Context:** `/hr/loans`

### 2.1 Loan Type Management (Phase 1)

**URL:** `/hr/loan-types`

| # | Test Action | Expected Result | Pass |
|---:|---|---|:---:|
| 1 | Log in as `hr_manager` or `admin` and open `/hr/dashboard-loans-overview`. | Dashboard displays four metric cards and a recent loans table. | [ ] |
| 2 | Select **Loan Types** in the sidebar. | DataTable lists existing loan types or displays an empty state. | [ ] |
| 3 | Click **Add**. | Full-page form displays Name, Description, Min/Max Amount, Interest Rate, Max Months, Requires Approval, and Active fields. | [ ] |
| 4 | Create **Salary Advance**: Min = 100, Max = 5000, Rate = 0%, Months = 6, Active = Yes. | Record appears in the table with a success alert. | [ ] |
| 5 | Create **Emergency Loan**: Min = 500, Max = 20000, Rate = 8%, Months = 24, Active = Yes. | Record appears in the table. | [ ] |
| 6 | Edit a loan type. | Form opens with existing values and allows modifications. | [ ] |
| 7 | Delete a loan type and confirm. | Record is soft-deleted and appears in the trash view. | [ ] |
| 8 | Switch to List view using the top-right toggle. | Card-based view displays the name, description, and active badge. | [ ] |

### 2.2 Loan CRUD and Approval Workflow (Phases 1 and 3)

**URL:** `/hr/loans`  
**Loan detail URL:** `/loans/{id}`

| # | Test Action | Expected Result | Pass |
|---:|---|---|:---:|
| 1 | Select **All Loans** in the sidebar. | DataTable lists all loans. | [ ] |
| 2 | Click **Add** and create a loan by selecting an employee and loan type, entering amount = 2000, and selecting 3 months. | Loan is created with status **Draft** and an auto-generated number such as `LN-2026-XXXXX`. | [ ] |
| 3 | Click **Submit for Approval** on the draft loan. | Status changes to **Pending Approval**. `WorkflowEngine.start()` creates a three-step workflow: Manager → HR → Finance. | [ ] |
| 4 | Click **Show** (eye icon) on the loan. | Loan detail page displays three tabs: Loan Details, Approval Workflow, and Repayment Schedule. | [ ] |
| 5 | Open the **Approval Workflow** tab. | Approval panel displays Approve/Reject buttons and the workflow timeline. | [ ] |
| 6 | Log in as a user with the manager role and open the loan. | Manager sees the Approve and Reject buttons. | [ ] |
| 7 | Approve Step 1 and add the comment `Approved by manager`. | Workflow advances to Step 2 (HR Review), and the timeline records the action. | [ ] |
| 8 | Log in as `hr_manager` and approve Step 2. | Workflow advances to Step 3 (Finance Approval). | [ ] |
| 9 | Log in as `finance_manager` and approve Step 3. | Workflow completes, loan status becomes **Approved**, and the approval date is set. | [ ] |
| 10 | As an admin, click **Mark as Active** (play icon) on the approved loan. | Status changes to **Active**. `LoanRepaymentService.generateSchedule()` generates the repayment installments. | [ ] |
| 11 | Open the active loan and select **Repayment Schedule**. | Full amortization table displays due dates, amounts, principal/interest split, and status badges. | [ ] |

### 2.3 Repayment Management (Phase 4)

**URL:** `/hr/loan-repayments`

| # | Test Action | Expected Result | Pass |
|---:|---|---|:---:|
| 1 | Select **Repayments** in the sidebar. | DataTable lists repayments across all loans. | [ ] |
| 2 | Filter repayments by **Pending** status. | Only pending repayments are displayed. | [ ] |
| 3 | Click **Mark as Paid** on a pending repayment. | Status becomes **Paid**, `paid_date` is set, and the loan balance updates. | [ ] |
| 4 | Click **Waive** on another pending repayment. | Status becomes **Waived**, and the loan balance is recalculated. | [ ] |
| 5 | Open the parent loan detail page at `/loans/{id}`. | Paid amount increases, balance decreases, and the progress bar updates. | [ ] |

### 2.4 Dashboard Overview (Phase 6)

**URL:** `/hr/dashboard-loans-overview`

| # | Test Action | Expected Result | Pass |
|---:|---|---|:---:|
| 1 | Open the Loans Overview dashboard. | Four metric cards appear: Active Loans, Pending Approval, Total Outstanding, and Overdue Repayments. The Recent Loans table is also displayed. | [ ] |
| 2 | Compare dashboard metrics with records in `/hr/loans`. | Dashboard figures are consistent with the underlying data. | [ ] |
| 3 | Click a loan number in the Recent Loans table. | The corresponding `/loans/{id}` detail page opens. | [ ] |

---

## 3. Employee Self-Service Testing

**Context:** `/hr/my-portal`

### 3.1 My Loans Hub (Phase 2)

| # | Test Action | Expected Result | Pass |
|---:|---|---|:---:|
| 1 | Log in as an employee whose user account is linked to an Employee record. Open `/hr/my-portal`. | **My Loans** appears in the sidebar, in position 6 after Company Policies. | [ ] |
| 2 | Click **My Loans**. | `/hr/my-loans` opens and displays an empty state with a **Request Your First Loan** CTA when no loans exist. | [ ] |
| 3 | Click **Request New Loan**. | Inline loan request form appears in a card with a five-step progress indicator. | [ ] |

### 3.2 Loan Request Wizard (Phase 2)

**URL:** `/hr/my-loans`

| # | Test Action | Expected Result | Pass |
|---:|---|---|:---:|
| 1 | Step 1: Select the Salary Advance loan type card. | Card highlights with a blue border and check badge. Next is enabled. | [ ] |
| 2 | Click **Next**. | Step 2 opens and an information alert displays the loan type limits. | [ ] |
| 3 | Step 2: Enter amount = 3000 and reason = `Medical emergency`. | Amount is validated in real time against the minimum and maximum limits. | [ ] |
| 4 | Enter an amount below the configured minimum and click **Next**. | Validation error indicates that the amount is below the minimum. | [ ] |
| 5 | Enter a valid amount and reason, then click **Next**. | Wizard advances to Step 3. | [ ] |
| 6 | Step 3: Select 3 months from the repayment dropdown. | EMI Summary displays Principal, Monthly EMI, Total Interest, and Total Repayable. | [ ] |
| 7 | Click **Next**. | Wizard advances to Step 4. | [ ] |
| 8 | Step 4: Upload a test PDF. | File appears in the selected-files list with a size badge. | [ ] |
| 9 | Click **Next** without uploading a document. | Wizard advances to Step 5 because documents are optional. | [ ] |
| 10 | Step 5: Review the summary. | Summary displays Loan Type, Amount, Period, Rate, EMI, Total Repayable, Reason, and Documents. | [ ] |
| 11 | Check **I confirm...** and click **Submit Request**. | Success message appears, form closes, and loan appears in **Pending & Under Review**. | [ ] |
| 12 | Inspect the submitted loan card. | Loan number, type, amount, term, reason, and request date match the submitted details. | [ ] |

**Important test-data note:** The original test case uses `100` as the below-minimum amount, but the example Salary Advance type has a minimum of `100`. To test rejection, enter an amount strictly below the configured minimum.

### 3.3 Loan Status Tracking (Phase 2)

| # | Test Action | Expected Result | Pass |
|---:|---|---|:---:|
| 1 | After an admin approves and activates a loan, refresh My Loans. | Loan moves from **Pending** to **Active**. | [ ] |
| 2 | Inspect the active loan card. | Card displays amount, monthly EMI, paid amount, balance, and repayment progress bar. | [ ] |
| 3 | Check the progress bar before any repayment is recorded. | Progress is initially 0%. | [ ] |
| 4 | Set the status filter to **Completed**. | Only completed loans are displayed. | [ ] |
| 5 | Set the filter to **All Loans**. | Loans across all statuses are displayed. | [ ] |

### 3.4 Eligibility Rules (Phase 6)

Configure the following eligibility rules on a loan type:

```json
{
  "min_tenure_months": 12,
  "max_active_loans": 1,
  "max_salary_percent": 30
}
```

| # | Test Action | Expected Result | Pass |
|---:|---|---|:---:|
| 1 | As admin, edit a loan type, enter the eligibility rules above, and save. | Rules are saved successfully. | [ ] |
| 2 | As an employee hired less than 12 months ago, attempt to request this loan type. | Step 1 blocks the request with an employment-tenure validation message. | [ ] |
| 3 | As an employee with an existing active loan, attempt to request another loan. | Step 1 blocks the request because the active-loan limit has been reached. | [ ] |
| 4 | As an otherwise eligible employee, enter a loan amount exceeding 30% of monthly salary. | Step 2 blocks the request with a salary-percentage validation message. | [ ] |

---

## 4. Payroll Integration Testing (Phase 5)

| # | Test Action | Expected Result | Pass |
|---:|---|---|:---:|
| 1 | Ensure an active loan has pending repayments due within the current pay period. Check `/hr/loan-repayments`. | A suitable pending repayment is available for testing. | [ ] |
| 2 | As a payroll admin, create and process a payroll run covering that period. | Payroll follows its normal processing workflow. | [ ] |
| 3 | After `calculation_status` becomes `completed`, inspect the integration result. | `PayrollRunLoanSyncListener` creates `PayrollRunAdjustment` records with type `deduction` and a label such as `Loan Repayment - LN-...`. | [ ] |
| 4 | Open `/payroll/payroll-run-adjustments` and filter by the payroll run. | Loan repayment deduction line items are present. | [ ] |
| 5 | Finalize the payroll run. | `finalized_at` is set. | [ ] |
| 6 | Verify the finalization listener result. | Repayments are marked as paid with `payment_method: payroll_deduction` and the related `payroll_run_id`. | [ ] |
| 7 | Check the employee's payslip. | Loan repayment appears as a deduction line item. | [ ] |
| 8 | As the employee, check My Loans. | Paid amount increases, balance decreases, and the progress bar updates. | [ ] |

---

## 5. Scheduled Command Testing (Phase 4)

### 5.1 Run the Command

```bash
php artisan loans:process-repayments
```

Expected output includes messages similar to:

```text
Marked X repayment(s) as overdue
Sent Y repayment reminder(s)
Sent Z overdue notification(s)
```

### 5.2 Verify the Results

| # | Test Action | Expected Result | Pass |
|---:|---|---|:---:|
| 1 | Run `php artisan loans:process-repayments`. | Command completes without errors and reports overdue repayments and notifications processed. | [ ] |
| 2 | Check repayments whose due dates have passed. | Eligible repayments change from **Pending** to **Overdue**. | [ ] |
| 3 | Check the notification bell for employees with overdue repayments. | **Overdue Loan Repayment** notifications appear. | [ ] |
| 4 | Check employees whose repayments are due within three days. | **Loan Repayment Due** reminder notifications appear. | [ ] |

---

## 6. Quick Smoke Test

Run this checklist after each deployment. The two-minute estimate assumes the environment and test accounts are already prepared.

- [ ] `/hr/dashboard-loans-overview` loads without errors and displays metrics.
- [ ] `/hr/loan-types` DataTable loads; create, edit, and delete actions work.
- [ ] `/hr/loans` DataTable loads; row actions match each loan's status.
- [ ] `/hr/loan-repayments` loads; **Mark as Paid** and **Waive** work.
- [ ] `/hr/my-loans` loads for an employee; **Request New Loan** opens the wizard.
- [ ] Loan request wizard supports all five steps, calculates EMI, and submits successfully.
- [ ] `/loans/{id}` loads with all three tabs; workflow panel renders for pending loans.
- [ ] `php artisan loans:process-repayments` runs without errors.

---

## 7. Test Completion Record

| Field | Details |
|---|---|
| Tester | |
| Test Date | |
| Environment / URL | |
| Build or Release | |
| Overall Result | Not Started / Passed / Failed / Blocked |
| Defects or Notes | |

### Result Definitions

- **Passed:** Actual result matches expected result.
- **Failed:** Actual result differs from expected result.
- **Blocked:** Testing cannot proceed because of an environment, access, or dependency issue.
- **Not tested:** Test case has not yet been executed.

---

**End of UAT Guide**
