# AI Handoff Prompt — Nigerian Payroll Compliance Verification & Gap Check

Paste this entire message into a new session.

---

## Your Role

You are a senior Laravel + Livewire engineer and a Nigerian payroll compliance analyst. Your job in this session has two parts:

1. **Verify the accuracy** of every compliance-related payroll calculation that was just implemented.
2. **Audit for any remaining Nigerian statutory compliance** that is still missing from the HR SaaS app.

Do NOT make code changes until you have traced the existing implementation and reported your findings. After reporting, recommend and (only after confirmation) implement fixes.

---

## Project Structure

```
Library (UI framework):  /Users/mac/Projects/Libraries/ui-library/
Consuming HR app:       /Users/mac/Projects/LaravelProjects/hr-consuming-app/
Payroll module:         app/Modules/Payroll/
```

The library provides the generic DataTable/Form/Wizard/Workflow infrastructure. The Payroll module is a **consuming-app business module** and may reference library contracts/models, but the library (`src/`) must NEVER reference `App\Modules\*` (two-domain test).

## Database

- Main: `mysql://root:Honestee@127.0.0.1:3306/honestee`
- Test: `mysql://root:Honestee@127.0.0.1:3306/honestee_test`

⚠️ Known issue: the test DB (`honestee_test`) is out of sync with the latest migrations — a fresh `migrate` fails on an older migration. Do not rely on DB-backed integration tests until the test DB is rebuilt. The existing unit tests pass using `DatabaseTransactions`.

---

## What was already implemented (this is the code you are verifying)

### 1. 2026 PAYE bands (Nigeria Tax Act 2025, effective 1 Jan 2026)
File: `app/Modules/Payroll/Database/Seeders/NigerianPayeTaxPolicySeeder.php`

- First ₦800,000 → 0%
- Next ₦2,200,000 (≤ ₦3,000,000) → 15%
- Next ₦9,000,000 (≤ ₦12,000,000) → 18%
- Next ₦13,000,000 (≤ ₦25,000,000) → 21%
- Next ₦25,000,000 (≤ ₦50,000,000) → 23%
- Above ₦50,000,000 → 25%

The seeder is an **upsert** (updates existing rows).

### 2. Statutory policies
File: `app/Modules/Payroll/Database/Seeders/NigerianStatutoryPolicySeeder.php`

| Policy | Type | Employee | Employer | Base |
|---|---|---|---|---|
| Pension (CPS) | pension | 8% | 10% | pensionable (BHT) |
| NHF | deduction | 2.5% | 0 | base_salary (optional for private sector) |
| NSITF | deduction | 0 | 1% | gross_pay |
| ITF | deduction | 0 | 1% | gross_pay |
| NHIS | insurance | 5% | 10% | base_salary |
| Group Life | insurance | 0 | config default 1.5% | gross_pay |

### 3. Country strategy pattern (multi-country foundation)
Files:
- `app/Modules/Payroll/Contracts/CountryPayrollCalculator.php`
- `app/Modules/Payroll/Services/Payroll/CountryPayrollCalculatorRegistry.php`
- `app/Modules/Payroll/Services/Payroll/Countries/NigeriaPayrollCalculator.php`
- `app/Modules/Payroll/Services/Payroll/Countries/DefaultCountryPayrollCalculator.php`

The generic `PayrollCalculator` delegates country-specific decisions (pensionable base, tax exemption, tax reliefs) to the strategy resolved by `country_code`.

### 4. Salary components + rent relief
Files:
- `app/Modules/Payroll/Models/EmployeeSalaryComponent.php`
- `app/Modules/Payroll/Http/Livewire/Payroll/SalaryComponentsEditor.php`
- `app/Modules/Payroll/Resources/views/livewire/payroll/salary-components-editor.blade.php`
- `app/Modules/Payroll/Components/FieldTypes/SalaryComponentsField.php`
- Migrations: `2026_11_22_000004_create_employee_salary_components_table.php`, `2026_11_22_000005_add_annual_rent_to_employee_payroll_profiles_table.php`

BHT = Basic + Housing + Transport, resolved from `employee_salary_components`.

### 5. Frequency normalization
File: `app/Modules/Attendance/Traits/HasPayPeriods.php`

Normalises `weekly`, `bi-weekly`, `semi_monthly`, `monthly`, `quarterly`, `yearly` (and variants) to canonical keys. Config: `app/Modules/Payroll/Config/quick_hr_payroll.php` (`pay_periods_per_year`).

### 6. Tests
- `app/Modules/Payroll/Tests/Unit/NigeriaPayrollCalculatorTest.php` (6 tests)
- `app/Modules/Payroll/Tests/Unit/PayrollCalculatorTest.php` (33 tests)

---

## Your Tasks

### Task A — Verify payroll calculation accuracy

Trace the full calculation pipeline in `app/Modules/Payroll/Services/Payroll/PayrollCalculator.php` (especially `applyPolicyLogic`, `resolveSalaryComponents`, `resolveEmployeeReliefs`) and verify each of these produces mathematically correct output:

1. **PAYE** — annualization × bands − reliefs → de-annualization, for Monthly / Weekly / Bi-weekly / Semi-monthly frequencies.
2. **Minimum-wage exemption** — gross ≤ ₦70,000/month → zero PAYE.
3. **Rent relief** — 20% of annual rent, capped at ₦500,000.
4. **Pension** — 8% employee / 10% employer of BHT (basic + housing + transport), with `base_salary` fallback when components are absent.
5. **NHF** — 2.5% of basic salary (employee only).
6. **NSITF** — 1% of gross (employer only; must NOT deduct from employee).
7. **ITF** — 1% of gross (employer only).
8. **NHIS** — 5% employee / 10% employer of basic salary.
9. **Group Life** — employer-only, configurable rate of gross.
10. **Frequency annualization** — confirm banded tax is annualized with the correct periods-per-year for each frequency, and that flat-percentage deductions stay frequency-agnostic.

For each, produce a hand-worked example (pick a sample salary, compute expected amounts by hand, compare against the code). Flag any discrepancies.

### Task B — Nigerian compliance gap audit

Using current Nigerian law (Nigeria Tax Act 2025, Pension Reform Act 2014, NHF Act, Employees' Compensation Act 2010, ITF Act, NHIA Act 2022), identify anything still missing or incorrect, including (but not limited to):

- Consolidated Relief Allowance (CRA) — note: 2026 NTA removed CRA, replaced by the ₦800k 0% band. Verify this assumption.
- Rent relief details.
- Minimum-wage exemption threshold.
- TETFUND (corporate, not payroll — confirm it should NOT be in payslips).
- Any state-level levies or development levies.
- Employer remittance deadlines and penalty tracking (should the app track deadlines?).
- Any missing statutory fields on payslips.

Search the internet where necessary, but validate against the existing seeders/config.

---

## Key Files to Read First

- `app/Modules/Payroll/Services/Payroll/PayrollCalculator.php`
- `app/Modules/Payroll/Contracts/CountryPayrollCalculator.php`
- `app/Modules/Payroll/Services/Payroll/Countries/NigeriaPayrollCalculator.php`
- `app/Modules/Payroll/Database/Seeders/NigerianPayeTaxPolicySeeder.php`
- `app/Modules/Payroll/Database/Seeders/NigerianStatutoryPolicySeeder.php`
- `app/Modules/Payroll/Config/quick_hr_payroll.php`
- `app/Modules/Attendance/Traits/HasPayPeriods.php`
- `app/Modules/Payroll/Models/EmployeeSalaryComponent.php`
- `app/Modules/Payroll/Models/EmployeePayrollProfile.php`

## Pre-Coding Checklist (from the project docs)

- [ ] No library `src/` files reference `App\Modules\*` (two-domain test).
- [ ] Business logic stays in `app/Modules/Payroll/`.
- [ ] Rates/bands/thresholds live in seeders + config, not hardcoded in the engine.
- [ ] Country-specific logic lives in a `CountryPayrollCalculator` implementation.
- [ ] New fields are added to the model fillable/casts AND the data config (`Data/*.php`).
- [ ] New Livewire components are registered in `app/Modules/Payroll/Providers/PayrollServiceProvider.php`.

## Post-Coding Checklist

- [ ] `php -l` on every changed PHP file.
- [ ] `php artisan view:clear && php artisan config:clear && php artisan route:clear`.
- [ ] Re-run seeders (`php artisan db:seed --class=...` — they are idempotent upserts).
- [ ] Run `php artisan test app/Modules/Payroll/Tests/Unit/...`.
- [ ] No `App\Modules\*` references leak into the library `src/`.
- [ ] Run `php artisan migrate --force` on the main DB if you added columns/tables.
- [ ] Commit and push the consuming app repo.

---

## Current Git State

- Consuming app: latest commit `93af845` (already pushed).
- Library: analytics + preview fixes already committed/pushed.

## Deploy Note

`.cpanel.yml` runs `migrate --force` and `db:seed --force` on deploy, so new tables/seeders reach production automatically. The server's `.cpanel.yml` is preserved between deploys and must be updated manually if changed.
