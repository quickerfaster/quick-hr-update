# Holiday Batch Creation Wizard — Analysis & Recommendations

**Date:** 2026-10-22  
**Status:** Analysis complete — no code changes made

---

## Table of Contents

1. [Current State — What It Does Right](#1-current-state--what-it-does-right)
2. [Gap Analysis — What Needs Improvement](#2-gap-analysis--what-needs-improvement)
3. [Recommendations](#3-recommendations)
   - [3.1 Multi-Date Batch Entry](#31-multi-date-batch-entry-high)
   - [3.2 Custom Recurrence Pattern Builder](#32-custom-recurrence-pattern-builder-high)
   - [3.3 Recurrence Rule Simplification](#33-recurrence-rule-simplification-medium)
   - [3.4 Step 2 — Enrich the Holiday Selection Checklist](#34-step-2--enrich-the-holiday-selection-checklist-medium)
   - [3.5 Step 3 — Real Preview (Not Just Config)](#35-step-3--real-preview-not-just-config-medium)
   - [3.6 Form Completeness in Batch Context](#36-form-completeness-in-batch-context-low)
   - [3.7 CSV Import — Actually Implement It](#37-csv-import--actually-implement-it-low)
   - [3.8 Year Options — Dynamic Range](#38-year-options--dynamic-range-low)
4. [Implementation Priority Matrix](#4-implementation-priority-matrix)
5. [Reference Patterns](#5-reference-patterns)

---

## 1. Current State — What It Does Right

The batch creation wizard at [`holiday_batch_creation.php`](app/Modules/Holiday/Data/wizards/holiday_batch_creation.php) is a solid foundation:

| Strength | Detail |
|---|---|
| **Clean 3-step flow** | Select Calendar & Year → Select Holidays → Review & Confirm |
| **Multiple creation methods** | Start Fresh, Copy from Previous Year, Use Country Template, Import CSV — four strategies in one wizard |
| **Dynamic step 2** | `'dynamic' => true` with `checklist` type and `source: getAvailableHolidayTemplates` — adapts to the method chosen in step 1 |
| **Proper completion actions** | View Calendar, View Holidays, Create Another Set — good post-submission UX |
| **Livewire-powered** | Uses `<livewire:qf.wizard>` which supports session persistence and draft resume |
| **Library-standard config** | Follows the same `wizards/*.php` config pattern as Leave and Payroll |
| **Calendar view integration** | Preview step shows `showCalendarPreview` and `showImpactAnalysis` |

The [`Holiday`](app/Modules/Holiday/Models/Holiday.php) model and its [DataTable config](app/Modules/Holiday/Data/holiday.php) are also well-built:

| Strength | Detail |
|---|---|
| **Rich field set** | 25+ fields including recurrence, payroll, business impact, eligibility |
| **Calendar view** | Built-in month/year DataTable calendar with color-coded holiday types |
| **Field groups** | Company, Basic Info, Date Configuration, Business Impact, Payroll Settings |
| **Card/list switch views** | Toggle between card and list layouts with badges, ribbons, tags |
| **Proper casts** | `date`, `boolean`, `decimal:2`, `integer`, `datetime` — all correctly typed |

---

## 2. Gap Analysis — What Needs Improvement

### Gap 1: No Multi-Date Batch Entry 🔴

**Current behavior:** Step 1 lets you pick a calendar and year. Step 2 shows a checklist of templates. Each selected template creates exactly **one** holiday on one date.

**Missing:** A user who wants to create "Every Friday in December" or "Jan 1, Jan 15, Feb 14, Mar 17" must create each holiday individually — defeating the purpose of "batch" creation.

**Why it matters:** The `recurrence_rule` field exists (`text` column in DB, `textarea` in the form) but is hidden on the DataTable (`onTable` hidden fields) and has no UI to help the user populate it. It's a raw textarea with label "Recurrence Rule" and no hint, guidance, or validation.

### Gap 2: Custom Recurrence Pattern Has No Builder 🔴

**Current behavior:** `recurrence_pattern` is a select with options:
```
yearly_fixed    → Yearly (Fixed Date)
yearly_floating → Yearly (Floating Date)  
custom          → Custom Pattern
none            → One-time Only
```

When `custom` is selected, the user sees `recurrence_rule` — a raw textarea. There is **no UI** to define what "custom" means.

**Why it matters:** The user's question — *"Recurrence Pattern of 'custom pattern' may need to allow the user to define the pattern"* — is spot on. A raw textarea for a structured concept like recurrence rules creates data quality problems:
- Users type free-form text that can't be parsed
- No validation of the format
- The `recurrence_rule` field becomes useless for automation (e.g., the YearRollover service ignores it entirely)

### Gap 3: Recurrence Rule Is a Raw Textarea 🔴

**Current behavior:** `recurrence_rule` is `field_type: textarea`, `validation: nullable|string|max:255`. No hint, no format guidance, hidden from the DataTable.

**What it should be:** A structured builder similar to how the [Policy Calculation Builder](app/Modules/Payroll/Http/Livewire/Payroll/PolicyCalculationBuilder.php) generates JSON from user-friendly inputs. The RRULE standard (RFC 5545) or a simplified JSON structure would make this machine-readable.

### Gap 4: Step 2 Checklist Is Too Bare 🟡

**Current behavior:** Step 2 is `dynamic: true` with a `checklist` field sourced from `getAvailableHolidayTemplates`. The user sees a flat list of holiday names to check/uncheck.

**Missing:**
- Date preview — user can't see what dates each template maps to
- Holiday type badges — can't distinguish public vs company holidays
- Pay rate indicators — can't see which holidays have premium pay
- Select all / deselect all
- Filter by type or month

### Gap 5: Step 3 "Preview" Is Configuration-Only 🟡

**Current behavior:** `preview.showCalendarPreview: true` and `preview.showImpactAnalysis: true`. These are config flags for the library wizard component — when they work, they render a calendar and impact analysis.

**Missing:** A tabular preview of exactly which holidays will be created with their dates, types, pay rates, and recurrence settings. The user should be able to edit individual entries before confirming.

### Gap 6: CSV Import Is a Placeholder 🟡

**Current behavior:** `source_option: import → Import CSV File` exists in the dropdown, but there is no file upload step in the wizard config, no CSV parsing logic, and no template download link.

### Gap 7: Year Options Are Hardcoded 🟢

**Current behavior:** `target_year` options are `[2024, 2025, 2026, 2027, 2028]` — hardcoded. In 2029, users won't see the current year.

---

## 3. Recommendations

### 3.1 Multi-Date Batch Entry (HIGH)

**Problem:** No way to create holidays for multiple dates in one batch operation.

**Recommendation:** Add a **Step 1.5 — "Date Selection"** that appears when `source_option = empty` (Start Fresh) or `template` (Use Country Template). This step lets the user define multiple dates at once using one of three modes:

**Mode A — Quick Picker (date range + weekdays)**
```
[ ] Every Monday    [ ] Every Tuesday   [ ] Every Wednesday
[ ] Every Thursday  [ ] Every Friday    [ ] Every Saturday
[ ] Every Sunday

Date Range: [2026-12-01] to [2026-12-31]
```
This generates a list of dates matching the selected weekdays in the range.

**Mode B — Multi-Date Picker (specific dates)**
```
[Date Picker]  [Add Date]

Selected:
  Dec 25, 2026  [✕]
  Dec 26, 2026  [✕]
  Jan 1, 2027   [✕]
```
A tag-based multi-date selector.

**Mode C — Upload CSV**  
Moves the CSV import from a "source option" to a proper step with file upload, column mapping, and preview.

**How to implement:** Follow the [`PolicyCalculationBuilder`](app/Modules/Payroll/Http/Livewire/Payroll/PolicyCalculationBuilder.php) pattern:
1. Create `app/Modules/Holiday/Http/Livewire/HolidayBatchDateSelector.php` — a Livewire component
2. Register it in [`HolidayServiceProvider`](app/Modules/Holiday/Providers/HolidayServiceProvider.php) as `Livewire::component('qf.holiday-batch-date-selector', ...)`
3. Add it as a `customComponent` step in the wizard config:

```php
// In holiday_batch_creation.php, between current step 0 and step 1:
1 => [
    'title' => 'Select Dates',
    'customComponent' => 'qf.holiday-batch-date-selector',
    'description' => 'Choose which dates to create holidays for',
],
```

The component emits `dateSelectionUpdated` with the list of dates, and the parent wizard stores them in `stepData`.

---

### 3.2 Custom Recurrence Pattern Builder (HIGH)

**Problem:** `recurrence_pattern = custom` has no UI to define the pattern. `recurrence_rule` is a raw textarea.

**Recommendation:** Replace the `recurrence_rule` textarea with a **Recurrence Rule Builder** Livewire component that generates structured JSON. This follows the exact same architecture as the [Policy Calculation Builder](app/Modules/Payroll/Http/Livewire/Payroll/PolicyCalculationBuilder.php).

**Design:**

```
┌──────────────────────────────────────────────┐
│ Recurrence Pattern                           │
│                                              │
│ ○ Yearly (Fixed Date)  — Dec 25 every year   │
│ ○ Yearly (Floating)    — 3rd Monday of Jan   │
│ ● Custom               — Define below        │
│                                              │
│ ┌─ Custom Pattern ─────────────────────────┐ │
│ │ Frequency: [Monthly ▾]                   │ │
│ │                                          │ │
│ │ On:  ○ Day of month  ● Day of week      │ │
│ │                                          │ │
│ │ Day:  [25]                               │ │
│ │   OR                                     │ │
│ │ Week: [Third ▾]  Day: [Monday ▾]         │ │
│ │                                          │ │
│ │ Interval: Every [1] month(s)             │ │
│ │                                          │ │
│ │ End:  ○ Never  ○ After [12] occurrences  │ │
│ │       ● On date [2028-12-31]             │ │
│ └──────────────────────────────────────────┘ │
│                                              │
│ Generated Rule (read-only):                  │
│ FREQ=MONTHLY;BYMONTHDAY=25;INTERVAL=1        │
└──────────────────────────────────────────────┘
```

**JSON output** (stored in `recurrence_rule`):
```json
{
  "frequency": "monthly",
  "day_of_month": 25,
  "interval": 1,
  "end_type": "date",
  "end_date": "2028-12-31"
}
```

**How to implement:**
1. Create `app/Modules/Holiday/Http/Livewire/RecurrenceRuleBuilder.php`
2. Register in `HolidayServiceProvider`
3. Change `recurrence_rule` field type from `textarea` to a custom field type:

```php
'recurrence_rule' => [
    'display' => 'inline',
    'fillable' => true,
    'field_type' => 'recurrence_rule_builder',  // new custom type
    'label' => 'Recurrence Rule',
    'validation' => 'nullable|json',
    'visibility' => [
        'dependsOn' => 'recurrence_pattern',
        'showWhen' => ['custom'],
    ],
],
```

The `updateParent()` method in the component dispatches `recurrenceRuleUpdated` with the JSON, identical to how `PolicyCalculationBuilder` dispatches `calculationLogicUpdated`.

---

### 3.3 Recurrence Rule Simplification (MEDIUM)

**Problem:** Even with the builder above, `recurrence_rule` stores JSON — which is great for machines but still has a learning curve.

**Recommendation:** Add **preset buttons** above the builder that auto-fill common patterns:

| Preset | Description | Generated Rule |
|---|---|---|
| 🇺🇸 Thanksgiving | 4th Thursday of November | `{"frequency":"yearly","month":11,"week_of_month":4,"day_of_week":4}` |
| 🇺🇸 Memorial Day | Last Monday of May | `{"frequency":"yearly","month":5,"week_of_month":-1,"day_of_week":1}` |
| 🇬🇧 Easter Monday | (would need a computed rule) | _(special — might need external API)_ |
| 🏢 Monthly Town Hall | 1st Friday of every month | `{"frequency":"monthly","week_of_month":1,"day_of_week":5}` |
| 🏢 Quarterly Review | Mar 31, Jun 30, Sep 30, Dec 31 | `{"frequency":"yearly","dates":["03-31","06-30","09-30","12-31"]}` |

These presets would be country-aware (filtered by the calendar's `country_code`).

---

### 3.4 Step 2 — Enrich the Holiday Selection Checklist (MEDIUM)

**Problem:** The current checklist is just names. Users can't see dates, types, or pay rates.

**Recommendation:** Enhance the `getAvailableHolidayTemplates` source method (or replace the checklist with a custom component) to show:

```
┌──────────────────────────────────────────────────────────────┐
│ Select Holidays to Create                                     │
│                                                              │
│ [Select All]  [Deselect All]  Filter: [All Types ▾]         │
│                                                              │
│ ┌──────────────────────────────────────────────────────────┐ │
│ │ [✓] Christmas          Dec 25, 2026    🟢 Public   2.0x  │ │
│ │ [✓] New Year's Day     Jan 1, 2027     🟢 Public   2.5x  │ │
│ │ [ ] Boxing Day         Dec 26, 2026    🔵 Company  1.5x  │ │
│ │ [✓] Independence Day   Jul 4, 2026     🟢 Public   2.0x  │ │
│ │ [ ] Company Picnic     Aug 15, 2026    🔵 Company  1.0x  │ │
│ └──────────────────────────────────────────────────────────┘ │
│                                                              │
│ 3 holidays selected                                          │
└──────────────────────────────────────────────────────────────┘
```

**How to implement:** Replace the `checklist` field with a `customComponent` step using a new `HolidayTemplateSelector` Livewire component. The component receives the calendar ID, target year, and source option from `presetData`.

---

### 3.5 Step 3 — Real Preview (Not Just Config) (MEDIUM)

**Problem:** The preview step relies on library-level `showCalendarPreview` and `showImpactAnalysis` flags. There's no tabular list of what will be created.

**Recommendation:** Add a custom preview that shows:

```
┌──────────────────────────────────────────────────────────────┐
│ Review & Confirm — 5 holidays will be created                 │
│                                                              │
│ ┌──────────────────────────────────────────────────────────┐ │
│ │ #  │ Name              │ Date          │ Type    │ Rate  │ │
│ │────┼───────────────────┼───────────────┼─────────┼───────│ │
│ │ 1  │ Christmas         │ Dec 25, 2027  │ Public  │ 2.0x  │ │
│ │ 2  │ New Year's Day    │ Jan 1, 2028   │ Public  │ 2.5x  │ │
│ │ 3  │ Independence Day  │ Jul 4, 2027   │ Public  │ 2.0x  │ │
│ │ 4  │ Thanksgiving      │ Nov 25, 2027  │ Public  │ 1.5x  │ │
│ │ 5  │ Memorial Day      │ May 31, 2027  │ Public  │ 1.5x  │ │
│ └──────────────────────────────────────────────────────────┘ │
│                                                              │
│ Calendar: US National Holidays (2027)                        │
│ Creation Method: Copy from Previous Year                     │
└──────────────────────────────────────────────────────────────┘
```

Each row should be editable inline (click to change name, date, type, or rate).

---

### 3.6 Form Completeness in Batch Context (LOW)

**Problem:** When creating holidays in batch, certain fields that are required for individual creation (like `holiday_pay_rate`) may be left empty, causing the `MathException` we already fixed.

**Recommendation:** The batch creation should apply **smart defaults** based on context:

| Field | Default Logic |
|---|---|
| `holiday_pay_rate` | 1.0 for company holidays, 2.0 for public holidays (configurable) |
| `is_paid_holiday` | `true` for public/company, `false` for observances |
| `business_impact` | `office_closed` for public, `reduced_staff` for company |
| `eligible_employee_types` | `['full_time', 'part_time']` (already the model default) |
| `country_code` | Inherit from the calendar's `country_code` |
| `is_half_day` | `false` |

This could be a **Step 1.5 — "Default Settings"** that shows these defaults and lets the user override them once for all holidays in the batch.

---

### 3.7 CSV Import — Actually Implement It (LOW)

**Problem:** The `source_option: import` exists but does nothing.

**Recommendation:** When `source_option = import`, Step 2 becomes a file upload step:

1. **Download template** link (CSV with headers matching Holiday fields)
2. **File upload** with drag-and-drop
3. **Column mapping** — if CSV headers don't match exactly
4. **Validation preview** — show parsed rows with errors highlighted
5. **Confirm import**

This follows the library's existing import infrastructure at [`src/Http/Controllers/Imports/`](src/Http/Controllers/Imports/ImportController.php).

---

### 3.8 Year Options — Dynamic Range (LOW)

**Problem:** `target_year` options are hardcoded `[2024, 2025, 2026, 2027, 2028]`.

**Recommendation:** Generate dynamically: `range(now()->year - 1, now()->year + 3)`. Or better, make it a `number` field type with `min` and `max` validation.

```php
'target_year' => [
    'type' => 'number',
    'default' => now()->year + 1,
    'validation' => ['required', 'integer', 'min:' . now()->year, 'max:' . (now()->year + 5)],
    'label' => 'Target Year',
],
```

---

## 4. Implementation Priority Matrix

| Priority | Recommendation | Effort | Impact | Depends On |
|---|---|---|---|---|
| 🔴 **P0** | 3.1 Multi-Date Batch Entry | Large | High | — |
| 🔴 **P0** | 3.2 Custom Recurrence Pattern Builder | Medium | High | — |
| 🟡 **P1** | 3.4 Enrich Holiday Selection Checklist | Medium | Medium | — |
| 🟡 **P1** | 3.5 Real Preview Step | Medium | Medium | 3.1, 3.4 |
| 🟡 **P1** | 3.3 Recurrence Rule Presets | Small | Medium | 3.2 |
| 🟢 **P2** | 3.6 Batch Default Settings | Small | Low | — |
| 🟢 **P2** | 3.7 CSV Import | Large | Medium | — |
| 🟢 **P2** | 3.8 Dynamic Year Options | Trivial | Low | — |

**Suggested implementation order:**
1. **3.2 first** — Recurrence Pattern Builder (unlocks 3.3 presets, has the clearest reference implementation)
2. **3.1 second** — Multi-Date Batch Entry (the biggest UX gap)
3. **3.4 + 3.5 together** — Enrich checklist and preview (they share the same data model)
4. **3.6 + 3.8** — Quick wins
5. **3.7 last** — CSV import (largest effort, lowest immediate ROI)

---

## 5. Reference Patterns

### 5.1 Policy Calculation Builder — JSON Generation from UI

**File:** [`PolicyCalculationBuilder.php`](app/Modules/Payroll/Http/Livewire/Payroll/PolicyCalculationBuilder.php)  
**Blade:** [`policy-calculation-builder.blade.php`](app/Modules/Payroll/Resources/views/livewire/payroll/policy-calculation-builder.blade.php)

This is the **gold standard** for converting user-friendly UI inputs into structured JSON:

```
User fills in:                     JSON stored in DB:
┌──────────────────────┐           {
│ Start  │ End  │ Rate │             "bands": [
│   0    │ 300k │  7%  │               {"start":0, "end":300000, "rate":7},
│  300k  │ 800k │ 15%  │               {"start":300000, "end":800000, "rate":15},
│  800k  │      │ 25%  │               {"start":800000, "end":null, "rate":25}
└──────────────────────┘             ]
                                   }
```

**Pattern to replicate for Recurrence Rule Builder:**
- `mount()` loads existing JSON → populates form state
- User edits form → `updated*()` methods validate and call `updateParent()`
- `buildJson()` converts form state → JSON
- `updateParent()` dispatches event to parent form: `$this->dispatch('recurrenceRuleUpdated', $json)`
- Parent `DataTableForm` listens for the event and stores the JSON in the field

### 5.2 Leave ESS Wizard — Rich Step Configuration

**File:** [`employee_self_service.php`](app/Modules/Leave/Data/wizards/employee_self_service.php)

Demonstrates:
- `formComponent: 'leave-wizard-form'` — custom form component per step
- `dynamicFields` with `loadOptionsFrom`, `minDate`, `disableWeekends`, `highlightHolidays`
- `customValidation: ['checkLeaveBalance', 'checkDateConflicts']`
- `groups: ['request_details']` — field groups within a step
- `draftSuccessMessage` — user feedback on save
- `preview` with `showBalance`, `showTeamCalendar`, `showApprovalPath`, `balanceCallback`

### 5.3 Payroll Run Wizard — Custom Component Steps

**File:** [`payroll_run_wizard.php`](app/Modules/Payroll/Data/wizards/payroll_run_wizard.php)

Demonstrates:
- `customComponent: 'qf.payroll-wizard-adjustments'` — fully custom Livewire component as a step
- `customComponent: 'qf.payroll-wizard-preview'` — custom preview step
- `groups: ['schedule_period']` — organized field groups
- `customValidation: ['checkNoConflictingRun', 'checkEmployeesHaveBankAccounts']`

### 5.4 Wizard.php — Library Component Capabilities

**File:** [`Wizard.php`](src/Http/Livewire/Wizards/Wizard.php)

Key features available to all wizards:
- **Session persistence** — `saveToSession()` / restore from session
- **Draft resume** — `resumeRecordId` query parameter support
- **Step navigation** — `next()`, `previous()`, `goToStep()`
- **Step data storage** — `stepData[$stepIndex]` for per-step record IDs
- **Event system** — `stepFormSaved` event from child forms → `handleStepFormSaved()`
- **Preset data** — `presetData` passed from dashboard action cards
- **Force re-render** — `stepEnterCount` increment on each navigation, used as `:key` to force fresh child components

---

**End of Analysis**
