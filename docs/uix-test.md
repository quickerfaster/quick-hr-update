# AI Handoff Prompt — Holiday Module Implementation

Paste this entire message into a new session after git push is complete.

---

## Context

I'm building an HR application on top of the QuickerFaster UI Library (`quicker-faster/ui-library`). The application follows the pattern of an Employee IOU/Leave/Payroll module system where consuming app code lives in `app/Modules/{ModuleName}/` and library code lives in the package `src/`.

We just completed a comprehensive analysis of the **Holiday module** (`app/Modules/Holiday/`). The module is 70% complete — solid data model, calendar management, and cross-module integrations with Attendance and Leave. The following gaps were identified and need implementation.

## Task: Implement Holiday Module Enhancements

### High Priority (Implement First)

**1. Payroll Integration — Holiday Pay Rates**

The `Holiday` model already has `holiday_pay_rate` (1.0x–3.0x), `is_paid_holiday`, `minimum_hours_for_pay`, and `is_half_day` fields. The Attendance module's [`AttendanceAggregator`](app/Modules/Attendance/Services/AttendanceAggregator.php:112) already creates holiday attendance records with credited hours. But the Payroll module's [`PayrollCalculator`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php) doesn't apply holiday pay rate multipliers.

- When processing payroll, detect attendance records with `status = 'holiday'`
- Look up the corresponding `Holiday` record to get `holiday_pay_rate`
- Multiply the employee's base rate by the holiday pay rate for those hours
- Follow the same pattern used for regular earnings/deductions in the PayrollCalculator

**2. Automatic Year Rollover**

Recurring holidays (`is_recurring = true`) don't auto-generate for the next year. Create a service + scheduled command:

- Extract the "Copy from Previous Year" logic from the batch creation wizard config ([`holiday_batch_creation.php`](app/Modules/Holiday/Data/wizards/holiday_batch_creation.php)) into a dedicated `HolidayYearRollover` service
- Create `app/Modules/Holiday/Console/Commands/RolloverHolidays.php` — scheduled for Dec 1 each year
- Register in `Kernel.php`
- Only copy holidays with `is_recurring = true`, preserve original dates (recurring holidays are date-based, not year-specific)
- Log results

### Medium Priority

**3. Holiday Pay Rate — PayrollCalculator Integration Details**

The `PayrollCalculator` at [`app/Modules/Payroll/Services/Payroll/PayrollCalculator.php`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php) processes employees in Step 1-5. The holiday pay rate should be applied in the base pay calculation (Step 1 or 2), similar to how overtime might be handled. Key files:

- [`PayrollCalculator.php`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php) — main calculation engine
- [`AttendanceAggregator.php`](app/Modules/Attendance/Services/AttendanceAggregator.php) — creates holiday attendance records
- [`Holiday.php`](app/Modules/Holiday/Models/Holiday.php) — holiday model with pay rate fields

### Key Files to Review Before Starting

**Holiday Module:**
- `app/Modules/Holiday/Models/Holiday.php` — Model with pay rate fields
- `app/Modules/Holiday/Models/HolidayCalendar.php` — Calendar model
- `app/Modules/Holiday/Data/holiday.php` — DataTable config
- `app/Modules/Holiday/Data/wizards/holiday_batch_creation.php` — Batch creation wizard
- `app/Modules/Holiday/Services/` — (create this directory if needed for new services)

**Payroll Module (for holiday pay integration):**
- `app/Modules/Payroll/Services/Payroll/PayrollCalculator.php` — Main calculation engine
- `app/Modules/Payroll/Models/PayrollPayslip.php` — Payslip model
- `app/Modules/Payroll/Models/PayrollRunAdjustment.php` — Adjustments model

**Attendance Module (for holiday attendance context):**
- `app/Modules/Attendance/Services/AttendanceAggregator.php` — Holiday attendance handler at line 112
- `app/Modules/Attendance/Models/Attendance.php` — Attendance model

**Existing Cross-Module Integrations (for pattern reference):**
- `app/Modules/Loans/Services/LoanRepaymentPayrollIntegration.php` — How Loans creates PayrollRunAdjustments
- `app/Modules/Loans/Listeners/PayrollRunLoanSyncListener.php` — How Loans listens for payroll events
- `app/Modules/Payroll/Jobs/Payrolls/ProcessPayrollRun.php` — Where loan adjustments are created before calculation (line 109)

## Project Structure

```
Library:          /Users/mac/Projects/Libraries/ui-library/
Consuming App:    /Users/mac/Projects/LaravelProjects/hr-consuming-app/
Holiday Module:   app/Modules/Holiday/
Payroll Module:   app/Modules/Payroll/
Attendance:       app/Modules/Attendance/
Key Docs:         docs/library/ (in library), docs/consuming-app/ + docs/user/ (in consuming app)
```

## UI Library Philosophy

**Two-Domain Test**: Library code (`src/`) must NEVER reference `App\Modules\*`. Ask: "Would this work in a CRM?" If no, it belongs in the consuming app. See [`docs/library/25-library-independence-safeguards.md`](docs/library/25-library-independence-safeguards.md).

**Architecture Rules:**
- Consuming app code can reference library contracts and models ✅
- Library code must NOT reference consuming app modules ❌
- Use contracts and service container binding for cross-boundary communication
- Widget processors, CurrencyField, and other library components must remain domain-agnostic

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

## Current Progress Summary

The following were completed in previous sessions:

**Loans Module:**
- ✅ Document persistence on page refresh
- ✅ Workflow approval error fix (actor_id → approvingUserId)
- ✅ Loan detail documents tab (inline, matching Leave pattern)
- ✅ Draft cleanup after submission
- ✅ Loan detail null loan fix (withoutCompanyScope + @if/@else)
- ✅ Workflow notification empty title ($appends['title'])
- ✅ Employee display in DataTable (display_field + full_name accessor)
- ✅ Currency symbol applied to wizard, detail, DataTables, dashboards
- ✅ Dashboard rewritten with standard library widgets (stat, list, trend, chart, progress)
- ✅ List widget overflow fix (published view override)
- ✅ Company currency auto-resolution via HasCurrencySymbol + CompanyProvider
- ✅ Missing routes (loans.dashboard, loan-repayments CRUD)
- ✅ Loan repayments row actions (mark as paid, waive)
- ✅ Payroll integration timing fix (adjustments now created before calculation)
- ✅ User-facing docs at `docs/user/loans/README.md`

**Library Changes (already synced to vendor):**
- ✅ `CurrencyField` resolution order swapped (model accessor before relationship)
- ✅ `MetricWidgetProcessor` uses `HasCurrencySymbol` trait
- ✅ `HasCurrencySymbol` trait has `getCurrentCompanyCurrencyCode()` method
- ✅ `StatWidgetProcessor`, `ListWidgetProcessor`, `ChartWidgetProcessor` auto-resolve company currency
- ✅ `widgets/list.blade.php` overflow fix (both library and published view)

**Holiday Module Analysis (completed, ready for implementation):**
- ✅ Full code review of models, configs, migrations, routes, dashboard, integrations
- ✅ Competitor analysis (BambooHR, Personio, Zoho People)
- ✅ Recommendations: Payroll integration (high), Year rollover (high), Public holiday API (medium), ESS calendar (medium), Notifications (medium)

## Notes

- The Holiday module already has proper DataTable configs, calendar view, dashboard, and batch creation wizard — enhance, don't replace
- Follow the Loans module pattern for payroll integration (create adjustments before calculation, not after)
- The year rollover service should be idempotent (safe to run multiple times)
- Holiday pay rate should be tested with all pay types (salaried_full, salaried_daily, hourly)
