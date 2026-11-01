# AI Handoff Prompt — Payroll Preview Zero Earnings Investigation

Paste this entire message into a new session.

---

## Context

I'm building an HR application on top of the QuickerFaster UI Library (`quicker-faster/ui-library`). The application follows the pattern of an Employee IOU/Leave/Payroll module system where consuming app code lives in `app/Modules/{ModuleName}/` and library code lives in the package `src/`.

We just completed a comprehensive implementation of the **Holiday module** (payroll integration, year rollover, batch creation wizard, country templates, recurrence rules). All changes are committed and pushed.

## Task: Investigate Zero Earnings on Payroll Preview

### The Problem

The payroll run for **"Test Company"** with employee **"Employee 100 Mohammed (EMPLOYEE-2026-00015)"** shows **₦0.00** for Gross Pay, Deductions, and Net Pay on the payroll preview wizard page. The employee has daily clocking (attendance records), so earnings should be non-zero.

### UIX Evidence

```
Review Payroll Run
Period: 2026-10-01 – 2026-10-31

Total Cash Required: ₦0.00

Employee 100 Mohammed (EMPLOYEE-2026-00015)    ₦0.00    ₦0.00    ₦0.00

ITEM DETAILS              TYPE      AMOUNT
Base Salary               EARNING   + ₦0.00
```

### What to Investigate (No Code Changes — Analysis Only)

1. **Employee Position**: Check `EMPLOYEE-2026-00015`'s `EmployeePosition` record — what is their `pay_type`, `base_salary`, `hourly_rate`, `pay_frequency`, `employment_status`?

2. **Payroll Run**: Check the payroll run for period 2026-10-01 to 2026-10-31 — what is the `pay_schedule_id`, `company_id`, `is_multi_company`?

3. **Attendance Records**: Check if the employee has attendance records for October 2026 with `is_approved = true` and `status = 'present'` (or other statuses that credit hours).

4. **Payroll Calculation Flow**: Trace through `PayrollCalculator::calculateForEmployee()` for this employee:
   - Is `attendanceEnabled` true? (config `quick_hr_payroll.attendance_integration.enabled`)
   - What is the `effectivePayType`?
   - For `salaried_daily`: is `getAttendanceSummary()` returning non-zero `worked_days`?
   - For `salaried_full`: is `baseSalary` non-zero?
   - Is `prorationFactor` reducing the salary to 0 (hire date after period end, termination before period start)?

5. **Employee Payroll Profile**: Check `employee_payroll_profiles` — is the employee linked to the correct `pay_schedule_id` with `is_active = 1`?

6. **Work Pattern**: If `salaried_daily`, check if the employee has a work pattern assigned and if `getWorkdaysInPeriod()` returns a non-zero value.

### Key Files to Review

**Payroll Module:**
- `app/Modules/Payroll/Services/Payroll/PayrollCalculator.php` — Main calculation engine (especially `calculateForEmployee()`, `getAttendanceSummary()`, `getWorkdaysInPeriod()`, `resolveWorkPatternId()`)
- `app/Modules/Payroll/Http/Livewire/Payroll/PayrollWizardPreview.php` — Preview wizard component
- `app/Modules/Payroll/Models/PayrollRun.php` — PayrollRun model
- `app/Modules/Payroll/Models/PayrollPayslip.php` — Payslip model
- `app/Modules/Payroll/Models/PaySchedule.php` — PaySchedule model
- `app/Modules/Payroll/Models/EmployeePayrollProfile.php` — Employee payroll profile

**HR Module:**
- `app/Modules/Hr/Models/Employee.php` — Employee model
- `app/Modules/Hr/Models/EmployeePosition.php` — Position model (pay_type, base_salary, hourly_rate, pay_frequency)

**Attendance Module:**
- `app/Modules/Attendance/Models/Attendance.php` — Attendance records
- `app/Modules/Attendance/Models/WorkPattern.php` — Work patterns
- `app/Modules/Attendance/Models/EmployeeWorkPattern.php` — Employee work pattern assignments
- `app/Modules/Attendance/Services/AttendanceCalculator.php` — Attendance calculation

**Config:**
- `config/quick_hr_payroll.php` — Payroll config (attendance_integration.enabled, default overtime multipliers)

### Investigation Approach

1. Query the database directly to inspect the employee's position, attendance records, and payroll profile
2. Trace the `calculateForEmployee()` logic step by step for this specific employee
3. Identify which branch of the calculation is producing ₦0.00
4. Report findings with specific file:line references

### Expected Output

A clear report identifying:
- The root cause of the ₦0.00 earnings (with file:line references)
- Whether it's a data issue (missing attendance, wrong pay type, missing payroll profile) or a code issue (calculation bug)
- Recommended fix (but do NOT implement — analysis only)

## Project Structure

```
Library:          /Users/mac/Projects/Libraries/ui-library/
Consuming App:    /Users/mac/Projects/LaravelProjects/hr-consuming-app/
Payroll Module:   app/Modules/Payroll/
Attendance:       app/Modules/Attendance/
HR Module:        app/Modules/Hr/
Key Docs:         docs/library/ (in library), docs/consuming-app/ + docs/user/ (in consuming app)
```

## UI Library Philosophy

**Two-Domain Test**: Library code (`src/`) must NEVER reference `App\Modules\*`. Ask: "Would this work in a CRM?" If no, it belongs in the consuming app. See [`docs/library/25-library-independence-safeguards.md`](docs/library/25-library-independence-safeguards.md).

**Architecture Rules:**
- Consuming app code can reference library contracts and models ✅
- Library code must NOT reference consuming app modules ❌
- Use contracts and service container binding for cross-boundary communication

## Pre-Coding Checklist

From `docs/consuming-app/pre-coding-checklist.md`:

- [ ] Blade views use `<x-qf::navigation-layout>` with `configKey`, `context`, `moduleName`
- [ ] Livewire components registered in module's service provider
- [ ] All module files under `app/Modules/{ModuleName}/`
- [ ] Navigation items have permission and icon keys
- [ ] New routes follow existing naming convention
- [ ] Custom DataTable components extend library `DataTable` class

## Post-Coding Checklist

- [ ] Run `php artisan view:clear && php artisan config:clear && php artisan route:clear`
- [ ] If library files are modified, copy to `vendor/quicker-faster/ui-library/` (the consuming app uses local vendor, not symlink)
- [ ] Check `storage/framework/views/*.php` for stale compiled views
- [ ] Clear `bootstrap/cache/` if config changes
- [ ] No `App\Modules\*` references in library `src/` files
- [ ] Verify existing module functionality isn't broken (Loans, Leave, Payroll, Attendance)

## Database Connection

The test database is configured in `phpunit.xml`:
```
DB_CONNECTION: mysql
DB_HOST: 127.0.0.1
DB_PORT: 3306
DB_DATABASE: honestee_test
DB_USERNAME: root
DB_PASSWORD: Honestee
```

Use `php artisan tinker` or direct SQL queries to inspect the data.

## Notes

- This is an **analysis-only** task — do NOT change any code
- The employee has daily clocking, so attendance records should exist
- The pay period is October 2026 (2026-10-01 to 2026-10-31)
- The company is "Test Company" — check its `id` and currency settings
- The currency symbol is ₦ (Nigerian Naira) — check if the company's `currency_code` is `NGN`
- Previous session completed Holiday module implementation (payroll integration, year rollover, batch wizard, country templates, recurrence rules) — all committed and pushed
