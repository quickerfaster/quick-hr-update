# Holiday Module — User Guide

## Overview

The Holiday module lets you manage company holidays, public holidays, and observances across your organization. You can create individual holidays, batch-create multiple holidays at once, set up country-specific templates, configure holiday pay rates, and automatically roll holidays forward each year.

Holidays integrate with Attendance (employees are automatically marked as present on holidays with credited hours) and Payroll (holiday pay rate multipliers are applied during payroll processing).

---

## Table of Contents

1. [Roles & Permissions](#1-roles--permissions)
2. [Key Concepts](#2-key-concepts)
3. [For Administrators](#3-for-administrators)
   - [Holiday Calendars](#31-holiday-calendars)
   - [Creating a Single Holiday](#32-creating-a-single-holiday)
   - [Batch Creation Wizard](#33-batch-creation-wizard)
   - [Editing & Deleting Holidays](#34-editing--deleting-holidays)
4. [Holiday Pay & Payroll Integration](#4-holiday-pay--payroll-integration)
5. [Year Rollover](#5-year-rollover)
6. [Recurrence Rules](#6-recurrence-rules)
7. [Dashboard & Calendar Views](#7-dashboard--calendar-views)
8. [Troubleshooting](#8-troubleshooting)

---

## 1. Roles & Permissions

| Role | Typical Holiday Access |
|------|---------------------|
| **HR Manager / Company Admin** | Full access — create, edit, delete holidays; manage calendars; batch-create holidays; configure pay rates |
| **Admin / Super Admin** | Complete access — all of the above plus configure holiday settings |
| **Manager / Supervisor** | View-only — see holiday calendars and upcoming holidays |
| **Employee** | View-only — see holiday calendars and upcoming holidays |
| **Payroll Officer** | View holidays that affect payroll plus pay rate information |
| **Finance Manager / Accountant** | View holidays that affect payroll |

Key permissions:

| Permission | What it gates |
|------------|---------------|
| `view_holiday_overview` | View the Holidays Overview dashboard |
| `view_holiday_calendar` | View holiday calendars |
| `view_holiday` | View holiday list and details |
| `create_holiday` | Create individual holidays |
| `edit_holiday` | Edit holiday details |
| `delete_holiday` | Delete holidays |
| `create_holiday_batch` | Access the batch creation wizard |
| `manage_holiday_calendar` | Create and manage holiday calendars |

> See the [Roles & Permissions Guide](../roles-and-permissions/README.md) for the complete matrix.

---

## 2. Key Concepts

| Term | Meaning |
|------|---------|
| **Holiday Calendar** | A named collection of holidays (e.g., "US National Holidays 2027"). Each calendar belongs to a company and has a target year and country code. |
| **Holiday** | A date on the calendar — can be a public holiday, company holiday, regional observance, or optional day. |
| **Holiday Pay Rate** | A multiplier (1.0x to 3.0x) applied to an employee's base pay for hours worked on a holiday. For example, 2.0x means double pay. |
| **Recurring Holiday** | A holiday that repeats every year on the same date (e.g., Christmas = December 25 every year). |
| **Floating Holiday** | A holiday that shifts each year based on a pattern (e.g., Thanksgiving = 4th Thursday of November, Memorial Day = Last Monday of May). |
| **Observed Date** | When a holiday falls on a weekend, the observed date is the weekday it's celebrated (e.g., Christmas on Saturday → observed on Monday). |
| **Business Impact** | How the holiday affects operations: Office Closed, Reduced Staff, Normal Operations, Remote Only, or Essential Staff Only. |
| **Holiday Template** | A pre-configured holiday with name, type, pay rate, and recurrence rule. Templates are curated per country (US, UK, Nigeria) and are used in the batch creation wizard. |
| **Batch Creation** | Create multiple holidays at once using the wizard — pick a calendar and year, choose dates, select templates, and submit. |
| **Year Rollover** | An automated process (Dec 1 each year) that copies recurring holidays from the current year to the next year. |
| **Half-Day Holiday** | A holiday where only a partial day is credited for payroll purposes (e.g., office closes at noon). |

### Holiday Types

| Type | Description | Common Examples |
|------|-------------|-----------------|
| **Public** | Government-mandated national holiday | New Year's Day, Independence Day |
| **Company** | Company-specific holiday | Founder's Day, Annual Picnic |
| **Regional / State** | Region or state-level holiday | State-specific observances |
| **Observance** | Cultural or religious observance (not a day off) | Valentine's Day, Halloween |
| **Optional** | Optional holiday that employees can choose to observe | Floating personal day |

### Holiday Statuses

| Status | Meaning |
|--------|---------|
| **Active** | Holiday is active and visible on the calendar. Included in attendance and payroll processing. |
| **Inactive** | Holiday is hidden from the calendar and does not affect attendance or payroll. Can be reactivated. |
| **Archived (Soft-Deleted)** | Holiday has been removed. Available in the Trash view for restoration. |

---

## 3. For Administrators

### 3.1 Holiday Calendars

Holiday Calendars organize holidays by company, year, and country. Before creating holidays, you need an active calendar.

**To create a calendar:**

1. Navigate to **Holidays** → **Holiday Calendars** in the sidebar.
2. Click **Add** or use the **Create Calendar** action card on the Overview dashboard.
3. Fill in:
   - **Name** — e.g., "US National Holidays 2027"
   - **Year** — the target year for this calendar
   - **Description** — optional notes
   - **Country Code** — two-letter code (US, GB, NG, etc.). Used for country-specific templates.
   - **Region** — optional state/region
   - **Applicable To** — who this calendar applies to
   - **Is Default** — make this the default calendar
   - **Active** — enable or disable
4. Click **Save**.

**Managing calendars:**

- Click the eye icon to view a calendar's holidays.
- Click the pencil icon to edit the calendar details.
- Click the trash icon to delete (only if it has no associated holidays).
- The **Holiday Count** column shows how many active holidays are in each calendar.

### 3.2 Creating a Single Holiday

1. Navigate to **Holidays** → **Holidays** in the sidebar.
2. Click **Add**.
3. Fill in the form. The fields are grouped into sections:

#### Basic Information
- **Holiday Calendar** — which calendar this holiday belongs to.
- **Name** — e.g., "Christmas Day".
- **Description** — optional notes.
- **Holiday Type** — Public, Company, Regional, Observance, or Optional.
- **Active** — enable or disable.

#### Date Configuration
- **Holiday Date** — the calendar date.
- **Observed Date** — if the holiday falls on a weekend and is observed on a different day.
- **Recurring Holiday** — check if this holiday repeats every year.
- **Recurrence Pattern** — choose from:
  - **Yearly (Fixed Date)** — same date every year (Dec 25).
  - **Yearly (Floating Date)** — floats based on a pattern (4th Thursday of November).
  - **Custom Pattern** — use the Recurrence Rule Builder to define a custom schedule.
  - **One-time Only** — holiday does not repeat.
- **Recurrence Rule** — a visual builder that converts your selections into a structured rule. Choose frequency (yearly/monthly/weekly/daily), set the date pattern, and pick an end condition. Quick presets are available for common holidays (Thanksgiving, Memorial Day, Labor Day, etc.).
- **Half Day Holiday** — check if only a partial day is observed.
- **Half Day End Time** — if half-day, specify when the day ends.

#### Business Impact
- **Business Impact** — Office Closed, Reduced Staff, Normal Operations, Remote Only, or Essential Only.
- **Eligible Employee Types** — which employee categories this holiday applies to (Full-time, Part-time, Contractors, Interns, Temporary, All).
- **Country Code** — two-letter country code for filtering.
- **Region/State Code** — state or region code for regional holidays.

#### Payroll Settings
- **Paid Holiday** — whether employees are paid for this day.
- **Affects Payroll** — whether this holiday impacts payroll calculations.
- **Holiday Pay Rate** — the pay multiplier (1.0x to 3.0x). Default is 1.0x (standard pay).
- **Minimum Hours for Pay** — minimum credited hours for payroll (default: 8).

4. Click **Save**.

### 3.3 Batch Creation Wizard

The Batch Creation Wizard lets you create multiple holidays at once — ideal for setting up a new year's calendar or adding country-specific holidays.

**Access:** Navigate to **Holidays** → **Batch Create** in the sidebar.

The wizard has five steps:

#### Step 1 — Select Calendar & Year

- **Holiday Calendar** — choose the target calendar.
- **Target Year** — the year for which holidays will be created.
- **Company** — in multi-company mode, filter calendars by company.
- **Creation Method** — how to populate the holidays:

| Method | What It Does |
|--------|-------------|
| **Copy from Previous Year** | Copies holidays from year-1 in the same calendar, shifting dates to the target year. Best for rolling forward an existing calendar. |
| **Start Fresh** | Pick dates and choose from 8 common holiday templates. Dates and templates are cross-joined (each date × each template). |
| **Use Country Template** | Loads curated holiday templates based on the calendar's country code. The system has templates for US (11 holidays), UK (8), and Nigeria (8). Select the ones you want. |
| **Import CSV File** | Upload a CSV with date and name columns. Each row becomes one holiday (1:1 mapping). |

#### Step 2 — Batch Defaults (Optional)

Enable this to set default values that apply to all holidays in the batch:
- **Default Pay Rate** — pay multiplier for all holidays.
- **Default Business Impact** — how each holiday affects operations.
- **Default Eligible Employee Types** — which employees these holidays apply to.
- **Default Paid / Recurring** — whether holidays are paid and recurring.

Individual holidays can still be edited in the review step.

#### Step 3 — Select Dates (Optional)

Choose which dates to create holidays on. If you skip this, each template uses its own natural date (1:1 mapping).

Three picker modes:
- **Quick Picker** — select weekdays (Mon–Sun) and a date range. Useful for "every Friday in December."
- **Multi-Date** — pick individual dates one at a time. Dates appear as removable tags.
- **CSV Upload** — upload a CSV file with dates and optionally names.

#### Step 4 — Select Holidays

Choose which holiday templates to create. Templates are shown with their date, type badge, and pay rate.

- **Filter by type** — show only Public, Company, Regional, etc.
- **Select All** — toggle all visible templates at once.
- Each template shows its target date, type, pay rate, and whether it's paid and recurring.

#### Step 5 — Review & Confirm

A table shows every holiday that will be created. You can:
- **Edit inline** — click the pencil icon to change name, date, type, or pay rate for any individual entry.
- **Remove** — click the trash icon to remove an entry.
- **Review totals** — the header shows how many holidays will be created, which calendar and year, and the creation method.

Click **Save & Continue** (or the final wizard button) to submit. All holidays are created in a single transaction.

> **Tip:** You can cancel at any time with "Keep Data" to save your progress. Click "Delete Progress" to clear everything and start over. Your selections are preserved while you stay on the page.

### 3.4 Editing & Deleting Holidays

- **Edit:** Click the pencil icon on any holiday in the DataTable or detail page. The form opens with all current values pre-filled. Make changes and save.
- **Delete:** Click the trash icon. The holiday is soft-deleted (moved to Trash). It can be restored from the Trash view.
- **Toggle Active/Inactive:** Use the switch view or edit form to deactivate a holiday without deleting it.
- **View:** Click the eye icon to see all holiday details in read-only mode, including calendar, dates, payroll settings, and business impact.

---

## 4. Holiday Pay & Payroll Integration

### How It Works

When an employee is scheduled to work on a holiday, the system:

1. **Attendance:** The Attendance Aggregator creates a holiday attendance record with `status = 'holiday'` and credits the employee with paid hours (full-day = 8 hours, half-day = 4 hours by default).

2. **Payroll:** During payroll processing, the system detects holiday attendance records and creates a **Holiday Pay Premium** adjustment for each employee:

| Pay Type | Premium Calculation |
|----------|-------------------|
| **Hourly** | `holiday hours × hourly rate × (pay rate − 1.0)` |
| **Salaried (Daily)** | `daily rate × (pay rate − 1.0)` |
| **Salaried (Full)** | `(base salary ÷ days in period) × (pay rate − 1.0)` |

**Example:** An hourly employee earning $25/hour works on Christmas (2.5x pay rate):
- Regular pay for 8 hours: `8 × $25 = $200` (paid via normal attendance)
- Holiday premium: `8 × $25 × (2.5 − 1.0) = $300` (added as a payroll adjustment)
- **Total holiday pay: $500**

The premium appears on the employee's payslip as an earning line item labeled "Holiday Pay Premium — Christmas (2.5x)."

### Configuring Holiday Pay

1. When creating or editing a holiday, go to the **Payroll Settings** section.
2. Set **Paid Holiday** to Yes.
3. Set **Affects Payroll** to Yes (if No, no premium is calculated).
4. Set **Holiday Pay Rate** to the desired multiplier:
   - **1.0x** = standard pay (no premium)
   - **1.5x** = time and a half
   - **2.0x** = double time
   - **2.5x** = double and a half
   - **3.0x** = triple time
5. Set **Minimum Hours for Pay** (default: 8). For half-day holidays, 4 hours are credited.

### Viewing Premiums in Payroll

1. After processing a payroll run that covers holiday dates, go to **Payroll** → **Payroll Run Adjustments**.
2. Filter by the payroll run. Holiday premium adjustments appear with `source_type = 'holiday_pay'`.
3. Open an employee's payslip to see the premium as an earning line item.

> **Edge cases handled:**
> - **Pay rate = 1.0x:** No premium is created (no extra pay).
> - **affects_payroll = false:** No premium, regardless of pay rate.
> - **Duplicate prevention:** Running payroll twice for the same period won't create duplicate premiums.
> - **Half-day holidays:** Premium is calculated on half the minimum hours.

---

## 5. Year Rollover

Recurring holidays are automatically rolled forward to the next year on **December 1st** at midnight. This ensures the following year's calendar is ready before the new year begins.

### How It Works

- Only holidays with `Recurring = Yes` are copied.
- Fixed-date holidays (e.g., Dec 25) keep the same month and day.
- Floating holidays (e.g., Thanksgiving) use their recurrence rule to compute the correct date.
- February 29th in a leap year rolls to February 28th in a non-leap year.
- The operation is **idempotent** — running it multiple times won't create duplicates.

### Running Manually

If you need to trigger a rollover outside the schedule:

```bash
php artisan holiday:rollover --year=2026 --target-year=2027
```

Omit the options to roll from the current year to the next:

```bash
php artisan holiday:rollover
```

### Verifying the Rollover

1. After the command runs, go to **Holidays** and filter by the target year.
2. Verify recurring holidays appear with the correct dates.
3. Check that non-recurring holidays from the source year were NOT copied.
4. The calendar's **Holiday Count** updates to reflect the new total.

---

## 6. Recurrence Rules

Recurrence rules determine when a holiday repeats. The Holiday module supports two types:

### Fixed Date
The holiday falls on the same calendar date every year (e.g., Christmas = December 25, Independence Day = July 4).

### Floating Date
The holiday shifts based on a pattern. The **Recurrence Rule Builder** (a visual tool in the holiday form) lets you define:

| Frequency | Options |
|-----------|---------|
| **Yearly** | Fixed date (month + day) or floating (e.g., "3rd Monday of January") |
| **Monthly** | Day of month (e.g., 15th) or day of week (e.g., "2nd Tuesday"), with interval |
| **Weekly** | Specific days (Mon–Sun), with interval (e.g., every 2 weeks) |
| **Daily** | Every N days |

**End conditions:** Never, After N occurrences, or On a specific date.

**Quick Presets:** One-click buttons for common holidays:
- 🇺🇸 Thanksgiving (4th Thursday of November)
- 🇺🇸 Memorial Day (Last Monday of May)
- 🇺🇸 Labor Day (1st Monday of September)
- 📅 First Monday of every month
- 📅 15th of every month
- 🔄 Every other Friday

The builder generates a human-readable summary (e.g., "The Fourth Thursday of November") and stores the rule as structured data for the year rollover to use.

---

## 7. Dashboard & Calendar Views

### Holidays Overview Dashboard

**URL:** `/holiday/dashboard-holidays-overview`

The dashboard provides a high-level view of your holiday setup:

| Widget | What It Shows |
|--------|--------------|
| **Total Holidays** | Count of all holidays across all calendars |
| **Active Holidays** | Count of currently active holidays |
| **Holiday Calendars** | Count of calendars |
| **Upcoming (30 Days)** | Holidays in the next 30 days |
| **Holidays by Type** | Pie chart showing distribution of Public, Company, Regional, etc. |
| **Holidays by Calendar** | Bar chart showing holiday count per calendar |
| **Holidays Added (Last 6 Months)** | Trend of holiday creation over time |
| **Upcoming Holidays** | List of next 5 upcoming holidays with dates and types |
| **Holiday Calendars** | List of calendars with descriptions |

### Calendar View

The holidays DataTable includes a built-in **Calendar View** (toggle via the view switcher). This shows holidays on a month or year grid, color-coded by holiday type. Useful for visually scanning which days are holidays.

### DataTable Features

- **Search** holidays by name, country code, or type.
- **Filter** by calendar, holiday type, business impact, paid status, and active status.
- **Sort** by any column.
- **Export** to XLS, CSV, or PDF.
- **Toggle** between table view, card view, and calendar view.
- **Trash view** — see and restore soft-deleted holidays.

---

## 8. Troubleshooting

### I don't see the "Batch Create" option in the sidebar.

**Cause:** You don't have the `create_holiday_batch` permission.  
**Solution:** Ask an admin to assign the permission to your role.

### The "Add" button on the Multi-Date tab stays disabled.

**Cause:** The date input uses deferred binding; the component isn't refreshing.  
**Solution:** This has been fixed. If it recurs, clear your browser cache or refresh the page.

### The batch creation wizard shows the wrong number of holidays in the preview.

**Cause:** You selected both dates and templates, which creates a cross-join (each date × each template).  
**Solution:** If you want each template on its own natural date, skip the "Select Dates" step (leave it empty). The system will then use each template's own date.

**"New Year's Day" is showing on February 27 instead of January 1.** This happens when you pick dates in the Date Selector step — the dates override the template's natural date. Skip the Date Selector step to use template dates.

### Holidays aren't appearing in the Attendance module.

**Cause:** The holiday might be inactive, or the attendance aggregator hasn't run for the date in question.  
**Solution:** 
1. Verify the holiday is `Active` and `business_impact` is set to `office_closed` or `reduced_staff`.
2. The attendance aggregator must be triggered (manually or via schedule) to create holiday attendance records.

### Holiday pay premiums aren't appearing on payslips.

**Cause:** The holiday might have `affects_payroll = false` or `holiday_pay_rate = 1.0`.  
**Solution:**
1. Check that `affects_payroll` is `true` on the holiday.
2. Set `holiday_pay_rate` to a value greater than 1.0 (e.g., 2.0).
3. Verify the employee has a holiday attendance record (`status = 'holiday'`) on that date.
4. Reprocess the payroll run if it was already processed before the holiday pay rate was set.

### The year rollover didn't copy any holidays.

**Cause:** No recurring holidays exist in the active calendars, or the command was run with the wrong year parameters.  
**Solution:**
1. Verify holidays in the source year have `Recurring = Yes`.
2. Check the Laravel log (`storage/logs/laravel.log`) for rollover messages.
3. Run the command manually with explicit years: `php artisan holiday:rollover --year=2026 --target-year=2027`.

### I see "Holiday Pay Premium" on my payslip even though I didn't work on the holiday.

This is expected. The Premium is credited for ALL employees with holiday attendance records, regardless of whether they clocked in. The holiday attendance record is created automatically by the Attendance Aggregator.

### The company dropdown is missing on Step 1 of the batch wizard.

This is intentional in single-company mode. When your session has a specific company selected, the company is pre-selected and locked. The dropdown only appears in multi-company mode (no specific company selected).

---

**End of User Guide**
