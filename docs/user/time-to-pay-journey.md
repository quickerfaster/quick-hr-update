# From Clock-In to Payday — A Complete Guide for Employees

> **Who this guide is for**: Anyone who clocks in and out of work and wants to understand how their time turns into pay. No technical knowledge needed.

---

## Table of Contents

1. [The Big Picture: Your Time Becomes Your Pay](#1-the-big-picture-your-time-becomes-your-pay)
2. [Part 1 — Clocking In and Out](#2-part-1--clocking-in-and-out)
3. [Part 2 — How Your Daily Attendance Is Calculated](#3-part-2--how-your-daily-attendance-is-calculated)
4. [Part 3 — Understanding Your Attendance Status](#4-part-3--understanding-your-attendance-status)
5. [Part 4 — Leave Requests: Taking Time Off](#5-part-4--leave-requests-taking-time-off)
6. [Part 5 — How Leave Affects Your Attendance and Pay](#6-part-5--how-leave-affects-your-attendance-and-pay)
7. [Part 6 — Company Holidays](#7-part-6--company-holidays)
8. [Part 7 — From Attendance to Payroll: How Your Pay Is Calculated](#8-part-7--from-attendance-to-payroll-how-your-pay-is-calculated)
9. [Part 8 — Understanding Your Payslip](#9-part-8--understanding-your-payslip)
10. [Part 9 — Common Situations and What to Do](#10-part-9--common-situations-and-what-to-do)
11. [Part 10 — Quick Reference: What Affects What](#11-part-10--quick-reference-what-affects-what)

---

## 1. The Big Picture: Your Time Becomes Your Pay

Every time you clock in and clock out, the system records that moment. At the end of each day, those clock events are turned into an **attendance record** — a single summary of your workday. When it's time to run payroll, the system looks at all your attendance records for the pay period, adds up your hours or days worked, applies your salary or hourly rate, and produces your **payslip**.

Here is the journey in four simple steps:

```
You clock in → You clock out
       ↓
Your attendance record is created (status, hours, any issues)
       ↓
Payroll reads all your attendance records for the period
       ↓
Your payslip is generated (gross pay, deductions, net pay)
```

If you take leave, that creates attendance records too — the system marks those days as "on leave" so payroll knows you were away with permission. If there's a company holiday, the system marks that day as a holiday.

Let's walk through each step in detail.

---

## 2. Part 1 — Clocking In and Out

### How to Clock In

1. Go to **My Portal** from the sidebar or your dashboard.
2. Find the **Clock In / Out** card.
3. Click the **Clock In** button when you start work.
4. Click the **Clock Out** button when you finish.

The card shows your current state:
- **"Not Clocked In"** — you haven't started yet
- **"Clocked In"** — shows the time you started (for example, "Clocked in at 8:00 AM")

### What Happens Behind the Scenes

When you click Clock In, the system saves:
- The exact date and time
- Whether it's a clock-in or clock-out
- Your location (if you allowed location access)
- The device you used (phone or computer)

Each clock-in and clock-out is called a **clock event**. The system pairs them together — your first clock-in with your next clock-out makes one **work session**. If you clock in, clock out for lunch, clock in again, and clock out at the end of the day, you'll have two work sessions for that day.

### What to Do If…

| Situation | What to Do |
|-----------|-----------|
| **You forgot to clock in** | Contact your HR administrator. They can add a missing clock-in time for you. |
| **You forgot to clock out** | Same — contact HR. The system will show you as still clocked in until it's fixed. |
| **You clocked in twice by accident** | Don't worry — the system ignores duplicate clock-ins within a few seconds of each other. |
| **You work an overnight shift** | Clock in before midnight and clock out after midnight as normal. The system handles overnight shifts correctly. |
| **The clock-in button doesn't work** | Check that you have an active employee position and that your company has a default attendance policy set up. If both are in place, contact HR. |

---

## 3. Part 2 — How Your Daily Attendance Is Calculated

After you clock out (or at the end of the day), the system runs a calculation that turns your clock events into a single **attendance record** for that day.

### What the System Looks At

To calculate your attendance, the system needs to know:

1. **Your clock events** — when you clocked in and out
2. **Your schedule** — what time you were expected to start and finish (from your shift or work pattern)
3. **Your attendance policy** — the rules your company set for things like grace periods and overtime

### How the Hours Are Calculated

Let's say your shift is 9:00 AM to 5:00 PM (8 hours). Here's what happens:

**Step 1 — Calculate actual hours worked**

The system adds up all your work sessions. If you clocked in at 8:55 AM and clocked out at 5:05 PM, your actual worked time is 8 hours and 10 minutes.

**Step 2 — Check for lateness**

Your company's attendance policy has a **grace period** — usually 5 minutes. This means if you clock in within 5 minutes of your start time, you're not marked late.

- Clock in at 9:03 AM → within the 5-minute grace period → **not late**
- Clock in at 9:08 AM → outside the grace period → **marked late** (3 minutes late)

**Step 3 — Check for early departure**

There's also an **early departure grace period** — usually 5 minutes before your scheduled end time.

- Clock out at 4:57 PM → within the 5-minute grace period → **not early**
- Clock out at 4:50 PM → outside the grace period → **marked as early departure** (5 minutes early)

**Step 4 — Split hours into regular, overtime, and double-time**

Your attendance policy sets thresholds:

| Type of Hours | When It Applies | Example (with default settings) |
|--------------|-----------------|--------------------------------|
| **Regular hours** | Up to 8 hours per day, up to 40 hours per week | You worked 7 hours → all 7 are regular |
| **Overtime hours** | Hours beyond 8 in a day, OR when your weekly total goes past 40 | You worked 10 hours → 8 regular + 2 overtime |
| **Double-time hours** | Hours beyond 12 in a single day | You worked 14 hours → 8 regular + 4 overtime + 2 double-time |

**Step 5 — Deduct unpaid breaks**

If your company policy includes unpaid break time (for example, 30 minutes for lunch), that time is deducted from your **payable hours**. Your actual worked time stays the same for status purposes, but the payable hours are reduced.

**Step 6 — Check break compliance**

If your policy requires you to take a break after a certain number of hours (for example, a 30-minute break after 5 hours of continuous work), the system checks whether you actually took that break. If you didn't, it's flagged as a violation.

### What to Expect When…

| Situation | What Happens |
|-----------|-------------|
| **You worked a normal day** | Status: Present. All hours are regular. |
| **You arrived a few minutes late** | Status: Late. Your hours are still counted normally. |
| **You left early** | Status: Early Departure. Your hours are still counted for the time you worked. |
| **You worked extra hours** | Your overtime hours are recorded separately and paid at a higher rate. |
| **You didn't clock in at all** | If you have no approved leave, you're marked as Absent (unplanned). |

---

## 4. Part 3 — Understanding Your Attendance Status

Every day, your attendance record gets one of these statuses:

### The Statuses Explained

| Status | What It Means | Example |
|--------|--------------|---------|
| **Present** | You worked your full expected hours, on time, with no issues | Clocked in at 8:55, out at 5:05 — perfect day |
| **Late** | You clocked in after the grace period | Clocked in at 9:12 when your shift starts at 9:00 (grace period is 5 minutes, so you're 7 minutes late) |
| **Half-Day** | You worked, but only up to half of your expected hours | You worked 3 hours but your shift is 8 hours |
| **Incomplete** | You worked more than half but less than 90% of your expected hours, or you forgot to clock out | You worked 6 hours of an 8-hour shift, or you have a missing clock-out |
| **Early Departure** | You clocked out before the early departure grace window | Clocked out at 4:45 when your shift ends at 5:00 (grace is 5 minutes, so you're 10 minutes early) |
| **Absent** | You didn't work at all on a scheduled workday, and you have no approved leave | No clock events, no approved leave |
| **Unscheduled** | You worked on a day that's not in your normal work pattern | You came in on a Saturday when your pattern is Monday–Friday |
| **Holiday** | It's a company holiday — no work expected | Christmas Day, New Year's Day |
| **Leave** | You have approved leave for this day | You're on annual leave, sick leave, etc. |

### How the System Decides Your Status

The system checks things in this order:

1. **Is today a company holiday?** → If yes, status is **Holiday**. Nothing else matters.
2. **Do you have approved leave?** → If yes, status is **Leave**. Clock events are ignored.
3. **Did you clock in at all?** → If no, status is **Absent** (unplanned).
4. **Did you clock in?** → The system calculates your status based on your hours, lateness, and early departure.

### What to Do If…

| Situation | What to Do |
|-----------|-----------|
| **Your status says "Absent" but you were at work** | You probably forgot to clock in. Contact HR to add your missing clock events and recalculate. |
| **Your status says "Incomplete" but you worked a full day** | You may have forgotten to clock out. The system sees a clock-in with no matching clock-out. Contact HR. |
| **Your status says "Late" but you were on time** | Check your scheduled start time — it might be different from what you expect. Your shift or work pattern determines your expected start. |
| **Your status says "Unscheduled"** | You worked on a day outside your normal work pattern. This isn't necessarily bad — it just means the day isn't in your regular schedule. |

---

## 5. Part 4 — Leave Requests: Taking Time Off

### How to Apply for Leave

1. Go to the **Leave Hub** from your sidebar or dashboard.
2. Click the **Apply** tab.
3. Choose the **type of leave** you want (for example, Annual Leave or Sick Leave). The dropdown shows how many days you have left.
4. Pick your **start date** and **end date**.
5. If you only need half a day, check the **Half Day** box and choose morning (AM) or afternoon (PM).
6. Add a reason if you want (optional).
7. Click **Save Draft** to finish later, or **Save & Continue** to submit.

### What Happens After You Apply

1. Your leave request is created with a status of **Pending**.
2. If the leave type requires approval, your manager is notified.
3. Your manager reviews your request and either **approves** or **rejects** it.
4. You receive a notification about the decision.

### What Happens When Leave Is Approved

When your leave is approved, several things happen automatically:

1. **Your leave balance is reduced** — if the leave type deducts from your balance, the number of days you took is subtracted.
2. **Attendance records are created** — for each workday in your leave period, the system creates an attendance record with the status "Leave."
3. **Weekends and holidays are skipped** — if your leave covers a weekend or a company holiday, those days don't count as leave days and don't reduce your balance.
4. **The attendance records are auto-approved** — you don't need separate approval for leave attendance records.

### What Happens When Leave Is Rejected

- Your leave balance is **not** reduced.
- No attendance records are created.
- You can edit and resubmit your request, or apply for different dates.

### What Happens When Leave Is Cancelled

If your leave is cancelled after being approved:

1. The attendance records are unlinked from your leave request.
2. The system recalculates those days — if you clocked in, your actual work hours are used instead.
3. Your leave balance is restored.

### Understanding Your Leave Balance

There are two ways your company might give you leave days:

**Accrual model (you earn days over time)**:
- You earn a little bit of leave each month or week
- Example: 1.67 days per month = 20 days per year
- You can only use days you've already earned
- Your balance grows throughout the year

**Lump-sum model (you get all days upfront)**:
- Your full annual allowance is available from day one
- Example: 20 days available on January 1st
- You can take all your leave at once if you want

### What to Do If…

| Situation | What to Do |
|-----------|-----------|
| **A leave type doesn't appear in the dropdown** | You may not have a balance allocated for that leave type. Contact HR to set up your leave balance. |
| **Your balance shows 0 but you think you have days** | A balance record may not have been created for this year yet. Contact HR. |
| **Your leave was rejected** | Check the rejection reason. You can edit and resubmit, or speak with your manager. |
| **You need to cancel approved leave** | Contact HR or your manager. They can cancel the request, which restores your balance and recalculates your attendance. |
| **You applied for leave covering a holiday** | The system automatically skips holidays — they don't count as leave days and don't reduce your balance. |

---

## 6. Part 5 — How Leave Affects Your Attendance and Pay

Not all leave is the same when it comes to your pay. Your company sets up each leave type with specific rules.

### Paid vs Unpaid Leave

| Leave Type Setting | What It Means for Your Pay |
|-------------------|---------------------------|
| **Paid Leave** (`is_paid = true`) | You receive your normal pay for the leave day. For hourly employees, your standard shift hours are credited. For salaried daily employees, the day counts as worked. |
| **Unpaid Leave** (`is_paid = false`) | You do not receive pay for the leave day. For hourly employees, zero hours are credited. For salaried daily employees, the day still counts as worked (your daily rate isn't reduced), but no hours are added. |

### Full-Day vs Half-Day Leave

| Leave Type | What Happens |
|-----------|-------------|
| **Full-day leave** | Your full standard shift hours are credited (for paid leave) or zero hours (for unpaid leave) |
| **Half-day leave** | Half your standard shift hours are credited (for paid leave). For example, if your shift is 8 hours, a half-day paid leave credits 4 hours. |

### How Leave Attendance Records Work

When your leave is approved, the system creates attendance records that look like this:

| Field | Value for Paid Leave | Value for Unpaid Leave |
|-------|---------------------|----------------------|
| Status | "Leave" | "Leave" |
| Hours credited | Your standard shift hours (e.g., 8) | 0 |
| Approved | Yes (automatic) | Yes (automatic) |
| Needs review | No | No |

These records are immediately ready for payroll — no separate approval is needed.

### What to Expect When…

| Situation | What Happens to Your Pay |
|-----------|------------------------|
| **You take 3 days of paid annual leave** | Those 3 days count as worked. Your pay is the same as if you had worked. |
| **You take 2 days of unpaid leave** | Those 2 days still count as worked days (your daily rate isn't reduced), but no hours are credited. For hourly employees, you're not paid for those hours. |
| **You take a half-day of paid leave** | Half your standard hours are credited. If your shift is 8 hours, you get credited for 4 hours. |
| **Your leave overlaps with a weekend** | Weekend days are skipped — they don't count as leave days and don't affect your balance or pay. |
| **Your leave overlaps with a holiday** | The holiday takes priority. That day is marked as a holiday, not as leave. It doesn't count against your leave balance. |

---

## 7. Part 6 — Company Holidays

### How Holidays Work

Company holidays are set up by your HR administrator. Each holiday can be configured with pay rules.

### Holiday Pay Settings

| Setting | What It Means |
|---------|--------------|
| **Paid Holiday** | If checked, you receive credited hours for the holiday (like a normal workday). If unchecked, the holiday is unpaid. |
| **Minimum Hours for Pay** | How many hours you're credited for a paid holiday (usually 8). |
| **Half Day** | If checked, you're credited half the minimum hours (for example, 4 hours instead of 8). |
| **Affects Payroll** | If unchecked, the holiday is completely ignored by payroll. |

### Holiday Priority

Holidays take priority over **everything else**. If a date is a company holiday:

- It's always marked as **Holiday** — even if you have approved leave or you clocked in
- Your leave balance is not reduced (holidays don't count as leave days)
- If the holiday is paid, you receive credited hours
- If the holiday is unpaid, you receive zero hours

### What to Expect When…

| Situation | What Happens |
|-----------|-------------|
| **A paid holiday falls on your workday** | You're credited your minimum hours (usually 8). Your pay is the same as a normal workday. |
| **An unpaid holiday falls on your workday** | You receive zero hours. For salaried daily employees, the day still counts as worked. |
| **You have leave covering a holiday** | The holiday takes priority. That day is marked as Holiday, not Leave. Your leave balance is not reduced for that day. |
| **You work on a holiday** | The day is still marked as Holiday. Your clock events are ignored. (Future: holiday pay rates may apply.) |

---

## 8. Part 7 — From Attendance to Payroll: How Your Pay Is Calculated

When your company runs payroll, the system looks at all your attendance records for the pay period and calculates your pay based on how you're paid.

### The Three Ways Employees Are Paid

| Pay Type | How Your Pay Is Calculated |
|----------|--------------------------|
| **Salaried Full** (fixed monthly salary) | You receive your full base salary every period, regardless of how many days you worked. Attendance is not checked. |
| **Salaried Daily** (paid per day worked) | Your pay = (your base salary ÷ number of workdays in the period) × number of days you actually worked. Attendance is checked. |
| **Hourly** (paid per hour worked) | Your pay = (regular hours × your hourly rate) + (overtime hours × your hourly rate × 1.5) + (double-time hours × your hourly rate × 2.0). Attendance is checked. |

### How "Worked Days" Are Counted (Salaried Daily)

A day counts as "worked" if **any** of these is true:
- You have more than 0 payable hours
- Your status is anything other than "Absent"
- The day is marked as a paid absence (paid leave or paid holiday)

This means all of these count as worked days: Present, Late, Half-Day, Incomplete, Early Departure, Unscheduled, Leave (paid or unpaid), and Holiday.

Only **Absent** (unplanned, no leave) does NOT count as a worked day.

### How Hours Are Counted (Hourly)

The system adds up your hours from each day:

| Type of Day | Hours Credited |
|------------|---------------|
| Normal workday (Present, Late, etc.) | Your actual regular, overtime, and double-time hours |
| Paid leave day | Your standard shift hours as regular hours |
| Unpaid leave day | 0 hours (but the day still counts as worked) |
| Paid holiday | Your minimum holiday hours as regular hours |
| Unpaid holiday | 0 hours |
| Half-day paid leave/holiday | Half the standard hours |
| Absent (unplanned) | 0 hours, and the day does NOT count as worked |

### How Overtime Is Paid

Overtime rates come from your company's attendance policy:

| Type | Multiplier | Example (with $20/hour rate) |
|------|-----------|------------------------------|
| Regular | 1.0× | $20 per hour |
| Overtime | 1.5× (default) | $30 per hour |
| Double-time | 2.0× (default) | $40 per hour |

### What Happens If You Join or Leave Mid-Period

If you start your job in the middle of a pay period, or leave in the middle of a pay period, your pay is automatically adjusted:

- **New hire mid-period**: You're paid only for the days from your start date to the end of the period.
- **Leaving mid-period**: You're paid only for the days from the start of the period to your last day.
- **Final pay**: When you leave the company, you automatically receive a final payslip for your last working days.

### What to Expect When…

| Situation | What Happens to Your Pay |
|-----------|------------------------|
| **You're salaried and took 2 sick days** | No change — your full salary is paid regardless of attendance. |
| **You're daily-rate and missed 2 days without leave** | Your pay is reduced: those 2 days are not counted as worked. |
| **You're daily-rate and took 2 days of approved leave** | No reduction — leave days count as worked. |
| **You're hourly and worked 45 hours this week** | 40 hours at regular rate + 5 hours at overtime rate (1.5×). |
| **You're hourly and took a paid leave day** | You receive your standard shift hours at your regular rate for that day. |
| **You're hourly and took an unpaid leave day** | You receive 0 hours for that day. |
| **You started on the 15th of a monthly period** | Your salary is prorated: you receive roughly half the monthly amount. |

---

## 9. Part 8 — Understanding Your Payslip

When payroll is run, you receive a payslip. Here's what each part means:

### The Main Numbers

| Line | What It Means |
|------|--------------|
| **Base Salary** | Your base pay for the period (prorated if you joined or left mid-period) |
| **Gross Pay** | Total earnings before any deductions — includes base pay, overtime, bonuses, and allowances |
| **Income Tax** | Federal or national income tax deducted |
| **Social Security** | Social security or national insurance contribution |
| **Medicare** | Medicare or national health insurance contribution |
| **Pension (Your Share)** | Your contribution to your pension |
| **Pension (Company Share)** | What your employer contributes to your pension (not deducted from your pay) |
| **Health Insurance (Your Share)** | Your health insurance premium |
| **Health Insurance (Company Share)** | What your employer pays for your health insurance |
| **Other Earnings** | Any benefits, bonuses, or commissions |
| **Other Deductions** | Any other deductions like loan repayments or union dues |
| **Total Deductions** | Sum of everything deducted |
| **Net Pay** | Your take-home pay — what actually goes into your bank account |

### Example Payslip (Salaried Employee)

Let's say you earn ₦200,000 per month:

| Item | Amount |
|------|--------|
| Base Salary | ₦200,000 |
| **Gross Pay** | **₦200,000** |
| Income Tax | −₦15,000 |
| Pension (your share, 8%) | −₦16,000 |
| Health Insurance | −₦5,000 |
| **Total Deductions** | **−₦36,000** |
| **Net Pay** | **₦164,000** |

Your employer also contributes:
- Pension (company share, 10%): ₦20,000
- Health Insurance (company share): ₦5,000

These employer contributions don't come out of your pay — they're paid by the company on top of your salary.

### Example Payslip (Hourly Employee)

Let's say you earn ₦2,000 per hour and worked 176 hours this month (160 regular + 16 overtime):

| Item | Amount |
|------|--------|
| Base Pay (160h × ₦2,000) | ₦320,000 |
| Overtime Pay (16h × ₦2,000 × 1.5) | ₦48,000 |
| **Gross Pay** | **₦368,000** |
| Deductions… | … |
| **Net Pay** | **₦310,000** (example after deductions) |

### What to Do If…

| Situation | What to Do |
|-----------|-----------|
| **Your gross pay seems too low** | Check your attendance records for the period — were any days marked Absent? Were all your hours recorded? |
| **Your overtime pay is missing** | Check that your attendance records show overtime hours. Overtime is calculated from your clock events. |
| **A deduction seems wrong** | Each deduction comes from a payroll policy. Contact HR or Payroll to review the policy applied to you. |
| **Your net pay doesn't match your bank deposit** | Check the payslip for any one-time deductions or corrections. Contact Payroll if it still doesn't match. |

---

## 10. Part 9 — Common Situations and What to Do

### "I forgot to clock in this morning"

**What happens**: The system sees no clock-in event. If you clock out later, you'll have an orphaned clock-out. Your status may show as "Incomplete" or "Absent."

**What to do**: Contact HR. They can add your missing clock-in time and recalculate your attendance. Your hours will be corrected.

### "I forgot to clock out yesterday"

**What happens**: The system sees a clock-in with no matching clock-out. Your status will be "Incomplete" and flagged for review.

**What to do**: Contact HR. They can add your missing clock-out time. Until it's fixed, your hours for that day may not be counted correctly.

### "I worked on a Saturday but my status says Unscheduled"

**What happens**: Your work pattern says you work Monday–Friday. Working on Saturday is outside your pattern, so it's marked as "Unscheduled."

**What to do**: This isn't necessarily a problem — your hours are still recorded. If the work was authorized, your manager can approve the record. The hours will still count for payroll.

### "I applied for leave but it still shows as Pending"

**What happens**: Your manager hasn't approved or rejected it yet.

**What to do**: Check with your manager. If it's urgent, ask them to review it. Pending leave does not create attendance records and does not affect your pay.

### "My leave was approved but my balance hasn't changed"

**What happens**: The leave type may not deduct from your balance (some leave types, like compassionate leave, don't reduce your balance). Or the balance update may not have synced yet.

**What to do**: Check the leave type settings — if "Deducts from Balance" is unchecked, your balance won't change. Otherwise, contact HR.

### "I see a holiday on my attendance but I worked that day"

**What happens**: Holidays take priority over everything. If the date is a company holiday, it's marked as Holiday regardless of whether you worked.

**What to do**: Currently, working on a holiday doesn't override the holiday status. Contact HR if you need your work hours recorded for that day. (Future: holiday work pay rates will be supported.)

### "My payslip shows fewer days than I expected"

**What happens (salaried daily)**: Some of your attendance records may not be approved, or you may have unplanned absences.

**What to do**: 
1. Check your attendance records for the pay period — look for any days marked "Absent."
2. Make sure all your attendance records are approved (`is_approved = true`).
3. If you had approved leave, verify the leave was synced to attendance.

### "My payslip shows fewer hours than I expected"

**What happens (hourly)**: Some days may have 0 hours credited — for example, unpaid leave days or unplanned absences.

**What to do**:
1. Check your attendance records — look at the `regular_hours` and `overtime_hours` for each day.
2. If you had leave, check whether the leave type is paid or unpaid.
3. If you had a holiday, check whether it's a paid or unpaid holiday.

### "I'm leaving the company — when do I get my final pay?"

**What happens**: When your employment ends, you automatically receive a final payslip in the next payroll run covering your last working days. Your pay is prorated — you're paid only for the days you actually worked in that period.

**What to do**: Your final payslip will appear in the normal payroll run. If you don't see it, contact HR to verify your termination date is correctly recorded.

---

## 11. Part 10 — Quick Reference: What Affects What

### Settings That Affect Your Attendance Status

| Setting | Where It's Set | What It Does |
|---------|---------------|-------------|
| Grace period (minutes) | Attendance policy | How many minutes after your start time you can clock in without being marked late |
| Early departure grace (minutes) | Attendance policy | How many minutes before your end time you can clock out without being marked early |
| Unpaid break (minutes) | Attendance policy | How many minutes are deducted from your payable hours for breaks |
| Daily overtime threshold (hours) | Attendance policy | How many hours you can work before overtime starts (usually 8) |
| Weekly overtime threshold (hours) | Attendance policy | How many total regular hours before weekly overtime starts (usually 40) |
| Double-time threshold (hours) | Attendance policy | How many hours before double-time pay starts (usually 12) |
| Your shift start/end time | Your assigned shift | When you're expected to start and finish |
| Your work pattern days | Your work pattern | Which days of the week you're expected to work |

### Settings That Affect Your Leave

| Setting | Where It's Set | What It Does |
|---------|---------------|-------------|
| Paid / Unpaid | Leave type | Whether leave days are paid or unpaid |
| Deducts from balance | Leave type | Whether taking this leave reduces your available balance |
| Requires approval | Leave type | Whether a manager must approve your request |
| Half day | Your leave request | Whether you're taking a full day or half day |
| Annual allowance | Leave balance config | How many days you get per year |

### Settings That Affect Your Pay

| Setting | Where It's Set | What It Does |
|---------|---------------|-------------|
| Pay type (salaried/daily/hourly) | Your employee position | How your gross pay is calculated |
| Base salary | Your employee position | Your fixed pay per period |
| Hourly rate | Your employee position | Your pay per hour (hourly employees) |
| Overtime multiplier | Attendance policy | How much extra you're paid for overtime (usually 1.5×) |
| Double-time multiplier | Attendance policy | How much extra you're paid for double-time (usually 2.0×) |
| Paid holiday | Holiday settings | Whether you receive credited hours for a holiday |
| Minimum hours for pay | Holiday settings | How many hours you're credited for a paid holiday |
| Tax bands | Payroll policy | How much income tax is deducted based on your earnings |
| Pension rate | Payroll policy | What percentage goes to your pension |
| Insurance premium | Payroll policy | Your health insurance deduction |

### Settings That Affect Your Holiday Pay

| Setting | Where It's Set | What It Does |
|---------|---------------|-------------|
| Paid holiday | Holiday record | Whether the holiday is paid |
| Affects payroll | Holiday record | Whether the holiday appears in payroll at all |
| Minimum hours for pay | Holiday record | Hours credited for a paid holiday (default: 8) |
| Half day | Holiday record | Whether only half the minimum hours are credited |
| Holiday pay rate | Holiday record | Multiplier for working on the holiday (future feature) |

---

## That's It!

You now understand the complete journey from clocking in to receiving your pay. Here's a quick summary:

1. **Clock in and out** each day — the system records every event.
2. **Your daily attendance** is calculated from your clock events, your schedule, and your company's policies.
3. **Leave requests** create attendance records when approved — paid leave credits hours, unpaid leave doesn't.
4. **Holidays** take priority over everything and can be paid or unpaid.
5. **Payroll** reads all your approved attendance records and calculates your pay based on your pay type.
6. **Your payslip** shows your gross pay, all deductions, and your final net pay.

If something doesn't look right, start by checking your attendance records — that's usually where issues can be spotted and fixed.

> **Need help?** Contact your HR administrator or Payroll officer. They can adjust attendance records, fix missing clock events, review leave balances, and explain any deductions on your payslip.
