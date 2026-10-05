# Leave Module — User Guide

## Overview

The Leave module allows employees to request time off and administrators to manage leave types, balances, and approvals.

---

## For Administrators (HR Role)

### Setting Up Leave Types

1. Navigate to **Leave → Leave Types** in the sidebar.
2. Click **New Leave Type** to create one.
3. Configure the following:

| Setting | Description |
|---------|-------------|
| **Name** | Display name (e.g., "Annual Leave", "Sick Leave") |
| **Code** | Short code used in config overrides (e.g., "annual", "sick") |
| **Deducts from Balance** | If checked, taking this leave reduces the employee's balance |
| **Requires Approval** | If checked, requests must be approved by a manager |
| **Max Days Per Request** | Maximum days an employee can request in a single application |
| **Active** | If unchecked, the leave type won't appear in the request form |

### Setting Up Leave Balances

Leave balances determine how many days each employee has available. There are two models:

#### Accrual Model (Monthly, Weekly, etc.)
- Employees earn leave days progressively over time
- Example: 1.67 days/month = 20 days/year
- Employees can only use days they've already accrued
- The `LeaveAccrualService` runs monthly to increment balances

#### Lump-Sum Model (Frequency: None)
- Full annual allowance is granted upfront
- Employees can take all their leave at once
- Configured via `config/leave.php`:
  ```php
  'annual_allowances' => [
      'default' => 20,    // default for all leave types
      'annual' => 20,     // override for "annual" leave type
      'sick' => 10,       // override for "sick" leave type
  ],
  ```

### Configuring Leave Balance Records

1. Navigate to **Leave → Leave Balances**.
2. Create a balance record for each employee + leave type + year combination.
3. Set the **Accrual Frequency**:
   - **Monthly/Weekly/Daily**: Balance accrues automatically
   - **None**: Full annual allowance granted upfront (from config)

---

## For Employees

### Applying for Leave

1. Navigate to **Leave Hub** from the sidebar or dashboard.
2. Click the **Apply** tab.
3. Select the **Leave Type** from the dropdown — available balance is shown in parentheses.
4. Choose **Start Date** and **End Date**.
5. Optionally check **Half Day** and select **AM** or **PM** for half-day leave.
6. Add a **Reason** (optional).
7. Click **Save Draft** to save without submitting, or **Save & Continue** to submit.

> **Half-day leave**: When half-day is selected, only half the standard work hours are credited for attendance and payroll purposes. Half-day leave still counts as a full worked day for salaried daily employees.

### Understanding Your Balance

- The leave type dropdown shows your remaining balance (e.g., "Annual Leave (12 days balance left)")
- If a leave type doesn't appear, you may not have a balance allocated — contact HR
- "0 days balance left" means no balance record exists yet for this year

### Approval Process

Leave requests follow a configurable multi-step approval workflow:

1. **Submission**: You submit your leave request from the Leave Hub
2. **Manager Review**: Your direct line manager (from your job info/employee position) reviews and approves or rejects the request
3. **HR Authorization**: A company admin or HR manager gives final authorization

You'll receive notifications at each stage:
- When your request is **submitted** — confirmation that it's in the pipeline
- When a step is **approved** — your request advances to the next stage
- When your request is **fully approved** — final confirmation
- If your request is **rejected** — with the reason

You can check the status of your requests under the **My Leaves** tab. The approval panel shows the current step, who needs to act, and the full activity timeline.

> **Note**: If you don't have a manager assigned in your job info, the request goes to HR managers for review instead.

### How Your Balance Is Affected

- When your leave is **approved**, the days are deducted from your balance
- If your leave type uses the **accrual model**, you can only request days you've already earned
- If your leave type uses the **lump-sum model**, your full annual balance is available immediately

### Leave & Your Pay

- **Paid leave** (`is_paid = true`): Your regular hours are credited for the leave day. Hourly employees receive their standard shift hours at their normal rate. Salaried daily employees count the day as worked.
- **Unpaid leave** (`is_paid = false`): The day counts as worked (no pay deduction for salaried daily), but no hours are credited for hourly employees.
- **Half-day leave**: Half the standard hours are credited for paid leave types.
- Leave days **skip weekends and company holidays** — only workdays are counted.
- Approved leave attendance records are auto-approved (`is_approved = true`) and immediately available for payroll.

### Leave & Your Pay

- **Paid leave** (`is_paid = true`): Your regular hours are credited for the leave day. Hourly employees receive their standard shift hours at their normal rate. Salaried daily employees count the day as worked.
- **Unpaid leave** (`is_paid = false`): The day counts as worked (no pay deduction for salaried daily), but no hours are credited for hourly employees.
- **Half-day leave**: Half the standard hours are credited for paid leave types.
- Leave days **skip weekends and company holidays** — only workdays are counted.
- Approved leave attendance records are auto-approved (`is_approved = true`) and immediately available for payroll.
