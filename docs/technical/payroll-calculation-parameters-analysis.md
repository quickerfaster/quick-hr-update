# Payroll Calculation Parameters — Comprehensive Analysis Report

> **Date**: 2026-09-28
> **Analyst**: Technical analysis of `calculateForEmployee()` and all downstream dependencies
> **Scope**: How `pay_type` on `employee_positions` and all associated parameters affect payroll calculation output

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Pay Type Analysis with Code Traces](#2-pay-type-analysis-with-code-traces)
   - [2.1 `salaried_full`](#21-salaried_full)
   - [2.2 `salaried_daily`](#22-salaried_daily)
   - [2.3 `hourly`](#23-hourly)
   - [2.4 Attendance Integration Toggle Override](#24-attendance-integration-toggle-override)
3. [Complete Parameter Inventory](#3-complete-parameter-inventory)
4. [Documentation Gap Analysis](#4-documentation-gap-analysis)
5. [Code-vs-Documentation Discrepancies](#5-code-vs-documentation-discrepancies)
6. [Prioritized Recommendations](#6-prioritized-recommendations)

---

## 1. Executive Summary

The [`PayrollCalculator::calculateForEmployee()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:897) method is the central payroll computation engine. It branches on `pay_type` from `employee_positions`, reads attendance data, work patterns, policies, adjustments, and tax bands to produce a payslip. The method (~290 lines, lines 897–1183) follows a clear 9-step pipeline.

**Key findings:**

| Finding | Severity | Detail |
|---------|----------|--------|
| Jurisdiction blocking is documented but **not implemented** | 🔴 Critical | Docs say US/UK/EU blocks daily deductions; code has no jurisdiction check |
| `pay_frequency` on EmployeePosition is read for tax annualisation but **never used** for base salary proration | 🟡 Moderate | Base salary is always treated as "per period" regardless of frequency; only tax calculation uses `pay_frequency` |
| `salary_currency` on EmployeePosition is **not read** by the calculator | 🟡 Moderate | Only `PayrollRun.base_currency` is used for the payslip currency |
| `is_approved` on attendance records is **not filtered** by the payroll calculator | 🟡 Moderate | Unapproved attendance records could inflate `worked_days` and hours |
| `net_hours` fallback in `getAttendanceSummary()` when breakdown fields are all zero | 🟢 Low | Uses `net_hours` as `regular_hours` — reasonable but undocumented |
| Double-time multiplier fallback chain has a subtle gap | 🟢 Low | Policy → config fallback works, but policy resolution could return null |

---

## 2. Pay Type Analysis with Code Traces

### 2.1 `salaried_full`

**Code path**: [`PayrollCalculator.php:923–926`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:923)

```php
if ($effectivePayType === 'salaried_full') {
    $grossPay = $baseSalary;
    $regularPay = $baseSalary;
}
```

**Data used:**
| Field | Source | Line |
|-------|--------|------|
| `base_salary` | `EmployeePosition` | [:902](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:902) |

**What it does NOT use:**
- No attendance data (no [`getAttendanceSummary()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:130) call)
- No work pattern (no [`resolveWorkPatternId()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:211) call)
- No `hourly_rate` (field is ignored)
- No overtime calculations
- No proration based on days worked

**Behavior**: The employee's `base_salary` is used as-is for the period. No adjustments for absences, partial periods, or extra hours. This is appropriate for fixed-salary employees who receive a guaranteed period amount regardless of days worked.

**Documented?** Yes — [`docs/user/payroll/README.md:330–338`](docs/user/payroll/README.md:330).

---

### 2.2 `salaried_daily`

**Code path**: [`PayrollCalculator.ph:p937–944`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:937)

```php
if ($effectivePayType === 'salaried_daily) {
    $workPatternId = $this->resolveWorkPatternId($position);
    $totalWorkdays = $this->getWorkdaysInPeriod($periodStart, $periodEnd, $workPatternId);
    $dailyRate = $totalWorkdays > 0 ? $baseSalary / $totalWorkdays : 0;
    $workedDays = $attendanceSummary['worked_days'] ?? 0;
    $grossPay = $dailyRate * $workedDays;
    $regularPay = $grossPay;
}
```

#### 2.2.1 Daily Rate Computation

**Formula**: `daily_rate = base_salary ÷ total_workdays_in_period`

`total_workdays_in_period` is determined by [`getWorkdaysInPeriod()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:168):

1. **Work pattern exists** → counts days in `[period_start, period_end]` where `Carbon::dayOfWeek` matches the pattern's [`applicable_days`](app/Modules/Attendance/Models/WorkPattern.php:52) array (1=Mon through 7=Sun).
2. **No work pattern** → counts weekdays (Mon–Fri) using `Carbon::isWeekday()`.

**Work pattern resolution chain** ([`resolveWorkPatternId()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:211)):
1. [`EmployeeWorkPattern`](app/Modules/Attendance/Models/EmployeeWorkPattern.php) — employee-specific assignment overlapping payroll period
2. Default [`WorkPattern`](app/Modules/Attendance/Models/WorkPattern.php) with `is_default=true`, `is_active=true`, scoped to employee's company

#### 2.2.2 Worked Days Counting

From [`getAttendanceSummary()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:130):

```php
if ($day->net_hours > 0 || $day->status !== 'absent' || $day->is_paid_absence) {
    $summary['worked_days']++;
}
```

A day counts as "worked" if **any** of:
- `net_hours > 0` (any payable hours)
- `status !== 'absent'` (includes late, half_day, incomplete, early_departure, present, unscheduled, holiday, leave)
- `is_paid_absence === true` (paid leave days)

**Important**: There is **no filter** for `is_approved`. Unapproved attendance records with net_hours > 0 or non-absent status would still count as worked days and contribute hours to the payroll calculation.

#### 2.2.3 Jurisdiction Blocking (DOCUMENTED BUT NOT IMPLEMENTED)

The user documentation at [`docs/user/payroll/README.md:353`](docs/user/payroll/README.md:353) states:

> **Jurisdiction note**: For US, UK, and EU jurisdictions, daily deductions are **blocked** — salaried daily employees are treated as salaried full.

**This is NOT implemented in code.** There is no jurisdiction check anywhere in [`calculateForEmployee()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:897) or related methods. The only path that converts `salaried_daily` to `salaried_full` is the attendance integration toggle at line [:913](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:913):

```php
$effectivePayType = $attendanceEnabled ? $payType : 'salaried_full';
```

The `country_code` exists on `Location`, `Company`, and `PayrollPolicy` models but is never consulted for jurisdiction-based blocking in the payroll calculation path.

---

### 2.3 `hourly`

**Code path**: [`PayrollCalculator.php:950–962`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:950)

```php
} elseif ($effectivePayType === 'hourly') {
    $regularHours = $attendanceSummary['regular_hours'] ?? 0;
    $overtimeHours = $attendanceSummary['overtime_hours'] ?? 0;
    $doubleTimeHours = $attendanceSummary['double_time_hours'] ?? 0;

    $attendancePolicy = $this->getAttendancePolicyForEmployee($position);
    $overtimeMultiplier = $attendancePolicy->overtime_multiplier ?? config('quick_hr_payroll.default_overtime_multiplier', 1.5);
    $doubleTimeMultiplier = $attendancePolicy->double_time_multiplier ?? config('quick_hr_payroll.default_double_time_multiplier', 2.0);

    $regularPay = $regularHours * $hourlyRate;
    $overtimePay = ($overtimeHours * $hourlyRate * $overtimeMultiplier)
                 + ($doubleTimeHours * $hourlyRate * $doubleTimeMultiplier);
    $grossPay = $regularPay + $overtimePay;
}
```

#### 2.3.1 Hours Source

All hour values come from [`getAttendanceSummary()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:130), which reads [`Attendance`](app/Modules/Attendance/Models/Attendance.php:40):

| Payroll Field | Attendance DB Column | How Computed |
|---------------|---------------------|---------------|
| `regular_hours` | `regular_hours` | By [`AttendanceCalculator::calculateOvertime()`](app/Modules/Attendance/Services/AttendanceCalculator.php:690) — daily threshold (default 8h) + weekly overflow (default 40h) |
| `overtime_hours` | `overtime_hours` | Hours beyond daily threshold + weekly overflow, capped at `max_daily_overtime_hours` |
| `double_time_hours` | `double_time_hours` | Hours beyond `double_time_threshold_hours` (default 12h) |

**Fallback**: If `regular_hours`, `overtime_hours`, and `double_time_hours` are all zero but `net_hours > 0`, the summary uses `net_hours` as `regular_hours` ([line 152–154](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:152)).

#### 2.3.2 Overtime Multiplier Resolution Chain

1. **Attendance policy** → `overtime_multiplier` / `double_time_multiplier` from the policy resolved by 6-tier chain
2. **Config fallback** → [`quick_hr_payroll.default_overtime_multiplier`](app/Modules/Payroll/Config/quick_hr_payroll.php:54) (default `1.5`) / [`default_double_time_multiplier`](app/Modules/Payroll/Config/quick_hr_payroll.php:55) (default `2.0`)

**6-tier policy resolution** ([`getAttendancePolicyForEmployee()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:253)):
1. Employee-specific (`EmployeePosition.attendance_policy_id`)
2. Shift (via `PolicyAssignment`)
3. Department (via `PolicyAssignment`)
4. Location (via `PolicyAssignment`)
5. Company (via `PolicyAssignment`)
6. System default (`is_default=true, is_active=true`)

**Potential gap**: If `getAttendancePolicyForEmployee()` returns `null`, the `??` operator at lines [:957–958](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:957) would attempt `$attendancePolicy->overtime_multiplier ?? ...` which would throw a `Call to a member function on null` error. The fallback `??` is on the *property*, not the object itself.

#### 2.3.3 `hourly_rate` Source

From [`EmployeePosition.hourly_rate`](app/Modules/Hr/Models/EmployeePosition.php:47), cast to `decimal:2`, default `0`.

---

### 2.4 Attendance Integration Toggle Override

The [`isAttendanceIntegrationEnabled()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:121) method reads:

```php
return (bool) config('quick_hr_payroll.attendance_integration.enabled', true);
```

This controls the effective pay type at line [:913](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:913):

| Config Value | Effective Pay Type | Behavior |
|-------------|-------------------|----------|
| `true` (default) | Uses actual `pay_type` | Full pay type branching |
| `false` | Forces `salaried_full` | All employees treated as fixed salary |

When disabled, ALL employees get their `base_salary` as-is, regardless of their assigned `pay_type`, `hourly_rate`, or attendance data.

---

## 3. Complete Parameter Inventory

### 3.1 Employee Position Parameters

| Parameter | DB Column | Used By | Pay Types Affected | Documented? |
|-----------|-----------|---------|-------------------|-------------|
| `pay_type` | `employee_positions.pay_type` | `calculateForEmployee()` branch [:900](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:900) | All | ✅ [§8](docs/user/payroll/README.md:328) |
| `base_salary` | `employee_positions.base_salary` | Gross pay base [:902](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:902), daily rate divisor [:941](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:941), tax base [:498](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:498) | All | ✅ [§3.3](docs/user/payroll/README.md:122) |
| `hourly_rate` | `employee_positions.hourly_rate` | Hourly pay calculation [:960–961](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:960) | `hourly` only | ✅ [§3.3](docs/user/payroll/README.md:123) |
| `pay_frequency` | `employee_positions.pay_frequency` | Tax annualisation factor [:502](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:502) | All (tax only) | ⚠️ Partial — documented as tax input but not clarified it's tax-only |
| `salary_currency` | `employee_positions.salary_currency` | **NOT READ** by calculator | None | ❌ N/A (unused field) |
| `employment_status` | `employee_positions.employment_status` | Eligibility filter [:45](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:45) | All | ✅ [§3.2](docs/user/payroll/README.md:114) |
| `attendance_policy_id` | `employee_positions.attendance_policy_id` | 6-tier policy resolution (Priority 1) [:180](app/Modules/Attendance/Services/AttendanceCalculator.php:180) | `hourly`, `salaried_daily` | ✅ [attendance-system.md:156](docs/technical/attendance-system.md:156) |
| `shift_id` | `employee_positions.shift_id` | Schedule resolution [:382](app/Modules/Attendance/Services/AttendanceCalculator.php:382), policy assignment matching [:426](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:426) | `hourly`, `salaried_daily` | ✅ [attendance-system.md:128](docs/technical/attendance-system.md:128) |
| `department_id` | `employee_positions.department_id` | Policy assignment matching [:423](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:423) | All | ✅ [§7.4](docs/user/payroll/README.md:294) |
| `location_id` | `employee_positions.location_id` | Policy assignment matching [:420](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:420), country_code for global policies [:457](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:457) | All | ⚠️ Partial — location's country_code role in global policy filtering not documented |
| `company_id` | `employee_positions.company_id` | Policy assignment matching [:418](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:418), company scoping | All | ✅ [§7.4](docs/user/payroll/README.md:294) |

### 3.2 Attendance Parameters

| Parameter | DB Column / Source | Used By | Pay Types Affected | Documented? |
|-----------|-------------------|---------|-------------------|-------------|
| `regular_hours` | `attendances.regular_hours` | Hourly regular pay [:951](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:951) | `hourly` | ✅ [attendance-system.md:98](docs/technical/attendance-system.md:98) |
| `overtime_hours` | `attendances.overtime_hours` | Hourly overtime pay [:952](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:952) | `hourly` | ✅ [attendance-system.md:99](docs/technical/attendance-system.md:99) |
| `double_time_hours` | `attendances.double_time_hours` | Hourly double-time pay [:953](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:953) | `hourly` | ✅ [attendance-system.md:100](docs/technical/attendance-system.md:100) |
| `net_hours` | `attendances.net_hours` | Worked day counting [:145](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:145), fallback for regular_hours [:152](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:152) | `salaried_daily`, `hourly` | ✅ [attendance-system.md:97](docs/technical/attendance-system.md:97) |
| `status` | `attendances.status` | Worked day counting (status !== 'absent') [:145](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:145) | `salaried_daily` | ⚠️ Partial — which statuses count as "worked" is not documented for payroll |
| `is_paid_absence` | `attendances.is_paid_absence` | Worked day counting [:145](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:145) | `salaried_daily` | ⚠️ Not documented in payroll context |
| `is_approved` | `attendances.is_approved` | **NOT FILTERED** — gap | `salaried_daily`, `hourly` | ❌ Gap — unapproved records can affect payroll |
| `date` | `attendances.date` | Date range filter [:134](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:134) | `salaried_daily`, `hourly` | ✅ Implicit (attendance within period) |
| `employee_id` | `attendances.employee_id` | Employee filter [:133](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:133) | `salaried_daily`, `hourly` | ✅ Implicit |

### 3.3 Work Pattern Parameters

| Parameter | DB Column / Source | Used By | Pay Types Affected | Documented? |
|-----------|-------------------|---------|-------------------|-------------|
| `applicable_days` | `work_patterns.applicable_days` | Workday counting [`:177–189](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:177) | `salaried_daily` | ✅ [attendance-system.md:126](docs/technical/attendance-system.md:126) |
| `is_default` | `work_patterns.is_default` | Fallback pattern selection [:229](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:229) | `salaried_daily` | ✅ [attendance-system.md:277](docs/technical/attendance-system.md:277) |
| `is_active` | `work_patterns.is_active` | Pattern eligibility [:230](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:230) | `salaried_daily` | ✅ Implicit |
| `effective_date` | `work_patterns.effective_date` | Date range eligibility [:231](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:231) | `salaried_daily` | ✅ Implicit |
| `end_date` | `work_patterns.end_date` | Date range eligibility [:233](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:233) | `salaried_daily` | ✅ Implicit |
| `company_id` | `work_patterns.company_id` | Company scoping [:236](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:236) | `salaried_daily` | ✅ Implicit |

### 3.4 Payroll Policy Parameters

| Parameter | DB Column / Source | Used By | Pay Types Affected | Documented? |
|-----------|-------------------|---------|-------------------|-------------|
| `type` | `payroll_policies.type` | Policy processing branch (tax vs non-tax) [:501](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:501) | All | ✅ [§7.1](docs/user/payroll/README.md:264) |
| `effect` | `payroll_policies.effect` | Item type determination (addition→earning, else→deduction) [:1114](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:1114) | All | ⚠️ Not explicitly documented |
| `calculation_logic` | `payroll_policies.calculation_logic` (JSON) | Amount computation: `calculation_type`, `employee_value`, `employer_value`, `base`, `bands` [:491–550](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:491) | All | ✅ [§7.2](docs/user/payroll/README.md:278) |
| `calculation_logic.base` | JSON key `base` | Policy base: `base_salary` or `gross_pay` [:497](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:497) | All | ✅ [§7.3](docs/user/payroll/README.md:284) |
| `calculation_logic.bands` | JSON key `bands` | Tax bracket array (start, end, rate) [:507](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:507) | All (tax policies) | ✅ [§8.4](docs/user/payroll/README.md:370) |
| `country_code` | `payroll_policies.country_code` | Global policy filtering [:467](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:467) | All | �️ Not explicitly documented |
| `state_code` | `payroll_policies.state_code` | Global policy filtering [:470](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:470) | ❌ Not documented |
| `effective_date` | `payroll_policies.effective_date` | Policy eligibility [:327](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:327) | All | ✅ Implicit |
| `expiry_date` | `payroll_policies.expiry_date` | Policy eligibility [:328](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:328) | All | ✅ Implicit |
| `is_active` | `payroll_policies.is_active` | Policy eligibility [:460](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:460) | All | ✅ Implicit |
| `parent_policy_id` | `payroll_policies.parent_policy_id` | Policy inheritance chain [:346](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:346) | All | ✅ [§7.5](docs/user/payroll/README.md:304) |
| `employer_ratio` | `payroll_policies.employer_ratio` | Inherited in `resolveEffectivePolicy()` [:363](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:363) | All | �️ Not explicitly documented |

### 3.5 Policy Assignment Parameters

| Parameter | DB Column / Source | Used By | Pay Types Affected | Documented? |
|-----------|-------------------|---------|-------------------|-------------|
| `assignable_type` | `payroll_policy_assignments.assignable_type` | Polymorphic target: Company, Location, Department, Shift, EmployeeGroup [:402–407](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:402) | All | ✅ [§7.4](docs/user/payroll/README.md:294) |
| `assignable_id` | `payroll_policy_assignments.assignable_id` | Specific entity ID [:418–431](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:418) | All | ✅ Implicit |
| `priority` | `payroll_policy_assignments.priority` | Sort order (descending) [:437](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:437) | All | �️ Partially — mentioned but priority rule not explained |
| `effective_date` | `payroll_policy_assignments.effective_date` | Date range eligibility [:433](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:433) | All | ✅ Implicit |
| `expiry_date` | `payroll_policy_assignments.expiry_date` | Date range eligibility [:435](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:435) | All | ✅ Implicit |

### 3.6 Overtime Multplier Parameters

| Parameter | Source | Where Set | Fallback Chain | Documented? |
|-----------|--------|----------|---------------|-------------|
| `overtime_multiplier` | `attendance_policies.overtime_multiplier` (default: 1.5) | AttendancePolicy model [:68](app/Modules/Attendance/Models/AttendancePolicy.php:68) | Policy value → config `default_overtime_multiplier` (1.5) | ✅ [attendance-system.md:241](docs/technical/attendance-system.md:241) |
| `double_time_multiplier` | `attendance_policies.double_time_multiplier` (default: 2.0) | AttendancePolicy model [:69](app/Modules/Attendance/Models/AttendancePolicy.php:69) | Policy value → config `default_double_time_multiplier` (2.0) | ✅ [attendance-system.md:243](docs/technical/attendance-system.md:243) |

### 3.7 Overtime Threshold Parameters

Set on [`AttendancePolicy`](app/Modules/Attendance/Models/AttendancePolicy.php:34) and used by [`AttendanceCalculator::calculateOvertime()`](app/Modules/Attendance/Services/AttendanceCalculator.php:690), not directly by PayrollCalculator. They affect what values end up in `regular_hours`, `overtime_hours`, `double_time_hours`.

| Parameter | Default | Description |
|-----------|---------|-------------|
| `overtime_daily_threshold_hours` | 8 | Hours after which daily overtime begins |
| `overtime_weekly_threshold_hours` | 40 | Cumulative regular hours after which weekly overtime begins |
| `max_daily_overtime_hours` | 4 | Hard cap on daily overtime |
| `double_time_threshold_hours` | 12 | Hours after which double time applies |

All documented in [attendance-system.md:241–243](docs/technical/attendance-system.md:241).

### 3.8 Adjustment Parameters

| Parameter | Source | Used By | Documented? |
|-----------|--------|---------|-------------|
| `type` | `employee_adjustment_profiles.type` (earning/deduction) | Item type [:1023](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:1023) | ✅ [§7.7](docs/user/payroll/README.md:316) |
| `label` | `employee_adjustment_profiles.label` | Line item label [:1024](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:1024) | ✅ Implicit |
| `value` | `employee_adjustment_profiles.value` | Amount or percentage [:1020–1022](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:1020) | ✅ [§7.7](docs/user/payroll/README.md:316) |
| `calculation_type` | `employee_adjustment_profiles.calculation_type` (percentage/fixed) | How `value` is applied [:1020](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:1020) | �️ Not explicitly documented for recurring adjustments |
| `policy_id` | `employee_adjustment_profiles.policy_id` | Policy override vs standalone [:1002](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:1002) | ✅ [§7.7](docs/user/payroll/README.md:320) |
| `is_active` | `employee_adjustment_profiles.is_active` | Eligibility [:992](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:992) | ✅ Implicit |
| `effective_date` / `expiry_date` | `employee_adjustment_profiles.effective_date` / `expiry_date` | Date range eligibility [:993–996](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:993) | ✅ Implicit |

**One-time adjustments** ([`PayrollRunAdjustment`](app/Modules/Payroll/Models/PayrollRunAdjustment.php:34)):

| Parameter | Field | Effect |
|-----------|-------|--------|
| `type` | bonus/commission/reimbursement/deduction/correction | Determines if earning or deduction [:1038–1055](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:1038) |
| `amount` | Signed decimal | Added to (positive) or subtracted from (negative) gross [:1036](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:1036) |
| `label` | String | Line item description [:1056](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:1056) |

### 3.9 Configuration Parameters

| Parameter | Config Key | Default | Effect |
|-----------|------------|---------|--------|
| Attendance integration toggle | `quick_hr_payroll.attendance_integration.enabled` | `true` | Disables all attendance-based calculation when `false` |
| Default OT multiplier | `quick_hr_payroll.default_overtime_multiplier` | `1.5` | Fallback when policy has no multiplier |
| Default DT multiplier | `quick_hr_payroll.default_double_time_multiplier` | `2.0` | Fallback when policy has no double-time multiplier |
| Default proration basis | `quick_hr_payroll.default_proration_basis` | `calendar` | **NOT READ** by calculateForEmployee() — defined in config but unused |
| Pay periods per year | `quick_hr_payroll.pay_periods_per_year` | Monthly=12, Semi-monthly=24, Bi-weekly=26, Weekly=52, Daily=260 | Tax annualisation via `HasPayPeriods` trait |
| Batch size | `quick_hr_payroll.batch_size` | `100` | Employees per chunk |

### 3.10 Payroll Run Parameters

| Parameter | Source | Effect |
|-----------|--------|--------|
| `period_start` / `period_end` | `payroll_runs` | Date range for attendance queries [:134](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:134) and proration [:1079](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:1079) |
| `base_currency` | `payroll_runs.base_currency` | Payslip currency code [:1164](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:1164) |
| `pay_schedule_id` | `payroll_runs.pay_schedule_id` | Employee eligibility filter (via `employee_payroll_profiles`) [:43](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:43) |
| `is_multi_company` | `payroll_runs.is_multi_company` | Route to `calculateMultiCompany()` [:35](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:35) |

### 3.11 Employee Payroll Profile Parameters

| Parameter | Field | Effect |
|-----------|-------|--------|
| `pay_schedule_id` | `employee_payroll_profiles.pay_schedule_id` | Links employee to pay schedule for eligibility [:43](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:43) |
| `is_active` | `employee_payroll_profiles.is_active` | Must be `1` for inclusion [:44](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:44) |

---

## 4. Documentation Gap Analysis

### 4.1 Parameters Documented

The following are well-documented across existing docs:

| Parameter | [payroll README](docs/user/payroll/README.md) | [attendance-system.md](docs/technical/attendance-system.md) | [clockin-analysis](docs/technical/clockin-attendance-payroll-analysis.md) |
|-----------|------|------|------|
| `pay_type` branching | §8.1–8.3 | — | §7 |
| `base_salary` | §3.3, §8 | — | — |
| `hourly_rate` | §3.3, §8.3 | — | — |
| Attendance hour fields | — | §2 (Attendance Record) | §6.2 |
| Work patterns | §8.2 | §4.2 | — |
| Policy types & calculation | §7.1–7.3 | — | — |
| Policy assignments | §7.4 | — | — |
| Policy inheritance | §7.5 | — | — |
| Tax calculation | §8.4 | — | — |
| Attendance integration toggle | §8.5 | §1 | §7 |
| Overtime thresholds & multipliers | — | §4.1 | §6.2 |
| Policy resolution (6-tier) | — | §2 | §6.2 |

### 4.2 Parameters NOT Documented or Under-Documented

| Parameter | Where Missing | Gap Description | Priority |
|-----------|--------------|-----------------|----------|
| Jurisdiction blocking | [payroll README §8.2](docs/user/payroll/README.md:353) | **Documented but not implemented in code** — the docs claim US/UK/EU block daily deductions, but no such code exists | 🔴 Critical |
| `is_approved` filtering | All docs | Payroll calculator does not filter for `is_approved=true` on attendance records. Unapproved records with hours could inflate payroll. | 🔴 Critical |
| `pay_frequency` scope | [payroll README §3.3](docs/user/payroll/README.md:125) | Documented as a position field but not clarified that it's used ONLY for tax annualisation, not for base salary proration | 🟡 Moderate |
| `salary_currency` unused | [payroll README §3.3](docs/user/payroll/README.md) | Field exists on EmployeePosition but is never read by PayrollCalculator. Only `PayrollRun.base_currency` is used. | 🟡 Moderate |
| `net_hours` fallback behavior | All docs | When breakdown fields are all zero, `net_hours` is used as `regular_hours` — undocumented fallback | 🟡 Moderate |
| `is_paid_absence` in payroll context | All docs | The field exists in attendance docs but its role in payroll worked-day counting is not documented | 🟡 Moderate |
| Which attendance statuses count as "worked" | [payroll README §8.2](docs/user/payroll/README.md:342) | The docs mention "worked days (from approved attendance)" but the code counts days with `status !== 'absent'` — which includes late, half_day, incomplete, early_departure, unscheduled, holiday, leave | 🟡 Moderate |
| `country_code` on PayrollPolicy | All docs | Used for global policy filtering but not documented | 🟢 Low |
| `state_code` on PayrollPolicy | All docs | Used for global policy filtering but not documented | 🟢 Low |
| `default_proration_basis` config | All docs | Defined in config but **not read** by calculator | 🟢 Low |
| `calculation_type` on EmployeeAdjustmentProfile | [payroll README §7.7](docs/user/payroll/README.md:316) | The `percentage` vs `fixed` distinction is documented for standalone adjustments but not that it overrides policy logic | 🟢 Low |
| `effect` field on PayrollPolicy | All docs | Only implicitly understood as addition/subtraction; not documented in its own right | 🟢 Low |
| Overtime multiplier null-object risk | All code/docs | If `getAttendancePolicyForEmployee()` returns null, the `??` chain on property access would throw an error | 🟢 Low |
| Proration factor computation | All docs | `totalDays = period_end.diffInDays(period_start) + 1` and `activeDays / totalDays` is not documented | 🟢 Low |

---

## 5. Code-vs-Documentation Discrepancies

### 5.1 🔴 Jurisdiction Blocking — Docs Say Yes, Code Says No

**Documentation** ([`docs/user/payroll/README.md:353`](docs/user/payroll/README.md:353)):
> **Jurisdiction note**: For US, UK, and EU jurisdictions, daily deductions are **blocked** — salaried daily employees are treated as salaried full.

**Code reality**: There is zero jurisdiction-checking code in the payroll calculation path. The `country_code` exists on `Location`, `Company`, and `PayrollPolicy` models but [`calculateForEmployee()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:897) never reads or checks it for jurisdiction blocking.

**Impact**: If a US/UK/EU company has `salaried_daily` employees and `PAYROLL_ATTENDANCE_INTEGRATION_ENABLED=true`, those employees will have their pay prorated by worked days — which the documentation says should be blocked. This is either:
1. A missing feature that needs implementation (code is wrong), or
2. An incorrect documentation statement that should be removed (docs are wrong)

**Recommendation**: Clarify intent with stakeholders. If blocking is desired, implement it. If not, remove the documentation claim.

### 5.2 🟡 "Approved Attendance" Claimed, Not Enforced

**Documentation** ([`docs/user/payroll/README.md:345`](docs/user/payroll/README.md:345)):
```
Base Pay = Daily Rate × Worked Days (from approved attendance)
```

**Code reality** ([`getAttendanceSummary()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:130)):
- Queries all attendance records within the date range
- No `->where('is_approved', true)` filter
- Counts days with `net_hours > 0 || status !== 'absent' || is_paid_absence`

**Impact**: Unapproved attendance records (including `needs_review=true` records with violations) can contribute hours and worked days to payroll.

### 5.3 🟡 `pay_frequency` Documented but Scope Ambiguous

**Documentation** ([`docs/user/payroll/README.md:125`](docs/user/payroll/README.md:125)):
> `pay_frequency` — Monthly, Semi-monthly, Bi-weekly, Weekly, Daily

**Code reality**: `pay_frequency` is used **only** for tax annualisation in [`applyPolicyLogic()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:502). It is NOT used for base salary proration. A monthly employee and a weekly employee with the same `base_salary` would get the same gross pay in a monthly payroll run (for `salaried_full`).

**Impact**: Users might expect that setting `pay_frequency=Weekly` with `base_salary=1000` means ₦1,000/week, but in a monthly payroll run the system treats ₦1,000 as the period amount.

### 5.4 🟢 `default_proration_basis` Defined but Unused

[`quick_hr_payroll.php:58`](app/Modules/Payroll/Config/quick_hr_payroll.php:58):
```php
'default_proration_basis' => env('PAYROLL_DEFAULT_PRORATION_BASIS', 'calendar'),
```

This configuration value is never read anywhere in [`PayrollCalculator`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php). The proration is always `activeDays / totalDays` where `totalDays = period_end.diffInDays(period_start) + 1` (calendar days, not working days).

---

## 6. Prioritized Recommendations

### Priority 1 — Critical (Code Behavior)

| # | Recommendation | Rationale |
|---|---------------|-----------|
| **R1** | Resolve jurisdiction blocking — implement or remove docs | The documented US/UK/EU daily-deduction block does not exist in code. This is a compliance risk if the feature was promised. Either implement a `country_code` check on the employee's location/company in `calculateForEmployee()` or strike the claim from documentation. |
| **R2** | Add `is_approved` filter to `getAttendanceSummary()` | Unapproved attendance records can inflate payroll. Add `->where('is_approved', true)` to the attendance query at line [:132](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:132), or add a config toggle for whether unapproved records should be included. |

### Priority 2 — Documentation Gaps

| # | Recommendation | Where to Document |
|---|---------------|-------------------|
| **R3** | Document that `pay_frequency` is tax-annualisation only, not salary proration | [`docs/user/payroll/README.md` §3.3](docs/user/payroll/README.md:117) — add note: "Note: `pay_frequency` determines the annualisation multiplier for tax calculation. The `base_salary` is always treated as the per-period amount regardless of frequency." |
| **R4** | Document which attendance statuses count as "worked" for `salaried_daily` | [`docs/user/payroll/README.md` §8.2](docs/user/payroll/README.md:342) — add: "A day counts as worked if any of: net_hours > 0, status is not 'absent' (including late, half_day, incomplete, early_departure, unscheduled, holiday, leave), or is_paid_absence is true." |
| **R5** | Document `is_paid_absence` role in payroll | [`docs/user/payroll/README.md` §8.2](docs/user/payroll/README.md:342) and [`docs/technical/attendance-system.md` §2](docs/technical/attendance-system.md:53) — cross-reference that paid leave days count as worked days |
| **R6** | Document `country_code` and `state_code` filtering on global policies | [`docs/user/payroll/README.md` §7.4](docs/user/payroll/README.md:292) — add: "Global policies are further filtered by the employee's location country_code and state_code (matching policies where country_code is either the employee's country or null)." |
| **R7** | Document `effect` field on PayrollPolicy | [`docs/user/payroll/README.md` §7.1](docs/user/payroll/README.md:264) — add a row: "`effect` — `addition` (adds to gross) or `subtraction` (deducts from gross; treated as 'deduction' unless it's a tax policy)" |
| **R8** | Document proration formula | [`docs/user/payroll/README.md` §8](docs/user/payroll/README.md:326) — add: "Policies are prorated by `active_days / total_days` where total_days = calendar days in the payroll period and active_days = days the policy was effective within the period." |

### Priority 3 — Code Improvements (Non-Breaking)

| # | Recommendation | Rationale |
|---|---------------|-----------|
| **R9** | Guard against null `getAttendancePolicyForEmployee()` return | At line [:956](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:956), if the method returns null, accessing `->overtime_multiplier` will throw. Add a null check: `$attendancePolicy = $this->getAttendancePolicyForEmployee($position); $overtimeMultiplier = $attendancePolicy?->overtime_multiplier ?? config(...)` |
| **R10** | Clarify `salary_currency` usage or remove from fillable | The `salary_currency` field on `EmployeePosition` is never read by any payroll service. Either implement multi-currency salary support or add a comment documenting its intended future use. |
| **R11** | Remove or implement `default_proration_basis` config | The config value is defined but never read. Either remove it or implement `working_days` proration option in `getActiveDaysInRun()`. |
| **R12** | Add `net_hours` fallback documentation inline | At line [:152](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:152), add a comment explaining the fallback: `// FALLBACK: If breakdown fields are zero but net_hours exists, treat net_hours as regular hours` |

---

## Appendix A: Complete Code Trace — `calculateForEmployee()`

```
calculateForEmployee(position)                           [line 897]
  │
  ├─ payType = position.pay_type                       [line 900]
  ├─ hourlyRate = position.hourly_rate ?? 0            [line 901]
  ├─ baseSalary = position.base_salary ?? 0            [line 902]
  │
  ├─ [1] attendanceEnabled = config('quick_hr_payroll [line 910]
  │         .attendance_integration.enabled')
  ├─ effectivePayType = attendanceEnabled               [line 913]
  │                      ? payType : 'salaried_full'
  │
  ├─ [2] GROSS PAY COMPUTATION
  │   ├─ IF salaried_full:
  │   │    grossPay = baseSalary                         [line 925]
  │   ├─ ELSE (salaried_daily OR hourly):
  │   │    ├─ IF !attendanceEnabled:
  │   │    │    grossPay = baseSalary                    [line 931]
  │   │    ├─ ELSE:
  │   │    │    attendanceSummary = getAttendenceSummary [line 935]
  │   │    │    ├─ Query: Attendance WHERE employee_id   [line 132]
  │   │    │    │          AND date BETWEEN start AND end
  │   │    │    ├─ Loop: count worked_days              [line 144]
  │   │    │    │    IF net_hours >0 OR status!='absent' OR is_paid_absence → ++
  │   │    │    ├─ Sum: regular/overtime/double_hours    [lines 156–158]
  │   │    │    └─ Fallback: net_hours as regular_hours  [line 152]
  │   │    │
  │   │    ├─ IF salaried_daily:
  │   │    │    workPatternId = resolveWorkPatternId()   [line 939]
  │   │    │    ├─ EmployeeWorkPattern (date-overlap)    [line 214]
  │   │    │    └─ Default WorkPattern (company-scoped)  [line 228]
  │   │    │    totalWorkdays = getWorkdaysInPeriod()    [line 940]
  │   │    │    ├─ IF pattern: count applicable_days     [line 177]
  │   │    │    └─ ELSE: count weekdays (Mon-Fri)        [line 198]
  │   │    │    dailyRate = baseSalary / totalWorkdays   [line 941]
  │   │    │    grossPay = dailyRate * workedDays        [line 943]
  │   │    │
  │   │    ├─ IF hourly:
  │   │    │    regularHours  = summary['regular_hours'] [line 951]
  │   │    │    overtimeHours = summary['overtime_hours'][line 952]
  │   │    │    doubleHours   = summary['double_time']   [line 953]
  │   │    │    attPolicy = getAttendencePolicy()        [line 956]
  │   │    │    ├─ Calculator::getApplicablePolicy()      [line 257]
  │   │    │    │   └─ Employee→Shift→Dept→Loc→Co→Default
  │   │    │    otMult = attPolicy.ot_mult ?? config     [line 957]
  │   │    │    dtMult = attPolicy.dt_mult ?? config     [line 958]
  │   │    │    regularPay = regularHours * hourlyRate   [line 960]
  │   │    │    otPay = ot*hourlyRate*otMult             [line 961]
  │   │    │          + dt*hourlyRate*dtMult
  │   │    │    grossPay = regularPay + otPay            [line 962]
  │   │    └─ ELSE: fallback to baseSalary              [line 965]
  │
  ├─ [3] LINE ITEMS
  │   └─ makeItem('Base Salary', regularPay)           [line 979]
  │   └─ IF hourly && otPay>0: makeItem('OT Pay')      [line 980]
  │
  ├─ [4] RECURRING ADJUSTMENTS
  │   └─ EmployeeAdjustmentProfile::query               [line 990]
  │       ├─ employee_id, is_active, date range
  │       ├─ IF policy_id → override policy logic        [line 1002]
  │       └─ ELSE → standalone amount/% of grossPay      [line 1019]
  │
  ├─ [5] ONE-TIME ADJUSTMENTS
  │   └─ PayrollRunAdjustment::query                     [line 1030]
  │       └─ bonus/commission/reimbursement → earning
  │       └─ deduction → deduction
  │       └─ correction → earning or deduction (sign)
  │
  ├─ [6] RESOLVE POLICIES
  │   └─ resolvePoliciesForEmployee(position)            [line 1062]
  │       ├─ PolicyAssignments (5 entity types)           [line 401]
  │       └─ Global policies (country/state filtered)     [line 460]
  │       └─ Merge, overrides take precedence            [line 1070]
  │
  ├─ [7] APPLY POLICIES WITH PRORATION
  │   ├─ totalDays = diffInDays(start,end)+1             [line 1079]
  │   ├─ grossPayBase = sum(earning items)               [line 1082]
  │   ├─ FOR each policy:
  │   │    effectivePolicy = resolveEffectivePolicy()     [line 1085]
  │   │    ├─ Parent chain merge (fields + dates)         [line 344]
  │   │    activeDays = getActiveDaysInRun()              [line 1087]
  │   │    proration = activeDays / totalDays             [line 1089]
  │   │    amounts = applyPolicyLogic()                   [line 1092]
  │   │    ├─ IF tax: annualize → apply bands →           [line 501]
  │   │    │          de-annualize → prorate
  │   │    ├─ IF fixed: employeeValue                    [line 540]
  │   │    └─ IF %:  effectiveBase * value/100           [line 543]
  │   │    └─ Add employee + employer line items          [lines 1112–1136]
  │
  ├─ [8] CALCULATE TOTALS
  │   ├─ grossPayTotal = sum(earnings)                   [line 1142]
  │   ├─ totalDeductions = sum(deductions)               [line 1143]
  │   ├─ totalTaxes = sum(taxes)                         [line 1144]
  │   └─ netPay = grossTotal - deductions - taxes        [line 1145]
  │
  └─ [9] CREATE PAYSLIP + ITEMS
      ├─ PayrollPayslip::create(...)                     [line 1152]
      └─ PayslipItem::create(...) for each item          [line 1168]
```

---

## Appendix B: Database Field Dependency Map

```
employee_positions
  ├─ pay_type ───────────────────────► calculateForEmployee() branch
  ├─ base_salary ────────────────────► grossPay, daily rate divisor, tax base
  ├─ hourly_rate ────────────────────► hourly regularPay, otPay
  ├─ pay_frequency ──────────────────► tax annualisation only
  ├─ salary_currency ────────────────► (NOT READ)
  ├─ employment_status ──────────────► eligibility filter
  ├─ attendance_policy_id ───────────► 6-tier policy resolution (Priority 1)
  ├─ shift_id ───────────────────────► policy assignment matching
  ├─ department_id ──────────────────► policy assignment matching
  ├─ location_id ────────────────────► policy assignment, country_code
  └─ company_id ─────────────────────► policy assignment, work pattern scoping

attendances
  ├─ regular_hours ──────────────────► hourly regular pay
  ├─ overtime_hours ─────────────────► hourly overtime pay
  ├─ double_time_hours ──────────────► hourly double-time pay
  ├─ net_hours ──────────────────────► worked day count, fallback hours
  ├─ status ─────────────────────────► worked day count (!= 'absent')
  ├─ is_paid_absence ────────────────► worked day count
  ├─ is_approved ────────────────────► (NOT FILTERED — gap)
  ├─ date ───────────────────────────► period filter
  └─ employee_id ─────────────────────► employee filter

work_pattens
  ├─ applicable_days ────────────────► workday count divisor
  ├─ is_default ────────────────► fallback pattern
  └─ company_id ────────────────► company scoping

attendance_policies
  ├─ overtim_multiplier ──────────────► hourly OT pay multiplier
  ├─ double_time_multiplier ────────────► hourly DT pay multiplier
  ├─ overtim_daily_threshold_hours ────► (via AttendanceCalculator)
  ├─ overtim_weekly_threshold_hours ──► (via AttendanceCalculator)
  ├─ max_daily_overtime_hours ──────► (via AttendanceCalculator)
  └─ double_time_threshold_hours ────► (via AttendanceCalculator)

payroll_policies
  ├─ type ───────────────► tax vs non-tax branch
  ├─ effect ───────────────► addition→earning, else→deduction
  ├─ calculation_logic ───────────────► JSON: type, values, base, bands
  ├─ country_code ───────────────► global policy filter
  ├─ state_code ───────────────► global policy filter
  └─ parent_policy_id ───────────────► inheritance chain

payroll_policy_assignments
  ├─ assignable_type ───────────────► Company/Location/Dept/Shift/Group
  ├─ assignable_id ───────────────► specific entity ID
  └─ priority ───────────────► sort order (desc)

employee_adjustment_profiles
  ├─ type ───────────────► earning vs deduction
  ├─ value ───────────────► amount or %
  ├─ calculation_type ───────────────► percentage vs fixed
  ├─ policy_id ───────────────► override vs standalone
  └─ is_active ───────────────► eligibility

payroll_run_adjustments
  ├─ type ───────────────► bonus/commission/reimbursement/deduction/correction
  ├─ amount ───────────────► signed amount
  └─ label ───────────────► item description

quick_hr_payroll (config)
  ├─ attendance_integration.enabled ──► effective pay type override
  ├─ default_overtime_multiplier ───► OT multiplier fallback
  ├─ default_double_time_multiplier ─► DT multiplier fallback
  ├─ pay_periods_per_year ───────────► tax annualisation factors
  └─ default_proration_basis ────────► (DEFINED BUT UNUSED)
