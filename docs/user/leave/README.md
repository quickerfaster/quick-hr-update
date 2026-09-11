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
5. Add a **Reason** (optional).
6. Click **Save Draft** to save without submitting, or **Save & Continue** to submit.

### Understanding Your Balance

- The leave type dropdown shows your remaining balance (e.g., "Annual Leave (12 days balance left)")
- If a leave type doesn't appear, you may not have a balance allocated — contact HR
- "0 days balance left" means no balance record exists yet for this year

### Approval Process

- If the leave type **requires approval**, your request will be sent to your manager
- You'll receive a notification when your request is approved or rejected
- You can check the status of your requests under the **My Leaves** tab

### How Your Balance Is Affected

- When your leave is **approved**, the days are deducted from your balance
- If your leave type uses the **accrual model**, you can only request days you've already earned
- If your leave type uses the **lump-sum model**, your full annual balance is available immediately
