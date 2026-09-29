# Payroll Policy & Adjustments – User Guide

This guide explains how **payroll policies**, **employee adjustments**, and **one-time adjustments** work in the system. It is written for **HR and Payroll Administrators** and requires no technical knowledge.

---

# 1. Overview

The payroll engine calculates each employee's payslip using a combination of:

- **Recurring rules (Policies)** that apply every pay period.
- **Employee-specific overrides (Recurring Adjustment Profiles)** that modify a policy or add a custom amount.
- **One-time Adjustments (Payroll Run Adjustments)** for bonuses, commissions, reimbursements, deductions, or corrections.

All three work together to produce the final:

- Gross Pay
- Deductions
- Taxes
- Net Pay

---

# 2. Policies (Recurring Rules)

Policies are the building blocks of payroll. They define:

- What is being added or deducted (e.g. Pension, Health Insurance, Bonus)
- How it is calculated (Fixed Amount, Percentage, or Tax Brackets)
- When it applies (Effective and Expiry Dates)
- Where it applies (Company, Location, Department, Shift, Employee Group, etc.)

---

## 2.1 Policy Types

| Type | Purpose | Example |
|-------|----------|---------|
| **Tax** | Progressive tax based on income brackets | Income Tax, Social Security |
| **Pension** | Employee and/or employer pension contributions | 5% Employee / 10% Employer |
| **Insurance** | Health, life, or other insurance premiums | Health Insurance Deduction |
| **Benefit** | Other recurring earnings or deductions | Car Allowance, Meal Vouchers |
| **Bonus** | Recurring bonus | 3% of Base Salary |
| **Commission** | Recurring commission | 2% of Base Salary |
| **Deduction** | Other recurring deductions | Loan Repayment, Union Dues |

---

## 2.2 Effect: Addition vs Subtraction

### Addition

The amount is **added** to the employee's gross pay.

Examples:

- Bonus
- Commission
- Car Allowance
- Reimbursement

### Subtraction

The amount is **deducted** from gross pay.

Examples:

- Pension
- Insurance
- Income Tax
- Loan Repayment

---

## 2.3 Calculation Methods

| Method | Description |
|---------|-------------|
| **Fixed** | A fixed amount every pay period (e.g. ₦10,000) |
| **Percentage** | Percentage of the employee's base salary (e.g. 5% of ₦100,000 = ₦5,000) |
| **Tax Brackets** | Progressive tax rates applied to annual income, then divided across the pay periods |


> **Note:** Percentage calculations are based on the employee's **Gross Pay** (which may include overtime for hourly employees). This ensures deductions are proportional to total earnings.

---


## 2.4 Pay Types & Attendance Integration

Employees can have one of three pay types, defined in their **Employee Position** record:

| Pay Type | Description | Gross Pay Calculation |
|----------|-------------|------------------------|
| **Salaried Full** | Fixed monthly salary. No attendance required. | Gross Pay = Base Salary (full amount). |
| **Salaried Daily** | Paid per day worked. Requires clock‑in on workdays. | Gross Pay = Daily Rate × Worked Days. Daily Rate = Base Salary / Number of Workdays in Period. |
| **Hourly** | Paid per hour worked. Overtime may apply. | Gross Pay = (Regular Hours × Hourly Rate) + (Overtime Hours × Overtime Rate) + (Double‑Time Hours × Double‑Time Rate). |

### Attendance Integration

The system can be configured to **use or ignore attendance data** for payroll calculations. This is controlled by a global setting (`PAYROLL_ATTENDANCE_INTEGRATION_ENABLED`).

- **Enabled (default):** Attendance records are used to calculate `salaried_daily` and `hourly` pay.  
- **Disabled:** All employees are treated as `salaried_full`, regardless of their assigned pay type. No attendance queries are executed.

> **Important:** Percentage‑based policies (e.g., Pension, Insurance) are calculated on the **Gross Pay** amount (which may include overtime for hourly employees). This ensures deductions are proportional to total earnings.

---



## 2.5 Who Does a Policy Apply To?

A policy may be assigned to one or more of the following:

- Company
- Location
- Department
- Shift
- Employee Group

Alternatively, a policy may be **Global**, meaning it applies to every employee (optionally filtered by Country and State).

> **Priority Rule**
>
> If an employee matches multiple assignments for the same policy, the assignment with the **highest priority** takes effect.

---

# 3. Parent Policies (Inheritance)

Policies may inherit settings from another policy.

This allows organisations to create a standard company-wide policy and customise it for specific regions or business units without duplicating everything.

---

## 3.1 How Inheritance Works

A child policy inherits every setting from its parent unless that field has been explicitly overridden.

### Fields that may be overridden

- Calculation Logic
- Effect
- Employer Ratio
- Is Statutory
- Country Code
- State Code
- Type
- Name

### Date Rules

Dates are merged automatically.

| Date | Rule |
|------|------|
| Effective Date | Uses the **later** of the Parent and Child dates |
| Expiry Date | Uses the **earlier** expiry date (or whichever exists) |

---

## 3.2 Example

### Parent Policy

**Health Insurance – Base**

- Employee: 2%
- Employer: 2%

### Child Policy

**Health Insurance – Company A**

Overrides:

- Employee: 3%
- Employer: 3%

### Result

- Employees in Company A receive **3% / 3%**
- Everyone else receives **2% / 2%**

---

# 4. Employee Adjustment Profiles (Recurring)

Employee Adjustment Profiles are recurring employee-specific payroll adjustments.

They can either be:

- Standalone adjustments
- Overrides of an existing payroll policy

---

## 4.1 Standalone Adjustment

Create a recurring earning or deduction that is **not linked** to a policy.

### Example

Monthly Car Allowance

- Fixed Amount
- ₦50,000
- Applies every payroll period

---

## 4.2 Policy Override (Source Policy)

Instead of creating a new earning or deduction, an adjustment profile may reference an existing **Source Policy**.

When linked to a Source Policy:

- The employee uses the custom value.
- The original policy calculation is ignored for that employee only.

### Example

Default Pension Policy

- Employee Contribution = 5%

Employee #123

- Source Policy = Pension
- Employee Contribution = 3%

### Result

Employee #123 contributes **3%** while every other employee contributes **5%**.

---

# 5. One-Time Adjustments (Per Payroll Run)

One-time adjustments apply only to a specific payroll run.

These are entered during:

> **Payroll Wizard → Step 2 → Adjustments**

They are stored in the **payroll_run_adjustments** table.

---

## 5.1 Adjustment Types

| Type | Effect | Example |
|------|--------|---------|
| **Bonus** | Added to Gross Pay | ₦100,000 Performance Bonus |
| **Commission** | Added to Gross Pay | ₦50,000 Sales Commission |
| **Reimbursement** | Added to Gross Pay | ₦20,000 Travel Expenses |
| **Correction** | May Increase or Decrease Pay | +₦5,000 Underpayment / -₦2,000 Overpayment |
| **Deduction** | Deducted from Gross Pay | ₦10,000 Loan Repayment |

---

## 5.2 How They Are Applied

For every payroll run:

1. The calculator loads all one-time adjustments.
2. Each adjustment becomes a separate payslip line item.
3. These adjustments do **not** modify payroll policies.
4. They are applied **on top of** all recurring calculations.

---

## 6. How Everything Fits Together

The payroll calculator processes each employee in the following order.

### Step 1 – Determine Gross Pay

- **Salaried Full** → Gross Pay = Base Salary.
- **Salaried Daily** → Gross Pay = Daily Rate × Worked Days (from attendance).
- **Hourly** → Gross Pay = (Regular Hours × Hourly Rate) + (Overtime / Double‑Time components).

If attendance integration is disabled, **all employees** use the Salaried Full logic.

---

## Step 2

Recurring Employee Adjustment Profiles

- Standalone Adjustments
- Policy Overrides

---

## Step 3

One-Time Payroll Run Adjustments

---

## Step 4

Assigned Policies and Global Policies

- Priority Resolution
- Parent Policy Inheritance

---

## Step 5

Proration

If a policy is active for only part of the pay period, the amount is prorated.

---

## Step 6

Totals

Gross Pay

```
Base Salary
+ Earnings
+ Benefits
+ Bonuses
+ Commissions
```

Net Pay

```
Gross Pay
− Deductions
− Taxes
```

---

# 7. Common Scenarios

## 7.1 New Hire with a Custom Pension Rate

### Default Policy

- Pension
- Employee = 5%
- Employer = 5%

### Employee Profile

- Source Policy = Pension
- Employee = 3%

### Result

Only that employee contributes **3%**.

Everyone else contributes **5%**.

---

## 7.2 One-Time Bonus

During:

**Payroll Wizard → Step 2**

Enter:

- Bonus
- Employee
- Amount

### Result

The bonus appears on that payroll run only.

---

## 7.3 Company-Specific Health Insurance

Policy

- Health Insurance
- Employee = ₦20,000
- Employer = ₦10,000

Assignment

- Company A

### Result

Only Company A employees receive the deduction.

---

## 7.4 Global Income Tax

Policy

- Income Tax
- Progressive Tax Brackets

Assignment

- None (Global)

Country Filter

- NG

### Result

Every employee in Nigeria receives the tax calculation.

---

## 7.5 Regional Pension Variation

Parent Policy

- Pension Base
- Employee = 5%
- Employer = 5%

Child Policy

- Pension Lagos
- Employee = 6%
- Employer = 6%
- State = Lagos

Assignment

- Lagos Department or Location

### Result

Employees in Lagos contribute **6%**.

Everyone else contributes **5%**.

---

# 8. Important Notes

- One-time adjustments are always applied **after** recurring calculations.
- Policies with effective or expiry dates inside the pay period are automatically **prorated**.
- Employee Adjustment Profiles linked to a Source Policy **completely replace** that policy for the employee.
- A policy is only considered **Global** when it has **no assignments**.

---

# 9. Troubleshooting

| Problem | Likely Cause | Solution |
|----------|--------------|----------|
| Policy not appearing on payslip | Policy inactive or assignment incorrect | Verify the policy is active and correctly assigned |
| Employee not receiving a policy | Employee does not match assignment criteria | Check Company, Department, Location, Shift, or Employee Group |
| One-time adjustment missing | Adjustment was entered but not saved | Click **Save & Continue** in Step 2 |
| Policy override not working | Adjustment profile inactive or expired | Verify Effective and Expiry Dates |
| Parent policy not inherited | Parent inactive or child overrides the field | Verify Parent Policy and overridden fields |
| **Hourly employee shows full salary despite no clock‑ins** | Attendance integration is disabled. | Enable attendance integration in Settings → Payroll. |
| **Salaried daily employee paid zero days** | No attendance records found for the period. | Verify attendance records exist and employee has clock‑ins. |
| **Pension deduction seems too high/low** | Percentage based on gross pay, not base salary. | Check if overtime is affecting gross pay. |
---

# 10. Glossary

| Term | Definition |
|------|------------|
| **Policy** | A recurring payroll rule that applies to one or more employees |
| **Assignment** | Linking a policy to a Company, Department, Location, Shift, or Employee Group |
| **Global Policy** | A policy with no assignments that applies to all employees (subject to Country/State filters) |
| **Priority** | Determines which assignment wins when multiple assignments apply |
| **Effective Date** | The date a policy or adjustment begins |
| **Expiry Date** | The date a policy or adjustment ends |
| **Proration** | Calculating a proportional amount for a partial pay period |
| **Parent Policy** | A policy whose settings are inherited by another policy |

---

# Summary

The payroll engine combines:

1. **Payroll Policies**
2. **Employee Adjustment Profiles**
3. **One-Time Payroll Adjustments**

to calculate each employee's final payslip accurately and consistently.

By understanding how these three components interact, Payroll and HR Administrators can confidently configure payroll for any business scenario.
