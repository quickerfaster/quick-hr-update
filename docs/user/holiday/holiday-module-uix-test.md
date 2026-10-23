# User Acceptance Testing (UAT) Guide
## Holiday Module — Payroll Integration & Year Rollover

**Purpose:** Verify the Holiday module across administration, calendar management, batch creation, payroll integration (holiday pay rates), and automatic year rollover.

## Table of Contents

1. [Prerequisites](#1-prerequisites)
2. [Admin Testing](#2-admin-testing)
   - [Overview Dashboard](#21-overview-dashboard)
   - [Holiday Calendar Management](#22-holiday-calendar-management)
   - [Holiday CRUD](#23-holiday-crud)
   - [Batch Creation Wizard](#24-batch-creation-wizard)
   - [Holiday Detail Page](#25-holiday-detail-page)
3. [Payroll Integration Testing](#3-payroll-integration-testing)
4. [Scheduled Command Testing](#4-scheduled-command-testing)
5. [Quick Smoke Test](#5-quick-smoke-test)
6. [Test Completion Record](#6-test-completion-record)

---

## 1. Prerequisites

Complete this one-time setup before testing.

### 1.1 Run Database Migrations

```bash
php artisan migrate
```

### 1.2 Seed Permissions

```bash
php artisan db:seed --class="App\\Modules\\Holiday\\Database\\Seeders\\HolidayPermissionSeeder"
```

> **Note:** If a dedicated Holiday seeder does not exist, ensure the `holiday` module permissions are registered via the library's auto-discovery. Verify with:
> ```bash
> php artisan ui-library:discover
> ```

### 1.3 Confirm Test Data

- [ ] At least one active **Company** exists.
- [ ] At least one active **Holiday Calendar** exists with a valid year (e.g., 2026).
- [ ] At least 3–5 **Holiday** records exist in the calendar, including:
  - A holiday with `is_recurring = true`
  - A holiday with `holiday_pay_rate = 2.0` (or greater than 1.0)
  - A holiday with `affects_payroll = true`
- [ ] Test accounts are available for the required roles: `admin`, `hr_manager`, and `employee`.
- [ ] At least one employee has an active **EmployeePosition** with `pay_type = hourly` and `hourly_rate > 0`.
- [ ] A **Pay Schedule** and at least one **Payroll Run** exist (or can be created) for payroll integration testing.

### 1.4 Clear Application Cache

```bash
php artisan config:clear && php artisan cache:clear && php artisan view:clear
```

---

## 2. Admin Testing

**Context:** `/hr/holidays` (sidebar group: **Holidays**)

### 2.1 Overview Dashboard

**URL:** `/holiday/dashboard-holidays-overview`

| # | Test Action | Expected Result | Pass |
|---:|---|---|:---:|
| 1 | Log in as `admin` or `hr_manager` and open `/holiday/dashboard-holidays-overview`. | Dashboard loads with four stat cards: **Total Holidays**, **Active Holidays**, **Holiday Calendars**, and **Upcoming (30 Days)**. | [ ] |
| 2 | Verify the stat card values. | Each stat card displays a count that matches the underlying data (e.g., Total Holidays matches the count in `/holiday/holidays`). | [ ] |
| 3 | Check the **Holidays by Type** pie chart. | Chart renders with segments for `company`, `public`, and any other types present in the data. | [ ] |
| 4 | Check the **Holidays by Calendar** bar chart. | Chart renders with one bar per calendar, showing the holiday count. | [ ] |
| 5 | Check the **Holidays Added (Last 6 Months)** trend widget. | Trend line or sparkline displays monthly creation counts. | [ ] |
| 6 | Check the **Upcoming Holidays** list widget. | Lists the next 5 upcoming holidays with Name, Date, and Type columns. | [ ] |
| 7 | Click **View All** on the Upcoming Holidays list. | Navigates to `/holiday/holidays` with the DataTable filtered or showing all holidays. | [ ] |
| 8 | Check the **Holiday Calendars** list widget. | Lists up to 5 calendars with Name and Description. | [ ] |
| 9 | Click the **Add Holiday** action card. | Opens a drawer with the holiday creation form (`configKey: holiday.holiday`). | [ ] |
| 10 | Click the **Batch Create** action card. | Navigates to `/holiday/holiday-batch-creation`. | [ ] |
| 11 | Click the **Manage Calendars** action card. | Opens a drawer with the calendar creation form (`configKey: holiday.holiday_calendar`). | [ ] |

### 2.2 Holiday Calendar Management

**URL:** `/holiday/holiday-calendars`

| # | Test Action | Expected Result | Pass |
|---:|---|---|:---:|
| 1 | Select **Holiday Calendars** in the sidebar. | DataTable lists existing calendars with columns: Name, Year, Country, Holiday Count, Active, and Default. | [ ] |
| 2 | Click **Add** (or use the action card on the dashboard). | Form opens with fields: Name, Year, Description, Country Code, Region, Applicable To, Is Default, and Is Active. | [ ] |
| 3 | Create a calendar: Name = `Test Calendar 2027`, Year = 2027, Country = US, Active = Yes. | Record appears in the table with a success alert. `holiday_count` starts at 0. | [ ] |
| 4 | Edit the calendar and change the name. | Form opens with existing values; save updates the record. | [ ] |
| 5 | Delete a calendar with no holidays and confirm. | Record is soft-deleted. | [ ] |
| 6 | Attempt to delete a calendar that has holidays. | Either the delete is blocked with a warning, or the holidays are cascade-deleted (depending on DB configuration). Confirm the behavior matches expectations. | [ ] |

### 2.3 Holiday CRUD

**URL:** `/holiday/holidays`

| # | Test Action | Expected Result | Pass |
|---:|---|---|:---:|
| 1 | Select **Holidays** in the sidebar. | DataTable lists all holidays with columns: Name, Date, Calendar, Type, Recurring, Paid, Pay Rate, and Active. | [ ] |
| 2 | Filter by calendar using the dropdown filter. | Table refreshes to show only holidays in the selected calendar. | [ ] |
| 3 | Filter by holiday type (`company` vs `public`). | Table refreshes correctly. | [ ] |
| 4 | Click **Add** and create a holiday: Name = `Test Holiday`, Date = 2026-07-15, Calendar = (select one), Type = Company, Recurring = Yes, Paid = Yes, Pay Rate = 2.0, Active = Yes. | Record appears in the table with a success alert. | [ ] |
| 5 | Verify the **Pay Rate** column displays `2.0` for the new holiday. | The value is visible in the DataTable. | [ ] |
| 6 | Edit the holiday and change the pay rate to `1.5`. | Form opens with existing values; save updates the record. | [ ] |
| 7 | Delete a holiday and confirm. | Record is soft-deleted. | [ ] |
| 8 | Toggle the **Active** switch on a holiday (if available as a row action). | Holiday is deactivated/reactivated without a full page reload. | [ ] |

### 2.4 Batch Creation Wizard

**URL:** `/holiday/holiday-batch-creation`

| # | Test Action | Expected Result | Pass |
|---:|---|---|:---:|
| 1 | Select **Batch Create** in the sidebar. | Three-step wizard loads: (1) Select Calendar & Year, (2) Select Holidays, (3) Review & Confirm. | [ ] |
| 2 | **Step 1:** Select a calendar from the dropdown. | Dropdown lists all active calendars. | [ ] |
| 3 | Select a target year (e.g., 2027). | Year dropdown shows available years (2024–2028). | [ ] |
| 4 | Set **Creation Method** to `Copy from Previous Year`. | The option is selected (this is the default). | [ ] |
| 5 | Click **Next**. | Wizard advances to Step 2. | [ ] |
| 6 | **Step 2:** Review the list of available holiday templates from the previous year. | Checklist displays holidays with their names and dates. | [ ] |
| 7 | Select 2–3 holidays and click **Next**. | Wizard advances to Step 3. | [ ] |
| 8 | **Step 3:** Review the summary. | Preview shows the selected holidays with their dates shifted to the target year. An impact analysis may also be displayed. | [ ] |
| 9 | Confirm and submit. | Success message: `{count} holidays have been added to the calendar for {year}.` Action buttons appear: **View Calendar**, **View Holidays**, **Create Another Set**. | [ ] |
| 10 | Click **View Holidays**. | Navigates to `/holiday/holidays?calendar_id={id}&year={year}` showing the newly created holidays. | [ ] |
| 11 | Test the `Start Fresh` creation method. | Step 2 shows an empty template list or a country-based template selector. | [ ] |
| 12 | Test the `Use Country Template` method (if country templates are configured). | Step 2 shows holidays pre-filtered by the calendar's country code. | [ ] |

### 2.5 Holiday Detail Page

**URL:** `/holiday/holidays/{id}`

| # | Test Action | Expected Result | Pass |
|---:|---|---|:---:|
| 1 | Click the **Show** (eye icon) on any holiday in the DataTable. | Detail page opens showing all holiday fields in a read-only layout. | [ ] |
| 2 | Verify the following fields are displayed: | Name, Description, Date, Observed Date, Calendar, Holiday Type, Recurring (Yes/No), Paid Holiday (Yes/No), Affects Payroll (Yes/No), **Holiday Pay Rate**, Minimum Hours for Pay, Half Day (Yes/No), Business Impact, Country Code, Active (Yes/No). | [ ] |
| 3 | Verify the **Holiday Pay Rate** field displays the correct multiplier (e.g., `2.0x` or `2.0`). | The value matches what was entered during creation/editing. | [ ] |
| 4 | Click **Edit** from the detail page. | Edit form opens with all fields pre-populated. | [ ] |
| 5 | Click the back/return button. | Returns to the holidays DataTable with the previous filter/sort state preserved. | [ ] |

---

## 3. Payroll Integration Testing

**Purpose:** Verify that holiday pay rate multipliers are applied as payroll adjustments during payroll processing.

### 3.1 Setup

| # | Test Action | Expected Result | Pass |
|---:|---|---|:---:|
| 1 | Ensure a holiday exists with `holiday_pay_rate > 1.0` (e.g., 2.0) and `affects_payroll = true` within a date range that falls inside a payroll period. | Holiday is active and configured correctly. | [ ] |
| 2 | Ensure at least one employee has an attendance record with `status = holiday` on that holiday date. | The attendance record shows `regular_hours > 0` and `is_approved = true`. | [ ] |
| 3 | Ensure the employee has an active `EmployeePosition` with a pay type and rate (e.g., `hourly` with `hourly_rate = 25.00`). | Position is active. | [ ] |

### 3.2 Payroll Run Processing

| # | Test Action | Expected Result | Pass |
|---:|---|---|:---:|
| 1 | As a payroll admin, create a payroll run whose period includes the holiday date. | Payroll run is created with `status = draft`. | [ ] |
| 2 | Process the payroll run (either via the UI or `php artisan payroll:process {id}`). | Processing begins; `calculation_status` transitions to `processing`. | [ ] |
| 3 | Wait for `calculation_status` to become `completed`. | Run completes without errors. | [ ] |
| 4 | Open `/payroll/payroll-run-adjustments` and filter by the payroll run. | A **Holiday Pay Premium** adjustment line item appears with `type = earning`, a label containing the holiday name and pay rate (e.g., `Holiday Pay Premium - Christmas (2.0x)`), and the correct premium amount. | [ ] |
| 5 | Verify the premium amount calculation. | For an hourly employee: `holiday_hours × hourly_rate × (pay_rate − 1.0)`. Example: 8h × $25 × (2.0 − 1.0) = $200. | [ ] |
| 6 | Open the employee's payslip for this run. | The **Holiday Pay Premium** appears as an earning line item. Gross pay includes the premium. | [ ] |
| 7 | Check the employee's net pay. | Net pay = gross pay (including holiday premium) − deductions − taxes. | [ ] |

### 3.3 Edge Cases

| # | Test Action | Expected Result | Pass |
|---:|---|---|:---:|
| 1 | Create a holiday with `holiday_pay_rate = 1.0` and process a payroll run covering it. | No holiday pay premium adjustment is created (1.0x means no premium). | [ ] |
| 2 | Create a holiday with `affects_payroll = false` and process a payroll run covering it. | No holiday pay premium adjustment is created. | [ ] |
| 3 | Process the same payroll run twice (reprocess). | No duplicate holiday pay adjustments are created (idempotent). | [ ] |
| 4 | Create a half-day holiday (`is_half_day = true`, `minimum_hours_for_pay = 8`) and process payroll. | Premium is calculated on 4 hours (half of minimum), not 8. | [ ] |

---

## 4. Scheduled Command Testing

**Purpose:** Verify the `holiday:rollover` command correctly copies recurring holidays to the next year.

### 4.1 Setup

| # | Test Action | Expected Result | Pass |
|---:|---|---|:---:|
| 1 | Ensure at least one active calendar has recurring holidays (`is_recurring = true`) in the current or a test year. | Recurring holidays exist. | [ ] |
| 2 | Note the current holiday count for the target year (e.g., 2027) before running the command. | Baseline count is recorded. | [ ] |

### 4.2 Run the Command

```bash
# Roll over from 2026 to 2027
php artisan holiday:rollover --year=2026 --target-year=2027

# Or use defaults (current year → current year + 1)
php artisan holiday:rollover
```

Expected output:

```text
Rolling over recurring holidays from 2026 to 2027...
Calendars processed: 2
Holidays copied:    5
Holidays skipped:   0
Holiday year rollover completed successfully.
```

### 4.3 Verify the Results

| # | Test Action | Expected Result | Pass |
|---:|---|---|:---:|
| 1 | Run `php artisan holiday:rollover --year=2026 --target-year=2027`. | Command completes without errors and reports the number of calendars processed and holidays copied. | [ ] |
| 2 | Open `/holiday/holidays` and filter by year = 2027. | The rolled-over holidays appear with the correct dates (same month/day as source, year = 2027). | [ ] |
| 3 | Verify that non-recurring holidays from 2026 were NOT copied to 2027. | Only `is_recurring = true` holidays appear in 2027. | [ ] |
| 4 | Verify that holiday attributes are preserved: `holiday_pay_rate`, `is_paid_holiday`, `affects_payroll`, `business_impact`, `holiday_type`, `country_code`. | Rolled-over holidays have the same attribute values as their source. | [ ] |
| 5 | Run the command again with the same parameters. | Command reports `Holidays skipped: N` (where N = previously copied count) and `Holidays copied: 0`. No duplicates are created. | [ ] |
| 6 | Check the calendar's `holiday_count` on `/holiday/holiday-calendars`. | The count reflects the total active holidays in the target year. | [ ] |
| 7 | Test with a holiday on Feb 29 in a leap year (e.g., 2028 → 2029). | The rolled-over holiday appears on Feb 28, 2029 (non-leap year clamp). | [ ] |
| 8 | Verify the Laravel log (`storage/logs/laravel.log`) contains rollover entries. | Log messages include `Starting holiday year rollover`, per-calendar results, and `Holiday year rollover complete`. | [ ] |

### 4.4 Schedule Verification

| # | Test Action | Expected Result | Pass |
|---:|---|---|:---:|
| 1 | Run `php artisan schedule:list`. | `holiday:rollover` appears in the schedule list, set to run `yearlyOn(12, 1, '00:00')` (December 1st at midnight). | [ ] |

---

## 5. Quick Smoke Test

Run this checklist after each deployment. The two-minute estimate assumes the environment and test accounts are already prepared.

- [ ] `/holiday/dashboard-holidays-overview` loads without errors and displays all stat cards, charts, and list widgets.
- [ ] `/holiday/holiday-calendars` DataTable loads; create, edit, and delete actions work.
- [ ] `/holiday/holidays` DataTable loads; create, edit, delete, and view actions work.
- [ ] `/holiday/holiday-batch-creation` wizard loads; all three steps function correctly.
- [ ] `/holiday/holidays/{id}` detail page displays all fields including **Holiday Pay Rate**.
- [ ] Holiday pay rate field accepts values from 1.0 to 3.0 and displays correctly in the DataTable and detail view.
- [ ] `php artisan holiday:rollover` runs without errors and reports results.
- [ ] Payroll run processing creates holiday pay premium adjustments for eligible holidays.
- [ ] Sidebar navigation group **Holidays** appears with four items: Overview, Holiday Calendars, Holidays, Batch Create.

---

## 6. Test Completion Record

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