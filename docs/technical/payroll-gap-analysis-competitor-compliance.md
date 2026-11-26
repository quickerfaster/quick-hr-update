# Payroll System — Gap Analysis: Competitors, Expectations, Compliance & Best Practices

> **Date**: 2026-09-28 (original) · **Updated**: 2026-11-24 (Nigerian compliance sweep)
> **Status**: Observation report with implementation updates — several gaps now closed
> **Companion to**: [`payroll-calculation-parameters-analysis.md`](payroll-calculation-parameters-analysis.md) (code-trace analysis)

### ✅ Gaps Closed Since Original Report (2026-11-24)

| ID | Gap | Resolution |
|----|-----|-----------|
| **R5** | YTD tracking never populated | ✅ Implemented — 8 YTD columns now computed per payslip (gross, tax, pension, NHF, NHIS, net) |
| **R10** | Payslip schema fields unused | ✅ Implemented — all 19 summary fields now populated (income_tax, pension_employee/employer, health_insurance_employee/employer, other_earnings/deductions, employer_contribution_total, taxable_earnings) |
| **R8** | Minimum wage enforcement | ✅ Implemented — Nigerian minimum-wage exemption (≤₦70k/month → zero PAYE) via `NigeriaPayrollCalculator::isIncomeTaxExempt()` |
| **R3** | Statutory tax breakdown not itemized | ✅ Implemented — tax policies auto-categorized as income_tax, social_security_tax, or medicare_tax based on policy name |
| **E2** | Pension contribution split | ✅ Implemented — pension_employee and pension_employer summary fields populated |
| **R7** | Tax table versioning | ✅ Implemented — `tax_year` field on PayrollPolicy; Nigerian 2026 PAYE bands seeded |
| **Nigerian compliance** | No Nigerian statutory policies | ✅ Implemented — 7 statutory policies seeded (PAYE, Pension, NHF, NSITF, ITF, NHIS, Group Life) + Lagos State Development Levy |
| **Nigerian compliance** | No country-specific calculation | ✅ Implemented — `CountryPayrollCalculator` strategy pattern with `NigeriaPayrollCalculator` (BHT pensionable base, tax reliefs, minimum-wage exemption) |
| **Nigerian compliance** | No pension/NHF tax relief | ✅ Implemented — employee pension (8%) and NHF (2.5%) deducted before PAYE calculation |
| **Nigerian compliance** | No statutory IDs on payslips | ✅ Implemented — TIN, RSA PIN, NHF Number, NHIS Number snapshotted onto payslips |
| **Nigerian compliance** | No remittance deadline tracking | ✅ Implemented — `remittance_due_days`, `late_penalty_rate`, `late_penalty_description` on all statutory policies |
| **Nigerian compliance** | ITF applied unconditionally | ✅ Implemented — ITF eligibility check (≥5 employees OR ≥₦50m turnover) |

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Competitor Benchmarking](#2-competitor-benchmarking)
3. [User Expectation Gaps](#3-user-expectation-gaps)
4. [Compliance Gaps](#4-compliance-gaps)
5. [Best Practice Gaps](#5-best-practice-gaps)
6. [Prioritized Gap Register](#6-prioritized-gap-register)
7. [Recommendations Roadmap](#7-recommendations-roadmap)

---

## 1. Executive Summary

This analysis evaluates the current payroll system against four dimensions:

| Dimension | Total Gaps Found | Critical | High | Medium | Low |
|-----------|-----------------|----------|------|--------|-----|
| **Competitor Benchmarking** | 11 | 1 | 3 | 5 | 2 |
| **User Expectations** | 9 | 1 | 3 | 3 | 2 |
| **Compliance** | 8 | 2 | 4 | 2 | 0 |
| **Best Practices** | 7 | 0 | 3 | 3 | 1 |

**Overall finding**: The system has a solid policy-based architecture with proper attendance integration, but it lags significantly behind market alternatives in mid-period change handling, shift differentials, statutory reporting, audit trails, and rounding. The `PayrollPayslip` model has 12+ summary fields that are defined but **never populated** by the calculator, revealing an incomplete migration from the old backup.

---

## 2. Competitor Benchmarking

Comparators: **Gusto** (US SMB), **ADP Workforce Now** (enterprise), **Sage Payroll** (UK/int'l), **BambooHR Payroll** (HR-integrated), **SeamlessHR** (African market), **Kronos/UKG** (workforce management).

### 2.1 Feature Comparison Matrix

| Feature | Gusto | ADP | Sage | Bamboo Payroll | SeamlessHR | **This System** | Gap |
|---------|-------|-----|------|---------------|-----------|----------------|-----|
| Fixed salary pay | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | — |
| Daily rate from attendance | ✅ | ✅ | ✅ | ⚠️ | ✅ | ✅ | — |
| Hourly + OT/DT | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ | — |
| **Shift differentials** (night/weekend/holiday rate) | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | 🔴 Critical |
| **Mid-period salary change proration** | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | 🔴 Critical |
| **Pro-rata for mid-period hires/terms** | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ (no first/last day logic) | 🔴 Critical |
| **Retroactive pay** | ✅ | ✅ | ✅ | ❌ | � | ❌ | � High |
| **Garnishment / attachment of earnings** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | � High |
| **YTD (Year-to-Date) tracking on payslip** | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ (schema has fields but never populated) | � High |
| **Multi-rate employee** (different rates per project/shift) | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ | � Medium |
| **Leave encashment / PTO payout** | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | 🟡 Medium |
| **Loan management with amortization** | ❌ | ✅ | ✅ | ❌ | ✅ | ❌ | 🟡 Medium |
| **Variance reporting** (current vs previous) | ✅ | ✅ | ✅ | ⚠️ | ✅ | ❌ | � Medium |
| **Rounding rules configuration** | ✅ | ✅ | ✅ | ❌ | � | ❌ | � Medium |
| **Tip/gratuity management** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | � Low |
| **Minimum wage compliance alerts** | ✅ | ✅ | ✅ | ❌ | � | ❌ | � Low |

### 2.2 Detailed Competitor Gap Analysis

#### 🔴 G-C1: Shift Differentials Not Supported

**What competitors do**: Gusto, ADP, and SeamlessHR all support shift-based pay multipliers. A "night shift" (e.g., 10pm–6am) pays 1.25×–1.5× the base hourly rate. Weekend and holiday shifts have their own multipliers. This is separate from overtime — it's additional pay for working undesirable hours regardless of total hours worked.

**What we have**: The [`Shift`](app/Modules/Attendance/Models/Shift.php:34) model has a `shift_category` field (`regular`, `peak`, `weekend`, `holiday`, `emergency`, `training`) but **no multiplier or rate-adjustment field** and **no payroll integration**. The `AttendancePolicy` has an `applies_to_shift_categories` field (JSON array) but this only filters which policy applies, not how pay is calculated.

**Impact**: Any company with night shifts, weekend staff, or holiday workers cannot correctly compute pay without manual adjustments. This blocks adoption by healthcare, manufacturing, hospitality, security, and retail sectors.

**Code gap**: `calculateForEmployee()` reads `hourly_rate` as a single value and never consults the shift's category for a rate differential.

---

#### 🔴 G-C2: Mid-Period Salary/Poosition Changes Not Prorated

**What competitors do**: When an employee gets a promotion or salary change effective mid-month, competitors automatically split the pay period. For example, a salary change from $3,000 to $4,000 effective January 15 in a monthly period (Jan 1–31): pay = (14/31 × $3,000) + (17/31 × $4,000).

**What we have**: The [`EmployeeJobHistory`] model tracks position changes with `effective_date`, and [`EmployeePosition`](app/Modules/Hr/Models/EmployeePosition.php:258) fires `updating` events that close old history entries and create new ones. However, [`PayrollCalculator::calculateForEmploee()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:897) reads `$position->base_salary` and `$position->hourly_rate` as single values — it never queries `EmployeeJobHistory` to detect mid-period changes.

**Impact**: A promoted employee in the middle of a pay period gets either the old or new rate for the entire period — never a prorated blend. This is both a user-expectation gap and a potential compliance risk (underpayment claims).

---

#### 🔴 G-C3: No Pro-Rata for Mid-Period Hires/Terminations

**What competitors do**: For an employee hired on January 20 in a January 1–31 payroll period, competitors prorate the salary: (12/31 × monthly_salary). Similarly for terminations — the employee is paid only through their last day.

**What we have**: The `employment_status` field filters employees to `'Active'`, but there's no hire-date / termination-date check. An employee hired on the last day of the period with `employment_status='Active'` would get the full period salary. An employee terminated mid-period gets nothing (they're excluded by the `Active` filter).

**Critical note**: The current logic **excludes terminated employees entirely**, meaning they receive NO final payslip for their last working days. This is a compliance violation in all major jurisdictions — final wages are legally mandated to be paid within a set timeframe.

---

#### � G-C4: No Retroactive Pay Support

**What competitors do**: When a salary increase is approved retroactively (e.g., effective from January 1 but processed in February), competitors compute the backpay as: (new_rate − old_rate) × periods_affected and add it as a separate line item.

**What we have**: No retroactive pay mechanism exists. The `PayrollRunAdjustment` model could be used manually, but there's no automatic detection or computation.

---

#### � G-C5: No Garnishment / Attachment of Earnings

**What competitors do**: ADP and Gusto handle court-ordered wage garnishments (child support, tax levies, creditor garnishments) with priority ordering, disposable income calculations, and statutory maximums.

**What we have**: No garnishment support. Could theoretically be modeled as a `PayrollPolicy` of type `deduction` with `calculation_type=fixed`, but this wouldn't handle disposable-income caps, multiple garnishment priority ordering, or state-specific rules.

---

#### ✅ G-C6: YTD Tracking — Implemented (2026-11-24)

**Previously**: The schema existed but fields were never populated.

**Now**: All summary fields are populated by [`calculateForEmployee()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php). Additionally, 8 dedicated YTD columns track cumulative tax-year totals:

| YTD Field | What It Tracks |
|-----------|---------------|
| `gross_pay_ytd` | Cumulative gross earnings |
| `income_tax_ytd` | Cumulative PAYE |
| `pension_employee_ytd` | Cumulative employee pension |
| `pension_employer_ytd` | Cumulative employer pension |
| `nhf_ytd` | Cumulative NHF |
| `nhis_employee_ytd` | Cumulative employee NHIS |
| `nhis_employer_ytd` | Cumulative employer NHIS |
| `net_pay_ytd` | Cumulative net pay |

YTD is computed by summing all previous payslips in the Nigerian tax year (Jan 1–Dec 31) via `payroll_runs.period_start`, then adding current period amounts. Falls back gracefully if columns haven't been migrated yet.

---

#### � G-C7: No Rounding Rules

**What competitors do**: ADP and Sage let you configure rounding — e.g., round hours to nearest 15 minutes, round pay to nearest cent/penny, round tax to nearest dollar. Different jurisdictions have different rounding rules.

**What we have**: Raw floating-point arithmetic. The only `round()` calls are for company-level summaries, not individual pay calculations. This can produce fractional-cent differences that cause reconciliation problems.

---

#### � G-C8: No Variance / Comparative Reporting

**What competitors do**: Gusto shows "Compared to last pay period" with color-coded changes. ADP has comprehensive variance reports for payroll audits.

**What we have**: No period-over-period comparison. The data exists in the database but there's no report or view that compares current-run totals against the prior run.

---

#### � G-C9: No Multi-Rate Employee Support

**What competitors do**: An employee who works both as a "Server" ($5/hr tipped) and "Host" ($12/hr regular) can have multiple rates. Payroll software computes pay separately for hours at each rate.

**What we have**: Single `hourly_rate` per position. A second position would require a second `EmployeePosition` record, and the payroll run would create two payslips.

---

#### � G-C10: Leave Encashment / PTO Payout Not Supported

**What competitors do**: When an employee leaves or at year-end, unused leave/PTO can be paid out as a calculated amount (daily_rate × unused_days).

**What we have**: The Leave module tracks balances, but there's no payroll integration for leave encashment.

---

## 3. User Expectation Gaps

### 3.1 What HR/Payroll Users Expect

Based on standard HR software requirements and the seventy existing `PayrollPayslip` fields that were designed but never wired up:

| # | Expectation | Status | Detail |
|---|-----------|--------|--------|
| **E1** | **Itemized tax breakdown on payslip** | ❌ Missing | Users expect to see income tax, social security, and medicare as separate line items — not just one "Tax" policy total |
| **E2** | **Pension contribution split** (employee vs employer) | ⚠️ Partial | Employer contributions are tracked as `employer_contribution` item type but pension employee/employer summary fields are never populated |
| **E3**| **Bank account snapshot on payslip** | ⚠️ Schema exists | `bank_account_snapshot` is a JSON field on `PayrollPayslip` but never written to |
| **E4** | **Payroll preview before finalization** | ✅ Exists | The wizard preview shows calculations |
| **E5** | **Bulk pay adjustment upload** | ❌ Missing | One-time adjustments can be added per-emplyee, but there's no CSV upload for bulk adjustments |
| **E6**| **Payslip lock after finalization** | � Partial | `PayrollRun` has `finalized_at` timestamp but payslips are not individually immutable — `calculateForEmployee()` just deletes and recreates them |
| **E7**| **Net pay in words** | ❌ Schema exists | `net_pay_in_words` field exists on `PayrollPayslip` but is never populated|
| **E8** | **Self-service payslip download (PDF)** | ✅ Exists | `/payroll/my-payslips` with PDF download |
| **E9** | **Multi-currency payslip** | � Partial | `currency_code` and `exchange_rate` fields exist but `exchange_rate` is never populated |

### 3.2 Detailed User Expectation Gaps

#### 🔴 E1: No Itemized Statutory Tax Breakdown

Users (employees and payroll officers) expect to see tax broken down into its components:
- **Federal/National Income Tax**
- **Social Security** (or equivalent — NHF in Nigeria, SSS in Philippines)
- **Medicare / National Health Insurance**

Currently, ALL tax policies produce a single aggregated `tax` line item. The `PayrollPolicy.type` discriminator (`tax` vs `pension` vs `insurance`) is used for the branch in [`applyPolicyLogic()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:501) but all tax policies end up as the same `tax` item type.

The payslip model already has separate columns for `income_tax`, `social_security_tax`, `medicare_tax` — these were designed for this purpose but never wired up.

---

#### � E5: No Bulk Adjustment Upload

HR managers processing a company-wide bonus (e.g., "₦50,000 end-of-year bonus for all staff") must manually create individual `PayrollRunAdjustment` records for each employee. Competitors (Gusto, ADP, SeamlessHR) support CSV uploads or percentage-based bulk adjustments.

---

#### 🟡 E6: Payslips Recreated, Not Immutable After Finalization

When a payroll run is re-processed, [`calculate()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:60) deletes all existing payslips and creates new ones. Even after `finalized_at` is set, there's no guard that prevents recalculation of finalized payslips. This violates the accounting principle of immutability — once payslips are issued, they should only be modifiable through explicit correction adjustments, not deletion-and-recreation.

---

## 4. Compliance Gaps

### 4.1 Jursdictional Compliance Matrix

| Requirement | US | UK | NG (Nigeria) | EU | **System Status** |
|------------|----|----|--------------|----|------------------|
| Progressive income tax | ✅ | ✅ | ✅ | Varies | ✅ Implemented (tax bands) |
| Social security / NI | ✅ (FICA) | ✅ (NIC) | ✅ (NSITF) | ✅ (varies) | ✅ Implemented — auto-categorized by policy name |
| Pension auto-enrollment | ✅ | ✅ | ✅ (Pension Reform Act 2014) | ✅ | ✅ Implemented — 8%/10% of BHT, pre-seeded policy |
| Minimum wage enforcement | ✅ Federal + State | ✅ National Living Wage | ✅ (₦70,000/mo) | ✅ (varies by country) | ⚠️ Partial — Nigerian exemption implemented; other jurisdictions pending |
| Overtime rules by jurisdiction | ✅ (FLSA — 1.5× after 40h) | ✅ (Working Time Regs) | ✅ (Labour Act) | ✅ (Working Time Directive) | ⚠️ Partial — single policy chain, no jurisdiction-specific defaults |
| Final pay deadline after termination | ✅ (varies by state) | ✅ (by next pay date) | ✅ (within 14 days) | ✅ (varies) | ❌ Terminated employees excluded |
| Payroll record retention | ✅ (3 yr FLSA) | ✅ (3–6 yr) | ✅ (6 yr) | ✅ (GDPR — varies) | ⚠️ No retention policy enforced in code |
| Statutory filing/reporting | ✅ (941, W-2, W-3) | ✅ (RTI, P11D, P60) | ✅ (PAYE, PENSION) | ✅ (varies) | ⚠️ Partial — YTD tracking + statutory IDs on payslips; no Form A/W-2 generation |
| Tax table versioning | Annual updates | Annual updates | Annual updates | Annual updates | ✅ Implemented — `tax_year` on PayrollPolicy; 2026 NGA bands seeded |

### 4.2 Detailed Compliance Gaps

#### ⚠️ C-C1: Minimum Wage Enforcement — Partially Implemented

**Regulation**: US (federal $7.25/h + state minimums), UK (National Living Wage £11.44/h), Nigeria (₦70,000/month National Minimum Wage Act 2019/2024).

**Current state (updated 2026-11-24)**: Nigerian minimum-wage exemption is implemented via [`NigeriaPayrollCalculator::isIncomeTaxExempt()`](app/Modules/Payroll/Services/Payroll/Countries/NigeriaPayrollCalculator.php:32) — employees earning ≤ ₦70,000/month (₦840,000/year) pay zero PAYE. However, there is still no validation that `base_salary` or `hourly_rate` meets minimum wage at the point of data entry. Other jurisdictions (US, UK, EU) are not yet covered.

**Risk**: Reduced — Nigerian tax compliance is addressed. Other jurisdictions still have legal liability for underpayment.

---

#### 🔴 C-C2: Terminated Employees Receive No Final Payslip

**Regulation**: All major jurisdictions require that terminated employees receive their final wages, including accrued but unused leave, within a statutory deadline (e.g., California: immediately; UK: next regular pay date; Nigeria: within 14 days).

**Current state**: The [`calculate()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:45) method filters to `employment_status = 'Active'`. Terminated employees are excluded. If someone is terminated on January 15 and payroll runs for January 1–31, they receive nothing.

**Risk**: Legal claims for unpaid wages, penalties, and interest.

---

#### 🔴 C-C3: No Statutory Report Generation

**Regulation**: Every jurisdiction requires periodic filings — US (Form 941 quarterly, W-2 annual), UK (RTI/FPS every pay period, P60 annual), Nigeria (PAYE annual, pension remittance monthly).

**Current state**: The system has no statutory report templates, no filing calendar, and no data exports formatted for tax authority submission.

**Risk**: Non-compliance fines, inability to operate legally as a payroll provider.

---

#### ✅ C-C4: Tax Tables Versioned (Implemented 2026-09-28)

**Regulation**: Tax rates, bands, and thresholds change annually. Payroll software must apply the correct rates for each tax year.

**Current state**: Tax bands are stored as JSON in `PayrollPolicy.calculation_logic`. A `tax_year` field has been added to the `payroll_policies` table (integer, nullable, indexed). The `PayrollPolicy` model includes `tax_year` in `$fillable` and `$casts`. Policies can now be filtered by tax year for correct annual tax application.

---

#### � C-C5: No Overtime Jurisdiction Defaults

**Regulation**: Different jurisdictions have different overtime rules:
- US California: 1.5× after 8h/day AND 40h/week, 2.0× after 12h/day
- UK: No statutory overtime rate (contractual)
- Nigeria: 1.5× after normal hours (Labour Act §13)
- EU: Working Time Directive — 48h/week max

**Current state**: Overtime thresholds and multipliers come from a single [`AttendancePolicy`](app/Modules/Attendance/Models/AttendancePolicy.php:34) resolved through the 6-tier chain. There's no jurisdiction-specific default policy or jurisdiction-based override. A UK company and a California company would both get the same `overtime_daily_threshold_hours = 8` unless explicitly configured.

---

#### � C-C6: No Audit Trail for Payroll Changes

**Regulation**: SOX (US), GDPR (EU), and general accounting standards require an immutable audit trail of who changed what in financial/payroll records.

**Current state**: The `PayslipItem` has `calculation_metadata` (JSON) for calculation transparency. `created_by` is now auto-populated on payslip creation via `auth()->id()`. Finalized payroll runs are no longer recalculated (immutability guard). Full change-log for adjustments remains a future enhancement.

---

## 5. Best Practice Gaps

### 5.1 Industry Best Practices for Payroll Systems

| Practice | Description | Status |
|----------|------------|--------|
| **Immutable payroll records** | Once finalized, payslips should never be deleted/recreated without an audit trail | ✅ Implemented (finalized_at guard) |
| **Idempotent calculations** | Same inputs always produce same outputs | ✅ Achieved (pure functions) |
| **Rounding at the end** | All intermediate calculations use full precision, round only at final display | ⚠️ Partial — hours rounded to 2dp in DB, no final rounding step |
| **Separation of calculation vs presentation** | Calculation engine is independent of payslip rendering | ✅ Achieved |
| **Versioned calculation logic** | Changes to pay formulas are tracked and dated | ❌ Missing |
| **Configurable earning/deduction types** | Users can define custom pay components without code changes | ✅ Achieved (PayrollPolicy) |
| **Test coverage for ALL calculation paths** | Unit tests for every pay type, edge case, and jurisdiction | ⚠️ 77 tests exist but coverage is incomplete |
| **Statutory compliance calendar** | System alerts for filing deadlines | ❌ Missing |
| **Double-entry / reconciliation** | Payroll totals reconcile to general ledger | ❌ Missing |
| **Role-based approval workflow** | Multi-step approval before payment | ✅ Achieved (2-step workflow) |
| **Payslip self-service** | Employees view/download their own payslips | ✅ Achieved |
| **Payroll run comparision** | Side-by-side comparison with previous period | ❌ Missing |

### 5.2 Detailed Best Practice Gaps

#### 🟡 BP1: No Calculation Versioning

When the overtime formula changes or a new tax band is introduced, there's no way to know which version of the logic produced a historical payslip. The [`Attendance`] model has `calculation_version` (`'1.0'`), which is good, but `PayrollPolicy` has no versioning.

**Recommendation**: Add `version` and `effective_from` to `PayrollPolicy` with immutable history (either via a `payroll_policy_versions` audit table or by never updating — always create new policies with incremented versions).

---

#### ✅ BP2: Immutability Guard Implemented (2026-09-28)

[`PayrollCalculator::calculate()`](app/Modules/Payroll/Services/Payroll/PayrollCalculator.php:35):

```php
if ($run->finalized_at !== null) {
    Log::warning("Payroll run #{$run->id} is already finalized. Skipping recalculation.");
    return;
}
```

A finalization guard now prevents recalculation of finalized runs. Once `finalized_at` is set, the calculator skips the run entirely. Payslips from finalized runs are immutable. Draft runs can still be recalculated normally.

---

#### � BP3: No Double-Entry / Reconciliation Framework

Professional payroll systems support reconciliation: total cash required should match the sum of all bank transfers, and payroll journal entries should balance. The current system generates totals (`total_cash_required`, `total_grss_pay`, etc.) but has no reconciliation workflow, no general ledger integration, and no "variance from expected" checks.

---

#### ✅ BP4: Payslip Schema Fields — Now Populated (2026-11-24)

**Previously**: Only 11 of 30+ fillable fields were populated by `calculateForEmployee()`.

**Now**: All summary fields are populated. Additionally, 8 YTD columns and 4 statutory identifier columns have been added:

| Category | Fields Now Populated |
|----------|---------------------|
| **Tax breakdown** | `income_tax`, `social_security_tax`, `medicare_tax` |
| **Pension** | `pension_employee`, `pension_employer`, `pension_employee_ytd`, `pension_employer_ytd` |
| **Health insurance** | `health_insurance_employee`, `health_insurance_employer`, `nhis_employee_ytd`, `nhis_employer_ytd` |
| **Other** | `other_earnings`, `other_deductions`, `employer_contribution_total`, `taxable_earnings` |
| **YTD totals** | `gross_pay_ytd`, `income_tax_ytd`, `nhf_ytd`, `net_pay_ytd` |
| **Statutory IDs** | `tax_identification_number`, `pension_rsa_number`, `nhf_number`, `nhis_number` |

The migration gap from the old backup is now closed.

---

#### 🟡 BP5: No Rounding Strategy

Rounding is applied ad-hoc:
- `round($companyGrossPay, 2)` in multi-company summaries
- `round($duration, 2)` in attendance sessions
- No explicit rounding in `calculateForEmployee()` or `applyPolicyLogic()`

Industry standard: **banker's rounding** (round half to even) for financial calculations to avoid systematic bias, applied only at the final payslip level, not at intermediate steps.

---

#### 🟢 BP6: Test Coverage Is Focused But Incomplete

The 77 tests (190 assertions) cover: policy proration, policy resolution, effective policy merging, recurring adjustments, one-time adjustments, tax bands. What's missing: `salaried_daily` with specific attendance scenarios, hourly with overtime, attendance-integration-disabled mode, multi-company, mid-period changes, zero-hour employees.

---

## 6. Prioritized Gap Register

### Critical (Would Block Adoption By Serious Users)

| ID | Category | Gap | Business Impact |
|----|----------|----|-----------------|
| **R1** | Competitor | **Shift differentials not supported** | Blocks healthcare, manufacturing, hospitality, retail |
| **R2** | Competitor | **Mid-period hire/termination prorata** | Legal risk; terminated employees get no final pay |
| **R3** | Compliance | **Statutory tax breakdown not itemized** | Payslips lack required detail for tax filings |
| **R4** | Compliance | **No statutory report generation** | Cannot operate legally as payroll provider in any jurisdiction |
| **R5** | User | **YTD tracking never populated** | Schema exists; employees cannot see cumulative earnings |

### High Priority (Quick Wins or User Pain Points)

| ID | Category | Gap | Business Impact |
|----|----------|----|-----------------|
| **R6** | User | **Payslip immutability after finalization** | Accounting compliance; audit risk |
| **R7** | Compliance | **Tax table versioning** | Tax errors when rates change mid-year or at year-end |
| **R8** | Compliance | **Minimum wage enforcement** | Legal liability for underpayment |
| **R9** | Competitor | **Retroactive pay support** | Common HR scenario (delayed approvals) has no workflow |
| **R10** | Best Practice | **Payslip schema fields populated** | 19 unused fields = incomplete migration |
| **R11** | Competitor | **Rounding rules configuration** | Reconciliation problems from fractional-cent differences |
| **R12** | User | **Bulk adjustment upload** | Manual adjustments for company-wide bonuses don't scale |

### Medium Priority (Would Improve Market Competitiveness)

| ID | Category | Gap |
|----|----------|-----|
| **R13** | Competitor | Mid-period salary change proration (different from hire/term) |
| **R14** | Competitor | Garnishment / attachment of earnings |
| **R15** | Competitor | Variance reporting (current vs previous period) |
| **R16** | Best Practice | Calculation versioning |
| **R17** | Compliance | Jurisdiction-specific overtime defaults |
| **R18** | User | Net pay in words on payslip |
| **R19** | Compliance | Payroll record retention enforcement |
| **R20** | User | Exchange rate for multi-currency payslips |

### Low Priority (Nice to Have)

| ID | Category | Gap |
|----|----------|-----|
| **R21** | Competitor | Multi-rate employee (per-project/shift rates) |
| **R22** | Competitor | Leave encashment / PTO payout |
| **R23** | Competitor | Loan management with amortization |
| **R24** | Competitor | Tip/gratuity management |
| **R25** | Best Practice | Double-entry reconciliation to GL |
| **R26** | Best Practice | Statutory compliance calendar with alerts |

---

## 7. Recommendations Roadmap

### Phase 1 — Compliance Foundation (Must Have Before Live Deployment)

1. **Implement final-pay for terminated employees** (R2)
   - Add `termination_date` check in `calculateForEmployee()` / eligibility filter
   - Prorate `base_salary` for partial periods
   - Ensure terminated employees appear in the payroll run for their final period

2. **Add `tax_year` to PayrollPolicy and implement versioning** (R7)
   - Add `tax_year` column to `payroll_policies`
   - Policy resolution should filter by `tax_year` matching the payroll period
   - Prevent editing finalized policies; always create new versions

3. **Wire up statutory tax breakdown fields** (R3)
   - `PayrollPolicy.type = 'tax'` should further distinguish `income_tax`, `social_security`, `medicare` subtypes
   - Populate `income_tax`, `social_security_tax`, `medicare_tax` on `PayrollPayslip`

4. **Implement minimum wage validation** (R8)
   - Configurable per-jurisdiction minimum wage
   - Validation on `EmployeePosition` save and at payroll run time
   - Warning (not block) for existing employees; block for new hires

### Phase 2 — Market Competitiveness (Blocks Adoption in Key Verticals)

5. **Implement shift differentials** (R1)
   - Add `rate_multiplier` to `Shift` model (or separate `ShiftPayRate` table)
   - Extend `calculateForEmployee()` hourly path to read shift category and apply multiplier
   - Attendance sessions already track start/end times — can map to shifts for differential

6. **Implement mid-period hire/termination proration** (R2 — continuation)
   - Read `employees.hire_date` and compare to payroll period
   - Compute calendar-day proration factor
   - Apply to `base_salary` before pay type branching

7. **Populate YTD fields on payslip** (R5)
   - Query prior payslips in the current tax year for the employee
   - Sum gross, tax, deductions
   - Populate `taxable_earnings` and related fields

8. **Build statutory report templates** (R4)
   - Start with Nigerian PAYE and Pension schedules (closest market)
   - Template engine that reads payslip data and formats per jurisdiction
   - PDF + CSV export

### Phase 3 — User Experience & Scalability

9. **Bulk adjustment upload** (R12)
   - CSV template: employee_number, type, label, amount
   - Preview before import
   - Validation and error reporting

10. **Payslip immutability** (R6)
    - After `finalized_at` is set, `calculate()` should skip (not delete) existing payslips
    - Correction adjustments create amendment records, not overwrites

11. **Rounding rules configuration** (R11)
    - Config option for rounding strategy (nearest, up, down, banker's)
    - Applied at payslip level only

12. **Wire up remaining payslip summary fields** (R10)
    - `pension_employee`, `pension_employer` — sum items with policy type `pension`
    - `health_insurance_employee`, `health_insurance_employer` — sum items with policy type `insurance`
    - `other_earnings`, `other_deductions` — sum items that are not tax/pension/insurance
    - `net_pay_in_words` — number-to-words converter for check printing
    - `bank_account_snapshot` — capture employee's bank details at time of payslip generation

---

## Appendix: Quick Wins (< 1 Day Each)

These are low-effort fixes that add immediate value:

| # | Fix | Effort | Impact |
|---|-----|--------|--------|
| QW1 | Add `is_approved` filter to `getAttendanceSummary()` | 5 min | Prevents unapproved attendance from inflating payroll |
| QW2 | Guard null `getAttendancePolicyForEmployee()` return | 5 min | Prevents crash if no default policy exists |
| QW3 | Populate `exchange_rate` on payslip from run currency | 30 min | Enables multi-currency display |
| QW4 | Add `created_by` / `updated_by` to payslip creation | 15 min | Basic audit trail |
| QW5 | Document unused payslip fields as "future / pending migration" | 15 min | Reduces confusion about schema-vs-code gap |
| QW6 | Add `->where('is_approved', true)` to attendance query | 5 min | Fixes documented-vs-actual behavior gap |
| QW7 | Resolve jurisdiction-blocking documentation (implement or remove claim) | 30 min | Fixes doc-vs-code discrepancy from companion report |

---

## Appendix B: Deferred for Reports Module

The following statutory report generation items are deferred until the Reports module is built. They require a report templating engine, jurisdiction-specific form layouts, and scheduled generation — infrastructure that belongs in a dedicated reporting subsystem, not the payroll calculation engine.

| ID | Report | Jurisdiction | Regulation | Data Available? | Notes |
|----|--------|-------------|------------|----------------|-------|
| **DR1** | **Form A — Annual Tax Return** | Nigeria (FIRS) | PITA §81 | ✅ Yes | Requires: per-employee annual gross, total PAYE, pension, NHF, NHIS. All data exists in `payroll_payslips` YTD columns. Format: FIRS-prescribed schedule showing each employee's emoluments and tax deducted. |
| **DR2** | **Pension Remittance Schedule** | Nigeria (PenCom) | PRA 2014 | ✅ Yes | Requires: per-employee RSA PIN, monthly pension (employee + employer). RSA PINs are now snapshotted on payslips. |
| **DR3** | **PAYE Monthly Remittance Schedule** | Nigeria (SIRS) | PITA | ✅ Yes | State-level schedule. Requires: TIN, gross, PAYE per employee. TINs are now snapshotted on payslips. |
| **DR4** | **NSITF Annual Return** | Nigeria (NSITF) | ECA 2010 | ✅ Yes | Requires: total annual payroll, 1% assessment. |
| **DR5** | **ITF Annual Return** | Nigeria (ITF) | ITF Act | ✅ Yes | Requires: total annual payroll, 1% assessment, employee count. |
| **DR6** | **W-2 (US)** | United States (IRS) | FLSA | ⚠️ Partial | Requires: SSN, wages, federal/state/local tax. US-specific fields not yet implemented. |
| **DR7** | **P60 (UK)** | United Kingdom (HMRC) | ITEPA 2003 | ⚠️ Partial | Requires: NI number, total pay, total tax. UK-specific fields not yet implemented. |

### Data Readiness Assessment

| Data Point | Status | Location |
|-----------|--------|----------|
| Annual gross per employee | ✅ Available | `payroll_payslips.gross_pay_ytd` (end-of-year payslip) |
| Annual PAYE per employee | ✅ Available | `payroll_payslips.income_tax_ytd` |
| Annual pension (employee) | ✅ Available | `payroll_payslips.pension_employee_ytd` |
| Annual pension (employer) | ✅ Available | `payroll_payslips.pension_employer_ytd` |
| Annual NHF | ✅ Available | `payroll_payslips.nhf_ytd` |
| Annual NHIS (employee) | ✅ Available | `payroll_payslips.nhis_employee_ytd` |
| Annual NHIS (employer) | ✅ Available | `payroll_payslips.nhis_employer_ytd` |
| TIN per employee | ✅ Available | `payroll_payslips.tax_identification_number` (snapshot) |
| RSA PIN per employee | ✅ Available | `payroll_payslips.pension_rsa_number` (snapshot) |
| NHF Number per employee | ✅ Available | `payroll_payslips.nhf_number` (snapshot) |
| NHIS Number per employee | ✅ Available | `payroll_payslips.nhis_number` (snapshot) |
| Employee count per company | ✅ Available | `employee_positions` (active count) |
| Total annual payroll | ✅ Available | Sum of `payroll_payslips.gross_pay` per company per tax year |

> **Implementation note**: When the Reports module is built, these deferred items should be prioritized as the first statutory report templates. All required data is already collected and stored. The reports module needs only to query, format, and export.
