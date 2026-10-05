# HR Office Daily Operations — A Complete Walkthrough

> **Who this guide is for**: HR Managers, HR Officers, and Payroll Officers who manage employees, attendance, leave, and payroll every day. No technical knowledge needed.

---

## Table of Contents

1. [Welcome — Your Role in the Big Picture](#1-welcome--your-role-in-the-big-picture)
2. [Your Daily Morning Routine](#2-your-daily-morning-routine)
3. [Managing Leave Requests](#3-managing-leave-requests)
4. [Managing Attendance Records](#4-managing-attendance-records)
5. [Preparing for Payroll](#5-preparing-for-payroll)
6. [Running Payroll — Step by Step](#6-running-payroll--step-by-step)
7. [After Payroll — Finalizing and Reporting](#7-after-payroll--finalizing-and-reporting)
8. [The Monthly Payroll Cycle](#8-the-monthly-payroll-cycle)
9. [Common Scenarios and How to Handle Them](#9-common-scenarios-and-how-to-handle-them)
10. [Quick Reference — Your Daily Checklist](#10-quick-reference--your-daily-checklist)

---

## 1. Welcome — Your Role in the Big Picture

As someone working in the HR office, you are the person who keeps everything running smoothly. You make sure employees can clock in, their leave is handled properly, their attendance is accurate, and they get paid correctly and on time.

Here is how all the pieces fit together:

```
YOUR DAILY WORK
───────────────────────────────────────────────────────────────
Morning:    Check attendance → Fix exceptions → Approve records
Throughout: Review leave requests → Approve or reject
Before pay: Verify all attendance is approved → Check for issues
Pay day:    Run payroll → Review → Finalize → Distribute payslips
After pay:  Generate reports → Handle corrections → Prepare next cycle
───────────────────────────────────────────────────────────────

WHAT EMPLOYEES SEE
───────────────────────────────────────────────────────────────
They clock in/out → Their attendance is calculated
They apply for leave → You review and approve
Payroll runs → They receive their payslip
───────────────────────────────────────────────────────────────
```

Everything you do in the system connects to everything else. When you approve a leave request, it creates attendance records. When you approve attendance records, they become available for payroll. When you run payroll, it reads all those approved records and calculates everyone's pay.

Let's walk through each part of your day.

---

## 2. Your Daily Morning Routine

Start each day by checking what happened yesterday. This takes about 10–15 minutes and prevents problems from piling up.

### Step 1 — Check for Attendance Issues

Go to **Attendance → Attendances**. This is your main attendance dashboard.

**What to look for**:

1. Filter by **"Needs Review"** — these are records that have problems and need your attention.
2. Look for any records with status **"Incomplete"** — this usually means someone forgot to clock out.
3. Look for any records with status **"Absent"** — these are unplanned absences (no clock events and no approved leave).

**What to do for each**:

| If You See… | What It Means | What to Do |
|-------------|--------------|-----------|
| **Incomplete** | The employee may have forgotten to clock out, or worked fewer hours than expected | Open the record. Check if there's a missing clock-out. If yes, add the missing time. If the hours look correct, you can approve it as-is. |
| **Absent (unplanned)** | The employee didn't show up and has no approved leave | Check if they have a pending leave request you haven't approved yet. If they were supposed to be at work, follow your company's absence policy. |
| **Late** | The employee clocked in after the grace period | Review the lateness. If it's a pattern, you may want to speak with the employee. The hours are still counted normally. |
| **Unscheduled** | The employee worked on a day outside their normal work pattern | Check if the work was authorized. If yes, approve the record. |

### Step 2 — Approve Records That Look Correct

For records that look fine (no violations, correct hours):

1. Open the record.
2. Review the hours and any notes.
3. Click **Approve**.

> **Why this matters**: Only approved attendance records count for payroll. If you don't approve them, the employee's pay will be reduced — even if they worked a full day.

### Step 3 — Handle Missing Clock Events

If an employee tells you they forgot to clock in or out:

1. Go to **Attendance → Attendances** and find their record for that day.
2. Click **Adjust**.
3. Add the missing clock-in or clock-out time.
4. Provide a reason for the adjustment (for example, "Employee forgot to clock in — confirmed by manager").
5. Save. The system will recalculate the hours.
6. Review and approve the corrected record.

### What to Expect When…

| Situation | What Happens Next |
|-----------|------------------|
| **You approve a record** | The record is now available for payroll. The employee's hours will be counted when payroll runs. |
| **You adjust a record** | The system recalculates the hours based on your changes. The record may need re-approval. |
| **You leave a record unapproved** | The record will NOT count for payroll. The employee will not be paid for that day. |
| **Multiple employees have the same issue** | You can filter by status or date to see all affected records at once. Handle them one by one. |

---

## 3. Managing Leave Requests

Employees apply for leave through the Leave Hub. Their requests go through a configurable multi-step approval workflow before being finalized.

### Understanding the Approval Workflow

Leave requests follow this flow:

1. **Employee submits** the leave request from the Leave Hub
2. **Manager Review** — the employee's direct line manager reviews and approves or rejects
3. **HR Authorization** — a company admin or HR manager gives final authorization

You'll receive notifications when requests need your action. The approval panel on each request shows the current step, who needs to act, and the full activity timeline.

> **Note**: If an employee doesn't have a manager assigned in their job info, the manager review step goes to HR managers instead.

### Step 1 — Check for Pending Leave Requests

Go to **Leave → Leave Hub** and look at the pending requests. You can also check your **Notifications** (bell icon) for workflow requests awaiting your action.

You'll see:
- Who is requesting leave
- What type of leave (annual, sick, etc.)
- The dates they want
- How many days it will use
- Their remaining balance
- Which approval step it's at

### Step 2 — Review Each Request

Before approving, check:

1. **Do they have enough balance?** The system shows their remaining days next to the leave type.
2. **Are the dates reasonable?** Check for any conflicts — are too many people off at the same time?
3. **Is it the right leave type?** Make sure they selected the correct type (annual leave vs sick leave, for example).
4. **Is it a half-day?** If they checked "Half Day," they're only taking half the day.

### Step 3 — Approve or Reject

**To approve**:
1. Open the leave request detail.
2. Review the approval panel — it shows which step you're acting on.
3. Click **Approve** and optionally add a comment.
4. The request advances to the next step, or is fully approved if you're the final authorizer.

When fully approved, the system automatically:
- Reduces the employee's leave balance (if the leave type deducts from balance)
- Creates attendance records for each workday in the leave period
- Skips weekends and holidays automatically
- Marks the attendance records as approved

**To reject**:
1. Click **Reject** on the approval panel.
2. Add a reason so the employee knows why.
3. The workflow is terminated and the employee can edit and resubmit.

### What Happens Behind the Scenes When You Approve Leave

Let's say an employee requests annual leave from Monday, July 6 to Friday, July 10 (5 working days). Here's exactly what happens:

1. **The leave request status changes to "Approved."**
2. **The employee's balance is reduced by 5 days** (if the leave type deducts from balance).
3. **Five attendance records are created** — one for Monday, Tuesday, Wednesday, Thursday, and Friday — all with status "Leave."
4. **Each record is auto-approved** — you don't need to approve them separately.
5. **If the leave type is paid**, each record shows the employee's standard shift hours (usually 8). If unpaid, each record shows 0 hours.
6. **Weekends are skipped** — if the leave covered a weekend, those days are not counted.

### What to Do If…

| Situation | What to Do |
|-----------|-----------|
| **An employee doesn't have enough balance** | You can still approve if your company allows it. The balance will go negative. Or reject and ask them to adjust the dates. |
| **Two employees requested the same dates** | Check your company's policy on overlapping leave. You may need to reject one and ask them to choose different dates. |
| **The leave covers a holiday** | The system handles this automatically — the holiday day is skipped and doesn't count against their balance. |
| **An employee needs to cancel approved leave** | Find the approved request and cancel it. The system will restore their balance, remove the leave attendance records, and recalculate those days. |
| **A leave request is stuck on "Pending"** | The employee's manager may need to approve it first. Check the approval workflow for that leave type. |

---

## 4. Managing Attendance Records

Beyond your morning check, you'll need to manage attendance records throughout the pay period.

### Reviewing Records with Violations

Records flagged with **"Needs Review"** have one or more issues:

| Violation Type | What It Means | Should You Approve? |
|---------------|--------------|-------------------|
| Late arrival | Employee clocked in after the grace period | Usually yes — the hours are still correct. Note the lateness for performance tracking. |
| Early departure | Employee left before the grace window | Same as above — approve if the hours are correct. |
| Missed break | Employee didn't take a required break | Review your company policy. You may want to speak with the employee. |
| Incomplete hours | Employee worked less than 90% of expected hours | Check if there's a reason (left early with permission, etc.). Adjust if needed. |

### How to Review and Approve

1. Filter attendance records by **"Needs Review = Yes"**.
2. Open each record.
3. Look at the violations listed in the record details.
4. If everything looks correct despite the violations, click **Approve**.
5. If something needs to be fixed, create an adjustment first, then approve.

### Making Adjustments

Sometimes you need to fix an attendance record:

1. Open the attendance record.
2. Click **Adjust**.
3. You can:
   - Add or change clock-in and clock-out times
   - Add missing work sessions
   - Change the status manually (rarely needed)
4. Provide a reason for the adjustment.
5. Save. The system recalculates the hours.
6. Review and approve the corrected record.

> **Important**: Once a record is approved, you cannot recalculate it without first unapproving it. Recalculation resets the approval status, so you'll need to approve it again afterward.

### Bulk Actions

If you have many records to handle at once:

- **Filter by status** to see all records of a certain type (all "Incomplete" records, for example)
- **Filter by date range** to focus on a specific week or month
- **Filter by department** if you manage multiple teams

### What to Expect When…

| Situation | What Happens |
|-----------|-------------|
| **You approve a record with violations** | The record is available for payroll. The violations are still recorded for reporting purposes. |
| **You adjust clock times** | The system recalculates regular, overtime, and double-time hours based on the new times. |
| **You unapprove a record** | The record is removed from payroll consideration until you re-approve it. |
| **An employee worked overtime** | The overtime hours are automatically calculated and shown separately. No extra action is needed from you. |

---

## 5. Preparing for Payroll

Before you run payroll, take time to verify everything is ready. This prevents errors and saves you from having to make corrections later.

### The Pre-Payroll Checklist

Go through this checklist before every payroll run:

- [ ] **All attendance records for the period are approved.** Filter by the pay period dates and check for any unapproved records. Unapproved records will not be counted.
- [ ] **All leave requests for the period are processed.** Check for any pending leave requests that cover dates in the pay period. Approve or reject them.
- [ ] **All employees have active payroll profiles.** Go to Payroll → Employee Profiles and verify every employee who should be paid has a profile linked to the correct pay schedule. An employee without a payroll profile will not appear in the run — even if they have an active position.
- [ ] **New hires and terminations are up to date.** Check that any employees who joined or left during the period have correct dates. Their pay will be automatically prorated.
- [ ] **Employee positions are correct.** Verify that pay types, base salaries, and hourly rates are up to date for all employees.
- [ ] **Recurring adjustments are active.** Check Employee Adjustment Profiles — make sure any recurring bonuses or deductions are still valid.
- [ ] **One-time adjustments are ready.** If you need to add bonuses, commissions, reimbursements, or corrections for this run, prepare them now.

### How to Check Attendance Readiness

1. Go to **Attendance → Attendances**.
2. Filter by the pay period dates (for example, July 1 to July 31).
3. Look at the status column — are there any records that are not approved?
4. If you see unapproved records, approve them now.

### How to Check Leave Readiness

1. Go to **Leave → Leave Hub**.
2. Look for any requests with status "Pending" that fall within the pay period.
3. Approve or reject them before running payroll.

### What Happens If You Run Payroll Without Preparing

| If You Forget To… | What Happens |
|-------------------|-------------|
| Approve attendance records | Employees with unapproved records will have reduced pay — those days won't be counted |
| Process pending leave | Employees on leave won't have attendance records for those days — they'll be marked as absent |
| Update new hire dates | New employees may receive a full period's pay instead of prorated pay |
| Add one-time adjustments | Bonuses or deductions for this period will be missed |

---

## 6. Running Payroll — Step by Step

The Payroll Wizard guides you through creating and processing a payroll run in three steps.

### Step 1 — Create the Payroll Run

1. Go to **Payroll → Payroll Wizard**.
2. Select the **Pay Schedule** you want to run (for example, "Monthly Staff").
3. The system shows you how many employees are on this schedule.
4. Enter a **Payroll Title** (for example, "July 2026 — Monthly Staff").
5. Set the **Period Start** and **Period End** dates.
6. The system checks for any conflicts — for example, if a payroll run already exists for the same period.

**Multi-company mode**: If your organization has multiple companies, you can toggle "All Companies" to run payroll for everyone at once. Otherwise, select a specific company.

### Step 2 — Add One-Time Adjustments

This is where you add anything extra for this specific payroll run:

| Adjustment Type | When to Use It | Example |
|----------------|---------------|---------|
| **Bonus** | One-time bonus payment | "₦50,000 performance bonus for Q2" |
| **Commission** | Sales or performance commission | "5% commission on July sales" |
| **Reimbursement** | Expense reimbursement | "₦15,000 travel expenses" |
| **Deduction** | One-time deduction | "₦10,000 loan repayment" |
| **Correction** | Fix a mistake from a previous period | "−₦5,000 overpayment correction from June" |

You can add adjustments:
- **Per employee** — for individual bonuses or deductions
- **As a list** — add multiple adjustments at once

### Step 3 — Review and Process

1. The system calculates all payslips and shows you a preview.
2. Review the preview carefully:
   - **Per-employee breakdown**: Check gross pay, deductions, taxes, and net pay for each employee.
   - **Run totals**: Total gross pay, total deductions, total taxes, total cash required.
   - **Grouped views**: See totals by department, location, or company.
3. If everything looks correct, click **Finalize**.
4. If something needs to change, go back to Step 2 and adjust.

### What Happens During Calculation

When you click to process payroll, the system:

1. Goes through every active employee on the pay schedule.
2. For each employee, reads their approved attendance records for the period.
3. Calculates their gross pay based on their pay type:
   - **Salaried full**: Uses their base salary as-is (prorated if they joined or left mid-period)
   - **Salaried daily**: Counts their worked days and multiplies by their daily rate
   - **Hourly**: Adds up their regular, overtime, and double-time hours at their hourly rate
4. Applies all payroll policies (tax, pension, insurance, benefits, deductions).
5. Creates a payslip with line items showing every earning and deduction.
6. Tracks progress so you can see how far along the calculation is.

### After Processing

Once processing is complete:

1. Review the final numbers.
2. If everything is correct, the run enters the **approval workflow**.
3. The Payroll Officer reviews and confirms (Step 1 approval).
4. The HR Manager authorizes the run for payment (Step 2 approval).
5. After both approvals, the run can be marked as **Paid**.

### What to Do If…

| Situation | What to Do |
|-----------|-----------|
| **The calculation shows an error for an employee** | Check their attendance records and position data. Fix any issues, then recalculate. |
| **You need to add a bonus after processing** | Go back to Step 2, add the adjustment, and recalculate. |
| **An employee is missing from the run** | This is almost always a missing Employee Payroll Profile. Go to Payroll → Employee Profiles and check if the employee has a profile linked to the correct pay schedule. Also verify their employment status is "Active." |
| **The totals seem wrong** | Review the per-employee breakdown to find which employee has unexpected numbers. Check their attendance and position. |
| **You need to cancel the run** | You can cancel a draft run at any time. Once finalized, you cannot cancel — you'll need to process corrections in the next run. |

---

## 7. After Payroll — Finalizing and Reporting

### Finalizing the Run

Once a payroll run is finalized:

- **Payslips are locked** — they cannot be changed or recalculated.
- **Employees can view their payslips** through self-service.
- **Reports are available** for download and printing.

> **Important**: After finalization, the only way to fix a mistake is through a **correction adjustment** in the next payroll run. Always review carefully before finalizing.

### Generating Reports

From a finalized payroll run, you can generate:

| Report | What It Shows | Format |
|--------|--------------|--------|
| **Run Report** | Complete breakdown of all payslips | Web view, PDF, Excel |
| **Summary by Department** | Totals grouped by department | Web view, PDF |
| **Summary by Location** | Totals grouped by location | Web view, PDF |
| **Executive Summary** | High-level overview for management | Web view |
| **Bank File** | Bank-ready payment file for transfers | Downloadable file |

### Distributing Payslips

Employees can view and download their own payslips through **My Portal** or the payslips section. You don't need to distribute them manually.

### Handling Corrections

If you discover a mistake after finalization:

1. Do NOT try to recalculate the finalized run — the system won't allow it.
2. Create a **correction adjustment** in the next payroll run.
3. For overpayments, add a negative correction (deduction).
4. For underpayments, add a positive correction (bonus).
5. Add a note explaining what the correction is for.

---

## 8. The Monthly Payroll Cycle

Here is what a typical monthly payroll cycle looks like from start to finish:

### Week 1–3 (During the Pay Period)

| Day | What You Do |
|-----|------------|
| **Daily** | Check attendance for issues (incomplete records, unplanned absences) |
| **Daily** | Review and approve leave requests as they come in |
| **Weekly** | Review all unapproved attendance records and approve the ones that look correct |
| **As needed** | Handle employee requests for clock-in/out corrections |

### Week 4 (End of Period — Payroll Preparation)

| Day | What You Do |
|-----|------------|
| **2–3 days before payroll** | Run through the pre-payroll checklist (see Section 5) |
| **1 day before payroll** | Verify all attendance is approved, all leave is processed, all adjustments are ready |
| **Payroll day** | Run the Payroll Wizard, review, finalize |

### After Payroll

| Day | What You Do |
|-----|------------|
| **After finalization** | Generate reports, distribute to management |
| **After approval** | Mark the run as Paid, generate bank file |
| **Ongoing** | Handle any correction requests for the next cycle |

---

## 9. Common Scenarios and How to Handle Them

### "An employee says they were marked Absent but they were at work"

**What happened**: They probably forgot to clock in. The system saw no clock events and marked them as absent.

**What to do**:
1. Find their attendance record for that day.
2. Ask them what time they arrived and left.
3. Create an adjustment — add the missing clock-in and clock-out times.
4. Save. The system recalculates.
5. Approve the corrected record.

### "An employee's leave was approved but their attendance still shows Absent"

**What happened**: The leave-to-attendance sync may not have run yet, or the leave dates don't match what you expect.

**What to do**:
1. Check the leave request — is it definitely approved?
2. Check the dates — do they cover the day in question?
3. If the leave is approved and the dates are correct, the attendance should update automatically. If it hasn't, contact your system administrator.

### "An employee was hired in the middle of the month — how is their pay calculated?"

**What happens**: The system automatically prorates their pay. If they started on July 15 and the pay period is July 1–31, they receive pay for 17 days out of 31.

**What to do**: Nothing — this is automatic. Just make sure their hire date is correct in their employee record.

### "An employee is leaving — how do I handle their final pay?"

**What happens**: When you mark their employment status as "Terminated," the system automatically includes them in the next payroll run with prorated pay for their last working days.

**What to do**:
1. Update their employee position — set employment status to "Terminated."
2. The termination date is recorded automatically.
3. In the next payroll run, they will appear with prorated pay.
4. After the run, you may want to deactivate their payroll profile.

### "I need to give everyone a bonus this month"

**What to do**:
1. During the Payroll Wizard Step 2 (Adjustments), add a bonus for each employee.
2. Alternatively, create a **recurring adjustment profile** if this bonus happens every period.
3. For company-wide bonuses, you can add adjustments in bulk.

### "The payroll calculation is taking a long time"

**What happens**: Large payroll runs are processed in batches. The system shows progress as it works.

**What to do**: Wait for it to complete. If it fails, the system will retry automatically. Check the progress bar on the run detail page.

### "A finalized payroll run has a mistake"

**What to do**:
1. You cannot change a finalized run.
2. Create a correction adjustment in the next payroll run.
3. For an overpayment: add a deduction correction (negative amount).
4. For an underpayment: add a bonus correction (positive amount).
5. Add a clear note explaining what the correction is for.

### "An employee says their payslip is wrong"

**What to do**:
1. Open their payslip and review each line item.
2. Check their attendance records for the period — were all days approved?
3. Check their employee position — is the pay type, base salary, or hourly rate correct?
4. Check for any one-time adjustments that may have been applied.
5. If you find an error, handle it as a correction in the next payroll run.

---

## 10. Quick Reference — Your Daily Checklist

### Every Morning

- [ ] Check attendance for "Needs Review" records
- [ ] Handle any "Incomplete" records (missing clock-outs)
- [ ] Review any "Absent" records (unplanned absences)
- [ ] Approve records that look correct
- [ ] Check for pending leave requests

### Every Week

- [ ] Review all unapproved attendance records for the week
- [ ] Follow up on any patterns (frequent lateness, repeated absences)
- [ ] Check leave balances — are any employees running low?
- [ ] Verify new hire information is complete

### Before Every Payroll Run

- [ ] All attendance records for the period are approved
- [ ] All leave requests for the period are processed
- [ ] New hires and terminations have correct dates
- [ ] Employee positions (pay type, salary, rate) are up to date
- [ ] Recurring adjustments are still valid
- [ ] One-time adjustments are prepared

### During Payroll

- [ ] Create the payroll run with correct period dates
- [ ] Add any one-time adjustments (bonuses, deductions, corrections)
- [ ] Review the calculation preview carefully
- [ ] Check per-employee breakdowns for any unexpected numbers
- [ ] Finalize only when everything looks correct

### After Payroll

- [ ] Generate and distribute reports as needed
- [ ] Mark the run as Paid after approval
- [ ] Generate bank file for payment processing
- [ ] Note any corrections needed for the next cycle
- [ ] Start preparing for the next pay period

---

## That's It!

You now have a complete picture of how to manage the HR office day by day. Here's the most important thing to remember:

**Everything connects.** When you approve a leave request, it creates attendance records. When you approve attendance records, they become available for payroll. When you run payroll, it reads all those records and calculates pay. Every action you take in one part of the system affects the others.

The key to smooth operations is staying on top of things daily — a few minutes each morning checking attendance and leave prevents hours of cleanup later.

> **Need help?** Each section of the system has its own detailed guide:
> - [Attendance Guide](attendance/README.md) — detailed attendance management
> - [Leave Guide](leave/README.md) — detailed leave management
> - [Payroll Guide](payroll/README.md) — detailed payroll operations
> - [Employee Time-to-Pay Guide](time-to-pay-journey.md) — share this with employees who want to understand how their time becomes pay
