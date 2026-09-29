# Leave & Holiday → Attendance → Payroll — Impact Analysis

> **Date**: 2026-09-29
> **Status**: ✅ All critical and high-priority recommendations implemented (2026-09-29)
> **Scope**: End-to-end trace of how leave requests and company holidays affect attendance records, and how those attendance records subsequently affect payroll run calculations.
> **Modules**: Leave, Holiday, Attendance, Payroll

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Data Flow Overview](#2-data-flow-overview)
3. [Leave → Attendance: Two-Path Sync](#3-leave--attendance-two-path-sync)
4. [Holiday → Attendance: Aggregator-Only Path](#4-holiday--attendance-aggregator-only-path)
5. [Attendance → Payroll: How Leave/Holiday Records Are Counted](#5-attendance--payroll-how-leaveholiday-records-are-counted)
6. [Pay Type Impact Matrix](#6-pay-type-impact-matrix)
7. [Gaps & Issues Found](#7-gaps--issues-found)
8. [Recommendations](#8-recommendations)
9. [Implementation Status](#9-implementation-status)

---

## 1. Executive Summary

Leave and holidays affect payroll through a **two-hop chain**: Leave/Holiday → Attendance records → Payroll calculation. The system has two independent paths for creating leave-attendance records (proactive sync + reactive aggregator), and one path for holiday-attendance records (aggregator only). Both produce attendance records that the payroll calculator then reads.

**Key findings:**

| Finding | Severity | Detail |
|---------|----------|--------|
| `is_paid` field missing from LeaveType model | 🔴 Critical | Code references `$leaveType->is_paid` but the field doesn't exist in the model or migration — always falls back to `true` |
| Holiday model has payroll fields that are **completely ignored** | 🔴 Critical | `is_paid_holiday`, `affects_payroll`, `holiday_pay_rate`, `minimum_hours_for_pay` are defined but never read by any attendance or payroll code |
| Two independent leave→attendance paths can conflict | 🟡 Moderate | `LeaveAttendanceSync` creates records proactively; `AttendanceAggregator` also checks for leave. Both can run on the same day |
| Holiday attendance records count as "worked days" for salaried_daily | 🟡 Moderate | Holiday records have `status='holiday'` (not 'absent') and `is_approved=true`, so they pass the worked-day check in payroll |
| Leave records always use 8h standard hours | 🟢 Low | `getStandardWorkHours()` has a TODO comment and hardcodes 8.00 — ignores shift schedules |
| Half-day leave not handled in attendance sync | 🟢 Low | `LeaveRequest.is_half_day` exists but `LeaveAttendanceSync` ignores it |

---

## 2. Data Flow Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│  LEAVE MODULE                         HOLIDAY MODULE                 │
│  ────────────                         ──────────────                 │
│  LeaveRequest (Approved)              Holiday (is_active=true,       │
│    │                                  │  business_impact=office_     │
│    │                                  │  closed|reduced_staff)       │
│    │                                  │                              │
│    ├─ PATH A (proactive)              │                              │
│    │  LeaveAttendanceSync             │                              │
│    │  ::syncLeaveToAttendance()       │                              │
│    │  → Creates/updates Attendance    │                              │
│    │    with status='leave'           │                              │
│    │                                  │                              │
│    └─ PATH B (reactive) ──────────────┤                              │
│       AttendanceAggregator            │                              │
│       ::recalculateForDay()           │                              │
│       → Checks for approved leave     │                              │
│       → Checks for company holiday ◄──┤                              │
│       → Checks for clock events       │                              │
│       → Delegates to Calculator       │                              │
└──────────────────┬────────────────────┴──────────────────────────────┘
                   │
                   ▼
┌──────────────────────────────────────────────────────────────────────┐
│  ATTENDANCE RECORDS (attendances table)                              │
│  ─────────────────────────────────────                               │
│  status='leave'      ← from leave sync / aggregator                  │
│  status='holiday'    ← from aggregator only                          │
│  status='absent'     ← unplanned absence (no events, no leave)       │
│  status='present'    ← normal workday with clock events              │
│  is_approved=true    ← leave & holiday auto-approved                 │
│  is_paid_absence     ← from LeaveType.is_paid (or default true)      │
└──────────────────┬───────────────────────────────────────────────────┘
                   │
                   ▼
┌──────────────────────────────────────────────────────────────────────┐
│  PAYROLL CALCULATOR                                                  │
│  ─────────────────                                                   │
│  PayrollCalculator::getAttendanceSummary()                           │
│  → Filters: is_approved=true, date BETWEEN period                    │
│  → Counts worked_days: net_hours>0 OR status!='absent' OR is_paid    │
│  → Sums regular_hours, overtime_hours, double_time_hours             │
│                                                                      │
│  Impact by pay type:                                                 │
│  • salaried_full:  No attendance used — leave/holiday have NO effect │
│  • salaried_daily: Leave days count as worked (is_paid_absence=true) │
│                    Holiday days count as worked (status='holiday')   │
│  • hourly:         Leave days add standardHours to regular_hours     │
│                    Holiday days add 0 hours (net_hours=0)            │
└──────────────────────────────────────────────────────────────────────┘
```

---

## 3. Leave → Attendance: Two-Path Sync

### 3.1 Path A — Proactive Sync (`LeaveAttendanceSync`)

**File**: [`app/Modules/Leave/Services/LeaveAttendanceSync.php`](app/Modules/Leave/Services/LeaveAttendanceSync.php:27)

**Trigger**: Called when a leave request is approved (via workflow listener or manual trigger).

**Flow**:
1. Validates `status === 'Approved'` and `attendance_synced === false` [:30, :39](app/Modules/Leave/Services/LeaveAttendanceSync.php:30)
2. Iterates each date from `start_date` to `end_date` [:58](app/Modules/Leave/Services/LeaveAttendanceSync.php:58)
3. **Skips weekends** (`$date->isWeekend()`) [:64](app/Modules/Leave/Services/LeaveAttendanceSync.php:64)
4. **Skips company holidays** (checks `Holiday` table) [:69](app/Modules/Leave/Services/LeaveAttendanceSync.php:69)
5. For each workday, either updates existing attendance or creates new [:86–92](app/Modules/Leave/Services/LeaveAttendanceSync.php:86)

**Attendance record created/updated**:

| Field | Value | Source |
|-------|-------|--------|
| `status` | `'leave'` | Hardcoded |
| `leave_request_id` | LeaveRequest ID | From leave |
| `net_hours` | `standardHours` (8.00) | [`getStandardWorkHours()`](app/Modules/Leave/Services/LeaveAttendanceSync.php:253) — hardcoded 8.00 |
| `regular_hours` | NOT SET | **Gap** — only set in `handleLeaveAttendance`, not in `createLeaveAttendanceRecord` |
| `is_approved` | `true` | Hardcoded |
| `is_paid_absence` | `$leaveType->is_paid ?? true` | From LeaveType — **always true** (field missing) |
| `hours_deducted` | `standardHours` if `deducts_from_balance` | From LeaveType |
| `needs_review` | `false` | Hardcoded |
| `absence_type` | `'planned_leave'` | Hardcoded |

**Critical gap in `createLeaveAttendanceRecord`**: The method at line [:282](app/Modules/Leave/Services/LeaveAttendanceSync.php:282) does NOT set `regular_hours`, `overtime_hours`, or `double_time_hours`. Compare with `handleLeaveAttendance` in the Aggregator at line [:170](app/Modules/Attendance/Services/AttendanceAggregator.php:170) which DOES set `regular_hours = $standardHours`. This means leave records created via Path A have `regular_hours = 0` (the DB default), which affects hourly payroll.

### 3.2 Path B — Reactive Aggregator (`AttendanceAggregator`)

**File**: [`app/Modules/Attendance/Services/AttendanceAggregator.php`](app/Modules/Attendance/Services/AttendanceAggregator.php:35)

**Trigger**: Called when clock events are processed (`ProcessAttendanceJob`), or when attendance is manually recalculated.

**Flow**:
1. Checks for holiday first [:56](app/Modules/Attendance/Services/AttendanceAggregator.php:56)
2. Checks for approved leave second [:59](app/Modules/Attendance/Services/AttendanceAggregator.php:59)
3. Checks for clock events [:78](app/Modules/Attendance/Services/AttendanceAggregator.php:78)
4. If leave found → `handleLeaveAttendance()` [:145](app/Modules/Attendance/Services/AttendanceAggregator.php:145)

**`handleLeaveAttendance()` sets**:

| Field | Value |
|-------|-------|
| `status` | `'leave'` |
| `net_hours` | `$standardHours` (from shift or 8.00) |
| `regular_hours` | `$standardHours` ✅ |
| `overtime_hours` | `0.00` |
| `is_approved` | `true` |
| `is_paid_absence` | `$leaveType->is_paid ?? true` |
| `hours_deducted` | `$standardHours` if `deducts_from_balance` |

### 3.3 Path Conflict Risk

Both paths can operate on the same employee+date:

- **Path A** runs when leave is approved (proactive)
- **Path B** runs when clock events arrive (reactive)

If an employee clocks in on a leave day:
1. Path A already created a `status='leave'` record
2. Path B finds the approved leave and calls `handleLeaveAttendance()`, which **overwrites** the record with leave status
3. The clock events are **ignored** — the employee's actual work is not recorded

This is by design (leave takes priority over clock events), but it means an employee who works during leave won't have those hours counted unless the leave is cancelled first.

### 3.4 Leave Cancellation Flow

When leave is cancelled ([`removeLeaveAttendance()`](app/Modules/Leave/Services/LeaveAttendanceSync.php:121)):
1. Finds attendance records with `leave_request_id` matching and `status='leave'`
2. Sets `leave_request_id = null`, `status = 'pending'`, `is_approved = false`, `needs_review = true`
3. Triggers `AttendanceAggregator::recalculateForDay()` for each affected date
4. The aggregator will then re-process clock events for those days

---

## 4. Holiday → Attendance: Aggregator-Only Path

### 4.1 Holiday Detection

**File**: [`AttendanceAggregator::isCompanyHoliday()`](app/Modules/Attendance/Services/AttendanceAggregator.php:258)

A date is a holiday if:
- Exact match on `holidays.date` OR observed match on `holidays.observed_date`
- `is_active = true`
- `business_impact` is `'office_closed'` OR `'reduced_staff'`

### 4.2 Holiday Attendance Record

**File**: [`AttendanceAggregator::handleHolidayAttendance()`](app/Modules/Attendance/Services/AttendanceAggregator.php:112)

| Field | Value |
|-------|-------|
| `status` | `'holiday'` |
| `net_hours` | `0.00` |
| `regular_hours` | `0.00` |
| `overtime_hours` | `0.00` |
| `is_approved` | `true` |
| `is_paid_absence` | **NOT SET** (defaults to `true` from DB) |
| `needs_review` | `false` |

### 4.3 Holiday Model Fields That Are IGNORED

The [`Holiday`](app/Modules/Holiday/Models/Holiday.php:34) model has these payroll-relevant fields that are **never read** by any attendance or payroll code:

| Field | Default | Purpose | Used? |
|-------|---------|---------|-------|
| `is_paid_holiday` | `true` | Whether the holiday is paid | ❌ Ignored |
| `affects_payroll` | `true` | Whether holiday affects pay calculation | ❌ Ignored |
| `holiday_pay_rate` | `1.0` | Multiplier for holiday pay (e.g., 1.5× for working on holiday) | ❌ Ignored |
| `minimum_hours_for_pay` | `8` | Minimum hours credited for holiday pay | ❌ Ignored |
| `is_half_day` | `false` | Whether it's a half-day holiday | ❌ Ignored |
| `eligible_employee_types` | `['full_time','part_time']` | Which employee types qualify | ❌ Ignored |

**Impact**: All holidays are treated identically — zero hours, status='holiday'. There's no way to configure paid holidays (where employees get credited hours), holiday work pay rates, or half-day holidays.

### 4.4 Holiday Priority

Holiday check happens **before** leave check and clock event check in [`recalculateForDay()`](app/Modules/Attendance/Services/AttendanceAggregator.php:66). This means:
- If a date is a holiday, it's ALWAYS marked as holiday — even if the employee has approved leave or clocked in
- Holiday takes absolute priority over everything else

---

## 5. Attendance → Payroll: How Leave/Holiday Records Are Counted

### 5.1 The Payroll Calculator's Attendance Query

**File**: [`PayrollCalculator::getAttendanceSummary()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:130)

```php
$attendances = Attendance::withoutCompanyScope()
    ->where('employee_id', $employeeId)
    ->where('is_approved', true)        // ← Only approved records
    ->whereBetween('date', [$start, $end])
    ->get();
```

### 5.2 Worked Day Counting Logic

```php
if ($day->net_hours > 0 || $day->status !== 'absent' || $day->is_paid_absence) {
    $summary['worked_days']++;
}
```

### 5.3 How Each Status Affects Payroll

| Attendance Status | `net_hours` | `status !== 'absent'` | `is_paid_absence` | Counts as Worked? | Hours Added? |
|-------------------|-------------|----------------------|-------------------|-------------------|-------------|
| `present` | > 0 | ✅ | false | ✅ Yes | ✅ Yes |
| `leave` (paid) | 8.00 | ✅ | true | ✅ Yes | ✅ Yes (8h) |
| `leave` (unpaid) | 8.00 | ✅ | false | ✅ Yes | ✅ Yes (8h) |
| `holiday` | 0.00 | ✅ | true (default) | ✅ Yes | ❌ No (0h) |
| `absent` (unplanned) | 0.00 | ❌ | false | ❌ No | ❌ No |
| `late` | > 0 | ✅ | false | ✅ Yes | ✅ Yes |
| `half_day` | > 0 | ✅ | false | ✅ Yes | ✅ Yes |

**Key observations**:

1. **Leave days always count as worked** — regardless of `is_paid_absence`. The `status !== 'absent'` check alone is sufficient because leave status is `'leave'`, not `'absent'`.

2. **Holiday days count as worked** — `status='holiday'` passes the `status !== 'absent'` check. But they contribute 0 hours, so for hourly employees they add nothing to pay.

3. **Unpaid leave still adds hours** — even if `is_paid_absence = false`, the leave record has `net_hours = 8.00` and `regular_hours = 8.00` (from Path B), so hourly employees get paid for unpaid leave days. This is a **bug** — the `is_paid_absence` flag should prevent hours from being counted.

4. **`is_paid_absence` is redundant for worked-day counting** — the `status !== 'absent'` check already catches all leave and holiday records. The `is_paid_absence` flag only matters for distinguishing paid vs unpaid leave, but the payroll calculator doesn't use it for that purpose.

---

## 6. Pay Type Impact Matrix

### 6.1 `salaried_full`

| Scenario | Effect on Pay |
|----------|--------------|
| Employee on approved leave | **No effect** — full base_salary paid regardless |
| Company holiday | **No effect** — full base_salary paid regardless |
| Unplanned absence | **No effect** — attendance not consulted |

### 6.2 `salaried_daily`

| Scenario | Effect on Pay |
|----------|--------------|
| Employee on approved leave (paid) | Leave days count as worked days → **full pay** |
| Employee on approved leave (unpaid) | Leave days STILL count as worked days → **full pay** ⚠️ |
| Company holiday | Holiday counts as worked day → **full pay** (but 0 hours) |
| Unplanned absence | Day not counted → **pay reduced** |

**Formula**: `grossPay = (baseSalary / totalWorkdays) × workedDays`

Where `workedDays` includes leave days and holidays.

### 6.3 `hourly`

| Scenario | Effect on Pay |
|----------|--------------|
| Employee on approved leave (Path B) | `regular_hours = 8.00` added → **paid for 8h** |
| Employee on approved leave (Path A) | `regular_hours = 0` (not set) → **NOT paid** ⚠️ |
| Employee on approved leave (unpaid) | Still gets 8h regular_hours → **paid incorrectly** ⚠️ |
| Company holiday | `net_hours = 0`, `regular_hours = 0` → **not paid** |
| Unplanned absence | `net_hours = 0` → **not paid** |

---

## 7. Gaps & Issues Found

### 🔴 G1: `is_paid` Field Missing from LeaveType Model

**Code references**: [`LeaveAttendanceSync:275`](app/Modules/Leave/Services/LeaveAttendanceSync.php:275), [`AttendanceAggregator:182`](app/Modules/Attendance/Services/AttendanceAggregator.php:182)

```php
'is_paid_absence' => $leaveRequest->leaveType->is_paid ?? true,
```

The `is_paid` field is NOT in [`LeaveType::$fillable`](app/Modules/Leave/Models/LeaveType.php:33) or `$casts`. It's not in any migration. The `?? true` fallback means **all leave types are treated as paid**. There is no way to configure unpaid leave types.

**Impact**: Unpaid leave (sick leave without pay, unpaid sabbatical, etc.) cannot be configured. Employees on unpaid leave still get paid.

### 🔴 G2: Holiday Payroll Fields Completely Ignored

The [`Holiday`](app/Modules/Holiday/Models/Holiday.php:34) model has 6 payroll-relevant fields that are never read:

- `is_paid_holiday` — should control whether holiday hours are credited
- `affects_payroll` — should control whether holiday affects pay at all
- `holiday_pay_rate` — should provide a multiplier for employees who work on holidays
- `minimum_hours_for_pay` — should set minimum credited hours
- `is_half_day` — should halve the credited hours
- `eligible_employee_types` — should filter which employees get holiday pay

**Impact**: All holidays are zero-hour, unpaid events. There's no way to configure paid public holidays where employees receive their regular pay without working.

### 🟡 G3: Path A Leave Records Missing `regular_hours`

[`createLeaveAttendanceRecord()`](app/Modules/Leave/Services/LeaveAttendanceSync.php:282) creates attendance records without setting `regular_hours`, `overtime_hours`, or `double_time_hours`. These default to `0` in the database.

Compare with [`handleLeaveAttendance()`](app/Modules/Attendance/Services/AttendanceAggregator.php:170) which correctly sets `regular_hours = $standardHours`.

**Impact**: Hourly employees whose leave was synced via Path A (proactive) will have `regular_hours = 0` and receive **no pay** for leave days. Only Path B (reactive aggregator) correctly credits hours.

### 🟡 G4: Unpaid Leave Still Credits Hours

Even when `is_paid_absence = false`, the leave attendance record has `net_hours = 8.00` and `regular_hours = 8.00`. The payroll calculator's `getAttendanceSummary()` adds these hours regardless of the `is_paid_absence` flag.

The `is_paid_absence` flag is checked in the worked-day counting condition (`|| $day->is_paid_absence`) but the hours are summed unconditionally:

```php
$summary['regular_hours'] += $regular;    // ← Always added
$summary['overtime_hours'] += $overtime;  // ← Always added
```

**Impact**: Unpaid leave is functionally identical to paid leave for payroll purposes. The `is_paid_absence` flag has no effect on hour accumulation.

### 🟡 G5: Holiday Days Count as Worked for Salaried Daily

Holiday records have `status='holiday'` which passes the `status !== 'absent'` check. This means holidays count as worked days for `salaried_daily` employees, giving them full pay for holidays. While this may be desired (paid public holidays), there's no way to configure it per holiday.

### 🟢 G6: `getStandardWorkHours()` Hardcoded to 8.00

[`LeaveAttendanceSync::getStandardWorkHours()`](app/Modules/Leave/Services/LeaveAttendanceSync.php:253) has a TODO comment and always returns 8.00. It doesn't consult the employee's shift schedule or work pattern.

### 🟢 G7: Half-Day Leave Not Handled

[`LeaveRequest.is_half_day`](app/Modules/Leave/Models/LeaveRequest.php:42) and `half_day_period` exist but are never read by `LeaveAttendanceSync` or `AttendanceAggregator`. Half-day leave requests create full-day leave attendance records.

### 🟢 G8: No Holiday-Only Path in Payroll

The payroll calculator has no awareness of holidays. It treats holiday attendance records the same as any other record — counting them as worked days (for salaried_daily) but adding 0 hours (for hourly). There's no holiday pay rate logic, no minimum hours credit, and no `affects_payroll` check.

---

## 8. Recommendations

### Priority 1 — Critical (Data Integrity)

| # | Recommendation | Rationale |
|---|---------------|-----------|
| **R1** | Add `is_paid` to LeaveType model and migration | Currently all leave is paid with no way to configure unpaid leave. Add `is_paid` (boolean, default true) to `leave_types` table and `LeaveType` model. |
| **R2** | Fix `createLeaveAttendanceRecord()` to set `regular_hours` | Path A leave records are missing `regular_hours`, causing hourly employees to receive no pay for leave days synced proactively. Add `'regular_hours' => $standardHours` to the create array. |
| **R3** | Make `getAttendanceSummary()` respect `is_paid_absence` for hour counting | When `is_paid_absence = false`, hours should not be added to the summary. Add a guard: `if (!$day->is_paid_absence) { $regular = 0; $overtime = 0; $double = 0; }` before summing. |

### Priority 2 — High (Feature Gaps)

| # | Recommendation | Rationale |
|---|---------------|-----------|
| **R4** | Implement holiday pay logic using existing Holiday model fields | Read `is_paid_holiday`, `holiday_pay_rate`, `minimum_hours_for_pay`, `is_half_day` in `handleHolidayAttendance()`. For paid holidays, set `net_hours = minimum_hours_for_pay` and `regular_hours = minimum_hours_for_pay`. |
| **R5** | Add `affects_payroll` check in payroll calculator | Skip holiday records where `affects_payroll = false` in `getAttendanceSummary()`. |
| **R6** | Implement half-day leave handling | Read `LeaveRequest.is_half_day` and `half_day_period` in both `LeaveAttendanceSync` and `AttendanceAggregator`. Halve the `standardHours` for half-day leave. |

### Priority 3 — Medium (Robustness)

| # | Recommendation | Rationale |
|---|---------------|-----------|
| **R7** | Implement `getStandardWorkHours()` using shift schedule | Replace the hardcoded 8.00 with actual shift duration from the employee's assigned shift or work pattern. |
| **R8** | Add `eligible_employee_types` filtering for holidays | Check the employee's type against `Holiday.eligible_employee_types` before marking attendance as holiday. |
| **R9** | Document the two-path leave sync in user-facing docs | Update [`docs/user/leave/README.md`](docs/user/leave/README.md) and [`docs/user/attendance/README.md`](docs/user/attendance/README.md) to explain the leave→attendance→payroll chain. |

---

## Appendix A: Complete Field Mapping

### LeaveType → Attendance Fields

| LeaveType Field | Attendance Field | Set By |
|----------------|-----------------|--------|
| `deducts_from_balance` | `hours_deducted` | Both paths |
| `is_paid` (MISSING) | `is_paid_absence` | Both paths (falls back to `true`) |
| *(hardcoded)* | `status = 'leave'` | Both paths |
| *(hardcoded)* | `is_approved = true` | Both paths |
| *(hardcoded)* | `needs_review = false` | Both paths |

### Holiday → Attendance Fields

| Holiday Field | Attendance Field | Used? |
|--------------|-----------------|-------|
| *(hardcoded)* | `status = 'holiday'` | ✅ |
| *(hardcoded)* | `net_hours = 0` | ✅ |
| `is_paid_holiday` | *(should set net_hours)* | ❌ Ignored |
| `holiday_pay_rate` | *(should set pay multiplier)* | ❌ Ignored |
| `minimum_hours_for_pay` | *(should set minimum hours)* | ❌ Ignored |
| `is_half_day` | *(should halve hours)* | ❌ Ignored |
| `affects_payroll` | *(should gate payroll inclusion)* | ❌ Ignored |
| `eligible_employee_types` | *(should filter eligibility)* | ❌ Ignored |

### Attendance → Payroll Fields

| Attendance Field | Payroll Use | Pay Types Affected |
|-----------------|------------|-------------------|
| `status` | Worked-day counting (`!== 'absent'`) | `salaried_daily` |
| `net_hours` | Worked-day counting (`> 0`) | `salaried_daily` |
| `regular_hours` | Hourly regular pay | `hourly` |
| `overtime_hours` | Hourly overtime pay | `hourly` |
| `double_time_hours` | Hourly double-time pay | `hourly` |
| `is_paid_absence` | Worked-day counting (OR condition) | `salaried_daily` |
| `is_approved` | Record inclusion filter | `salaried_daily`, `hourly` |

---

## 9. Implementation Status

All critical and high-priority recommendations were implemented on 2026-09-29. 77 tests pass with 0 regressions.

# | Recommendation | Status | Files Changed |
|---|---------------|--------|---------------|
**R1** | Add `is_paid` to LeaveType | ✅ Done | Migration, [`LeaveType.php`](app/Modules/Leave/Models/LeaveType.php), [`leave_type.php`](app/Modules/Leave/Data/leave_type.php) data config |
**R2** | Fix `createLeaveAttendanceRecord()` to set `regular_hours` | ✅ Done | [`LeaveAttendanceSync.php`](app/Modules/Leave/Services/LeaveAttendanceSync.php) — both create and update methods |
**R3** | Make `getAttendanceSummary()` respect `is_paid_absence` | ✅ Done | [`PayrollCalculator.php`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php) — skips hours for unpaid leave/absence, keeps worked-day count |
**R4** | Implement holiday pay logic | ✅ Done | [`AttendanceAggregator.php`](app/Modules/Attendance/Services/AttendanceAggregator.php) — reads `is_paid_holiday`, `minimum_hours_for_pay`, `is_half_day` |
**R5** | Add `affects_payroll` check | ✅ Done | [`PayrollCalculator.php`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php) — skips records where `affects_payroll === false` |
**R6** | Implement half-day leave | ✅ Done | [`LeaveAttendanceSync.php`](app/Modules/Leave/Services/LeaveAttendanceSync.php) + [`AttendanceAggregator.php`](app/Modules/Attendance/Services/AttendanceAggregator.php) |
**R7** | Implement `getStandardWorkHours()` from shift | ✅ Done | [`LeaveAttendanceSync.php`](app/Modules/Leave/Services/LeaveAttendanceSync.php) — reads from employee's shift schedule |
**R8** | `eligible_employee_types` filtering | ⏳ Future | Not yet implemented |
**R9** | Document leave→attendance→payroll chain | ✅ Done | [`leave/README.md`](docs/user/leave/README.md), [`attendance/README.md`](docs/user/attendance/README.md), [`payroll/README.md`](docs/user/payroll/README.md) |
