# AI Prompt — Payroll Calculation Analysis

> Paste this into a new AI session (Architect or Code mode) to continue the analysis.

---

## Task

Analyze and report how `pay_type` on `employee_positions` affects payroll run calculations. Identify **all other parameters** that can affect payroll run calculations (attendance data, policies, work patterns, pay schedules, adjustments, overtime multipliers, tax bands, proration, jurisdiction, attendance integration toggle, etc.). Determine whether this is documented or not, and if not, recommend what should be documented.

**Do NOT change code yet.** Produce a detailed observation report.

---

## Context — Codebase Layout

You are working on a Laravel/Livewire HR application at `/Users/mac/Projects/LaravelProjects/hr-consuming-app/` that consumes a domain-agnostic UI library at `/Users/mac/Projects/Libraries/ui-library/`.

### Module Structure (key modules)

| Module | Path | Role |
|--------|------|------|
| Payroll | [`app/Modules/Payroll/`](app/Modules/Payroll/) | Payroll runs, policies, payslips, tax, calculations |
| Attendance | [`app/Modules/Attendance/`](app/Modules/Attendance/) | Clock events, attendance records, work patterns, shifts, policies |
| Leave | [`app/Modules/Leave/`](app/Modules/Leave/) | Leave requests, leave-attendance sync |
| HR | [`app/Modules/Hr/`](app/Modules/Hr/) | Employees, positions, companies, departments, locations |

### Key Source Files to Analyse

**Payroll calculation core:**
- [`app/Modules/Payroll/Services/Payroll/PayrollCalculator.php`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:22) — `calculateForEmployee()` method (~290 lines starting at line 906) is the central method. It branches on `pay_type` (`salaried_full`, `salaried_daily`, `hourly`) and reads attendance, work patterns, policies, and adjustments.
- [`app/Modules/Payroll/Config/quick_hr_payroll.php`](app/Modules/Payroll/Config/quick_hr_payroll.php:1) — payroll batch settings, attendance integration toggle, pay periods per year, default overtime multipliers.

**Employee position (where `pay_type` lives):**
- [`app/Modules/Hr/Models/EmployeePosition.php`](app/Modules/Hr/Models/EmployeePosition.php) — fields: `pay_type`, `base_salary`, `hourly_rate`, `pay_frequency`, `attendance_policy_id`, `shift_id`, `department_id`, `location_id`, `employment_status`.

**Attendance integration:**
- [`app/Modules/Attendance/Services/AttendanceCalculator.php`](app/Modules/Attendance/Services/AttendanceCalculator.php:23) — 6-tier policy resolution, overtime/double-time calculation.
- [`app/Modules/Attendance/Traits/HasPayPeriods.php`](app/Modules/Attendance/Traits/HasPayPeriods.php:5) — `annualizeAmount()`, `deAnnualizeAmount()` used by PayrollCalculator.
- [`app/Modules/Attendance/Models/WorkPattern.php`](app/Modules/Attendance/Models/WorkPattern.php) — `applicable_days` field used for daily-rate divisor.

**Payroll policies:**
- [`app/Modules/Payroll/Models/PayrollPolicy.php`](app/Modules/Payroll/Models/PayrollPolicy.php) — `type` (tax/pension/insurance/benefit/bonus/commission/deduction), `calculation_logic` JSON, `effect` (addition/subtraction).
- [`app/Modules/Payroll/Models/PayrollPolicyAssignment.php`](app/Modules/Payroll/Models/PayrollPolicyAssignment.php) — polymorphic assignments to Company/Location/Department/Shift/EmployeeGroup.

**Payroll data configs:** All under [`app/Modules/Payroll/Data/`](app/Modules/Payroll/Data/) — `pay_schedule.php`, `payroll_policy.php`, `payroll_run.php`, `payroll_payslip.php`, `employee_payroll_profile.php`, `employee_adjustment_profile.php`, `payroll_run_adjustment.php`.

---

## Context — Key Documentation

**Core reference docs:**
- [`docs/user/payroll/README.md`](docs/user/payroll/README.md) — Payroll user guide (498 lines). §8 describes how payroll is calculated by pay type. §7 covers policies and adjustments.
- [`docs/technical/attendance-system.md`](docs/technical/attendance-system.md) — Attendance technical reference (659 lines). Documents policy resolution, overtime, break compliance, clock-in flow.
- [`docs/technical/clockin-attendance-payroll-analysis.md`](docs/technical/clockin-attendance-payroll-analysis.md) — Comprehensive analysis report (1,160+ lines). §7 and §8 cover payroll implementation and attendance-to-payroll integration. **Read this first for context on recent changes.**
- [`docs/technical/payroll-tax-calculation.md`](docs/technical/payroll-tax-calculation.md) — Tax calculation guide (186 lines).
- [`docs/technical/payroll-migration-gap-analysis.md`](docs/technical/payroll-migration-gap-analysis.md) — Old backup migration status.
- [`docs/technical/payroll-policy-adjustments-guide.md`](docs/technical/payroll-policy-adjustments-guide.md) — Policy & adjustments guide from old backup (501 lines).
- [`docs/technical/payroll-progress-debugging.md`](docs/technical/payroll-progress-debugging.md) — Payroll progress debugging guide.
- [`docs/technical/clockin-timezone-investigation.md`](docs/technical/clockin-timezone-investigation.md) — Timezone fix documentation.

**Library philosophy docs (must be respected):**
- [`docs/library/pilosophy.txt`](docs/library/pilosophy.txt) — Core: library must be decoupled from consuming app; consuming app modules must be self-contained so any module can be copied into a fresh Laravel project with the UI library and work.
- [`docs/library/25-library-independence-safeguards.md`](docs/library/25-library-independence-safeguards.md) — Non-negotiable: no `App\Modules\*` references in library code.
- [`docs/library/27-architecture-boundary.md`](docs/library/27-architecture-boundary.md) — The two-domain test and capability-vs-noun test.
- [`docs/consuming-app/pre-coding-checklist.md`](docs/consuming-app/pre-coding-checklist.md) — Must be reviewed before writing any code. Covers Blade views, Livewire components, library modifications, module file placement, navigation, row actions, boolean fields, workflow notifications, and active-state logic.

**Library docs (for reference):**
- [`docs/library/01-core-concepts.md`](docs/library/01-core-concepts.md) — 7-layer architecture, qf namespace convention.
- [`docs/consuming-app/module-structure.md`](docs/consuming-app/module-structure.md) — Module directory conventions.
- [`docs/consuming-app/data-configs.md`](docs/consuming-app/data-configs.md) — Data config schema.

---

## Context — Recent Changes (Completed in This Session)

The following changes have already been made. Do NOT redo them, but account for them in your analysis:

1. **Library boundary cleanup**: `ClockEventRecorder` contract, `ClockInOut` component, and `TeamWhoIsOutWidgetProcessor` moved from library to consuming app. Library is now HR-free.
2. **Payroll duality resolved**: `PayrollRunProcessor` deleted. All payroll processing now routes through `PayrollCalculator`.
3. **6-tier policy resolution**: `PayrollCalculator::getAttendancePolicyForEmployee()` now delegates to `AttendanceCalculator::getApplicablePolicy()`.
4. **Timezone fix**: New `UserTimezone` resolver with user→company→system cascade. Timestamps stay UTC, display converts to local.
5. **Test infrastructure**: Tests moved to `app/Modules/*/Tests/Unit/`. MySQL test database configured (`honestee_test`). 77 tests, 190 assertions, 4 intentionally skipped.
6. **Migration compliance**: 3 module migrations moved from `database/migrations/` to their proper module directories.
7. **ValueGenerator**: Sequence table name now configurable via `config('ui-library.sequence.table')`.
8. **AuthorizationService**: Renamed `$resolveUserEmployeeId` → `$resolveUserSubjectId`.
9. **`SettingsManager`**: `user` resolver wired in consuming app config.

---

## Specific Analysis Required

### 1. How `pay_type` Affects Payroll Calculations

Trace through [`PayrollCalculator::calculateForEmployee()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:906) for each of the three pay types:

- **`salaried_full`**: What data does it use? (`base_salary` only? Any attendance? Any work pattern?)
- **`salaried_daily`**: How is the daily rate computed? What determines "total working days"? How are "worked days" counted? What attendance statuses count? How does `is_paid_absence` affect it? How does jurisdiction (US/UK/EU) block daily deductions?
- **`hourly`**: How are `regular_hours`, `overtime_hours`, `double_time_hours` read from attendance? Where do overtime multipliers come from? What is the multiplier resolution chain?

For each pay type, identify:
- The exact database fields read
- The exact code path (method calls)
- Any fallback/default behaviors
- Any documented vs undocumented behaviors

### 2. All Parameters Affecting Payroll Run Calculations

Identify every parameter that can change the payroll calculation output. Group them:

| Category | Parameters | Where Defined | Documented? |
|----------|-----------|--------------|-------------|
| Employee position | `pay_type`, `base_salary`, `hourly_rate`, `pay_frequency` | EmployeePosition model | ? |
| Attendance | `regular_hours`, `overtime_hours`, `duble_time_hours`, `net_hours`, `status`, `is_paid_absence` | Attendance model | ? |
| ... | ... | ... | ? |

Include: attendance integration toggle, work patterns, pay schedules, payroll policies (tax/pension/insurance/benefit/bonus/comission/deduction), policy assignments, proration, one-time adjustments, recurring adjustments, overtime multipliers, tax bands, jursdiction blocking, company currency, pay frequency annualisation.

### 3. Documentation Gap Analysis

For each parameter identified in #2, check whether it is documented in:
- [`docs/user/payroll/README.md`](docs/user/payroll/README.md)
- [`docs/technical/attendance-system.md`](docs/technical/attendance-system.md)
- Any other doc in `docs/`

Produce a table: Parameter | Documented? | Where | Gaps

### 4. Recommendations

Based on findings, produce prioritized recommendations for:
- Code changes (if any parameters are missing or incorrectly handled)
- Documentation additions (what to document, where)

---

## Output Format

Produce a markdown report at `docs/technical/payroll-calculation-parameters-analysis.md` with:
1. Executive summary
2. Pay type analysis (salaried_full, salaried_daily, hourly) with code traces
3. Complete parameter inventory with documentation status
4. Documentation gap analysis
5. Prioritized recommendations

Use clickable file references: [`filename`](relative/path.ext:line).

Before writing any code, review the pre-coding checklist at [`docs/consuming-app/pre-coding-checklist.md`](docs/consuming-app/pre-coding-checklist.md).
