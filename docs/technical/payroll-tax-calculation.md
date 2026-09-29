# Payroll Tax Calculation Engine – Comprehensive User Guide

> **Migrated from old backup**: 2026-09-28  
> **Original**: `Docs/Payroll TAX_CALCULATION_GUIDE.md`  
> **Related**: [`docs/user/payroll/README.md`](../user/payroll/README.md) §8.4

## 1. Introduction
This guide explains how the payroll system calculates **Progressive (Marginal) Tax** based on the tax brackets you define.

**Core Principle:** The system automatically **annualizes** the employee's gross pay based on their assigned `Pay Frequency` (Monthly, Semi‑monthly, Bi‑weekly, Weekly, or Daily). It then calculates the annual tax using the defined brackets, and finally converts the annual tax back to the amount due for that specific pay period. This ensures compliance with tax authorities who define rates on an annual basis, while supporting all pay frequencies.

---

## 2. How the Calculation Works (Step‑by‑Step)

1. **Annualization**: The system multiplies the employee's gross pay for the period by the number of periods in a year based on their `Pay Frequency`:
   - Monthly → × 12
   - Semi‑monthly → × 24
   - Bi‑weekly → × 26
   - Weekly → × 52
   - Daily → × 365

2. **Bracket Iteration**: The system loops through your defined brackets (sorted by the `Start` value).

3. **Marginal Application**: For each bracket, it calculates the portion of the *annual* income that falls into that specific range.

4. **Summation**: It adds up the tax from all applicable brackets.

5. **Pay Period Conversion**: The annual tax is converted to the current pay period using the ratio `Base Salary / Annual Income`:
   - Monthly → `1/12`
   - Bi‑weekly → `1/26`
   - Weekly → `1/52`
   - Daily → `1/365`

6. **Proration (if applicable)**: If the tax policy's `Effective Date` or `Expiry Date` falls inside the payroll run period, the tax is proportionally reduced by the fraction of days the policy was active during that period.

### 2.1. The Mathematical Formula (Simplified)
For each bracket (`Start` to `End` at `Rate`%):

- `Upper_Bound = min(Annual_Income, End)`
- `Taxable_Amount_In_This_Bracket = max(0, Upper_Bound - Start)`
- `Tax += Taxable_Amount_In_This_Bracket * Rate`

### 2.2. Pay Period Conversion Formula
`Period_Tax = Annual_Tax × (Base_Salary / Annual_Income)`

---

## 3. The Golden Rules for Entering Tax Brackets

To get an accurate calculation, your bracket definitions **must** follow these rules:

| Rule | Description | Correct Example | Incorrect Example |
| :--- | :--- | :--- | :--- |
| **Contiguous** | The **End** of Bracket N **must equal** the **Start** of Bracket N+1. | `0-1000`, `1000-5000` | `0-1000`, `1001-5000` (Creates a gap) |
| **Non-Overlapping** | Bracket ranges must not overlap. | `0-1000`, `1000-5000` | `0-1000`, `500-2000` (Overlaps 500-1000) |
| **Open-Ended Top** | Leave the **End** field blank (null) for the highest bracket to represent infinity. | `0-1000`, `1000-`_(blank)_ | `0-1000`, `1000-100000` (If income exceeds 100k, untaxed) |
| **No Zero-Width** | A bracket cannot have the same Start and End. | `0-1000` (width = 1000) | `1000-1000` (taxes nothing) |

---

## 4. Scenario Analysis (What happens with different entries?)

Let's use an employee with a **Monthly Base Salary of ₦43,258.97** (Annual = ₦519,107.64) to test various user entries.

### Scenario A: Perfect Contiguous Entry (Recommended)
**Brackets Entered:**
- B1: `0` – `1000` @ `5%`
- B2: `1000` – *(blank)* @ `10%`

**The System's Math:**
- B1 Tax: `(1000 - 0) * 5%` = ₦50.00
- B2 Tax: `(519,107.64 - 1000) * 10%` = ₦51,810.76
- **Annual Tax:** ₦51,860.76
- **Monthly Deduction:** ₦51,860.76 × (43,258.97 / 519,107.64) = **₦4,321.73** ✅ *(Correct)*

---

### Scenario B: User Error – Creating a "Tax Gap" 
*(User enters `1001` instead of `1000`)*
**Brackets Entered:**
- B1: `0` – `1000` @ `5%`
- B2: `1001` – *(blank)* @ `10%`

**The System's Math:**
- B1 Tax: `(1000 - 0) * 5%` = ₦50.00
- B2 Tax: `(519,107.64 - 1001) * 10%` = ₦51,810.66
- **Annual Tax:** ₦51,860.66
- **Monthly Deduction:** **₦4,321.72** ⚠️ *(Wait, this is only a 1 Kobo difference!)*

*Correction for the guide:* Actually, the difference is small for high incomes. BUT imagine an employee earning exactly **₦1,000.50** annually. 
- With B1 (0-1000) and B2 (1001-inf): The ₦0.50 is **untaxed**. 
- **The Danger:** This gap creates a tiny "tax-free" zone. If your user adds `1` to *every* bracket (e.g., `1001`, `5001`), they create multiple untaxed slivers of income. **Always use exact contiguous numbers.**

---

### Scenario C: User Error – Overlapping Brackets
*(User accidentally overlaps)*
**Brackets Entered:**
- B1: `0` – `1000` @ `5%`
- B2: `500` – `2000` @ `10%`

**The System's Math:**
- B1 Tax: `(1000 - 0) * 5%` = ₦50.00
- B2 Tax: `(2000 - 500) * 10%` = ₦150.00 *(Taxes the 500-1000 range again!)*
- **Result:** Overlapping causes **double taxation** on the shared range. **Avoid at all costs.**

---

### Scenario D: Single Bracket (Flat Tax)
*(User defines only one row)*
**Brackets Entered:**
- B1: `0` – *(blank)* @ `10%`

**The System's Math:**
- B1 Tax: `(519,107.64 - 0) * 10%` = ₦51,910.76
- **Monthly Deduction:** ₦51,910.76 / 12 = **₦4,325.90** (10% of monthly salary).

---

### Scenario E: Income Below the First Bracket
*(Employee earns ₦500 monthly / ₦6,000 annually)*
**Brackets Entered:**
- B1: `0` – `1000` @ `5%`
- B2: `1000` – *(blank)* @ `10%`

**The System's Math:**
- The system checks: Is `Annual Income (6,000) <= Bracket_Start (1000)`? No.
- B1 Tax: `min(6000, 1000) - 0` = ₦1,000 * 5% = ₦50.00
- B2 Tax: Is `Annual Income (6,000) <= Bracket_Start (1000)`? No.
- B2 Upper: `min(6000, ∞) = 6000`. Taxable = `6000 - 1000 = 5000` * 10% = ₦500.
- **Annual Tax:** ₦550.
- **Monthly Deduction:** **₦45.83** ✅ *(Correct proportional tax).*

---

## 5. Common Mistakes & How to Avoid Them

| Mistake | Why it breaks | How to Avoid |
| :--- | :--- | :--- |
| **Using 1001 instead of 1000** | Creates an untaxed "gap" of ₦1 between brackets. | Always set the **Start** of the next bracket to the exact **End** of the previous bracket. |
| **Leaving Middle Brackets with an End** | If income exceeds the defined End, the system stops and ignores higher brackets. | Only leave the **very last** bracket's End blank. |
| **Entering Decimals Incorrectly** | `5.5%` might be saved as `5` or error out. | Ensure rates are entered strictly as whole numbers (e.g., `5` for 5%). |
| **Forgetting the Annualization** | Users compare monthly manual math (`4,275.90`) to the app (`4,321.73`) and think it's wrong. | The app **must** annualize to obey tax laws. The monthly deduction is always `Annual_Tax / 12`. |
| **Zero-Width Brackets** | e.g., `1000 - 1000 @ 5%`. The width is 0, so it taxes nothing and causes confusion. | Ensure `End` is always > `Start`. |
| **Incorrect Payroll Run Period** | If the `Period Start` and `Period End` of a payroll run cover a range longer than the intended pay period (e.g., a multi‑year range), the system applies **proration** based on the policy's effective date. This reduces the tax proportionally, leading to unexpected amounts. | Always ensure the payroll run's period is **exactly** the intended pay period (e.g., `2026-07-01` to `2026-07-31` for a monthly run). Never use arbitrary or historical ranges. |

---

## 6. FAQ – Troubleshooting

**Q: My manual calculation on a calculator gives ₦4,275.90, but the app gives ₦4,321.73. Why?**
**A:** You applied the 5% and 10% to the *monthly* salary directly. The app applies these rates to the *annual* salary and then divides by 12. Because of the way percentages compound on a larger base, `(5% of 1000) + (10% of (43k-1k))` is different from `[(5% of 12k) + (10% of (519k-12k))] / 12`. The app is correct per standard tax practices.

**Q: Does proration affect tax calculations?**
**A:** **Yes.** Unlike many systems where tax is calculated on the full annual income regardless of start dates, our system **prorates tax** if:
1. The tax policy's `Effective Date` or `Expiry Date` falls **inside** the payroll run period.
2. The payroll run's period is incorrectly defined (e.g., spanning multiple years instead of a single month).

**Example:** If a payroll run covers `2020-01-01` to `2026-07-31` and the tax policy starts on `2020-01-24`, the policy is inactive for the first 23 days of that huge range. The proration factor (≈0.9904) slightly reduces the tax from £4,024.11 to £3,985.61.

**To avoid unexpected proration:** Always set the payroll run's period to the exact target date range (e.g., a single month) and ensure your tax policy's effective date is before the period start.

**Q: Does the app handle negative numbers if an employee has a loss?**
**A:** Yes. If the base salary is `0` or negative (unlikely), the system breaks out of the tax loop immediately and returns `0` tax, preventing errors.

**Q: What if my employee is paid Bi‑weekly or Weekly? How does the system handle that?**
**A:** The system automatically detects the employee's `Pay Frequency` and uses the correct multiplier:
- Bi‑weekly: annualizes by × 26, then converts back by dividing by 26.
- Weekly: annualizes by × 52, then converts back by dividing by 52.
This ensures the correct tax amount is withheld for every pay schedule.

---

## 7. Summary Checklist for Admins

Before saving a tax policy, confirm:

- [ ] Bracket 1 starts at `0`.
- [ ] `End` of Bracket N = `Start` of Bracket N+1 (Contiguous).
- [ ] `Start` is always less than `End` (except the last bracket which has no End).
- [ ] The Top bracket has `End` left **Blank**.
- [ ] Rates are entered as integers (e.g., `5` for 5%).

### Before finalizing a payroll run, confirm:
- [ ] The `Period Start` and `Period End` exactly match the intended pay period (e.g., a single month).
- [ ] Any tax policy's `Effective Date` is on or before the `Period Start` (to avoid unexpected proration).

If you follow these rules, the engine will deliver mathematically flawless tax deductions for every employee, every time.
