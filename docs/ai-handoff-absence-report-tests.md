# AI Handoff: Absence Report — Unit Tests

## Session Context

This is a continuation of a multi-session HR dashboard audit + ESS absence reporting implementation. All code changes below have been applied and verified with `php -l`. The next task is to **write unit/feature tests** for the absence report implementation.

---

## Project Structure

```
Library (UI framework):  /Users/mac/Projects/Libraries/ui-library/
Consuming HR app:       /Users/mac/Projects/LaravelProjects/hr-consuming-app/
HR module:              app/Modules/Hr/
Attendance module:      app/Modules/Attendance/
Leave module:           app/Modules/Leave/
```

---

## Library Philosophy (Critical Rules)

1. **Config-driven**: Everything is defined in PHP config files, not hardcoded in Blade
2. **Domain-agnostic**: The library (`src/`) knows nothing about HR, Payroll, Leave — only generic widgets, tables, forms
3. **Module isolation**: Each module owns its own dashboards, data configs, routes
4. **Two-domain test**: Library `src/` must NEVER reference `App\Modules\*`
5. **Composite dashboards**: Multiple config files can merge into one dashboard view
6. **Widget types**: Each widget type has a processor class that resolves config → rendered output

---

## What Was Implemented (This Session)

### 1. HR Dashboard Audit (7 dashboards)
All dashboard configs in `app/Modules/Hr/Data/dashboards/` were audited and fixed:
- **Bug fixes**: Cross-module model references, missing date conditions, broken progress widgets
- **Layout fixes**: All rows now fill 12 columns, widgets reordered for logical grouping
- **New widgets**: Pending Invitations, Onboarding Completion %, Gender Distribution, Headcount by Location, Expiring Documents, Policies by Category, Loans by Status
- **Removed**: Employee self-service actions from admin dashboards, redundant widgets

### 2. Attendance Dashboard Audit (3 dashboards)
Configs in `app/Modules/Attendance/Data/dashboards/`:
- Normalized odd widths (5,7,9 → standard 4,6,12)
- Fixed layout gaps
- Added "Dashboard" topNav link to navigation config

### 3. Library-Level Fixes
- **ChartWidgetProcessor**: Added `label_map` support, boolean→Yes/No mapping, null→"Unspecified", removed auto-currency symbol
- **ListWidgetProcessor**: Added `boolean` format case (Active/Inactive)
- **QuickActionsWidgetProcessor**: Added `category` filter support
- **Chart blade**: Added `stepSize: 1` + `Math.round()` for integer Y-axis
- **EmployeeDetail.php**: Fixed tenure calculation (abs + round), fixed anniversary calculation
- **employee-detail.blade.php**: Fixed placeholder resolution

### 4. ESS Absence Report — Full Implementation

#### New Files Created
| File | Purpose |
|------|---------|
| `app/Modules/Attendance/Data/wizards/absence_report.php` | Dedicated wizard config (1 step, self-contained) |
| `app/Modules/Attendance/Http/Livewire/AbsenceReportForm.php` | Custom Livewire component (Pattern C — fully decoupled) |
| `app/Modules/Attendance/Resources/views/livewire/absence-report-form.blade.php` | Blade view with 4 fields + 2 checkboxes |

#### Modified Files
| File | Change |
|------|--------|
| `app/Modules/Hr/Http/Livewire/LeaveHub.php` | Added `report-absence` tab |
| `app/Modules/Hr/Resources/views/livewire/leave-hub.blade.php` | Added tab button + wizard rendering |
| `app/Modules/Hr/Data/dashboards/dashboard_leave_hub.php` | Added "Report Absence" action card (w6) |
| `app/Modules/Hr/Config/quick-actions.php` | Added "Report Absence" quick action |
| `app/Modules/Attendance/Providers/AttendanceServiceProvider.php` | Registered `attendance.absence-report-form` |
| `app/Modules/Attendance/Data/attendance.php` | Set `is_paid_absence` + `hours_deducted` to `editable: false` |
| `app/Modules/Attendance/Data/wizards/sick_call_report.php` | Fixed cross-module model references |

#### Architecture (Pattern C — Custom Component)
```
Leave Hub (/hr/leave-hub?tab=report-absence)
  └─ qf.wizard(configKey: attendance.wizards.absence_report)
       └─ Step 1: customComponent → attendance.absence-report-form
            └─ AbsenceReportForm.php (Livewire)
                 ├─ Listens for: saveStepForm
                 ├─ Validates: absenceType, startDate, absenceReason
                 ├─ Stores data in session
                 └─ Dispatches: stepFormSaved(recordId: 0, stepIndex: 0)
```

#### Wizard Form Fields
| Field | Type | Validation |
|-------|------|-----------|
| Absence Type | Select (Sick/Emergency/Personal/Official Assignment/Other) | required |
| Start Date | Date picker (max: today) | required, before_or_equal:today |
| Expected Duration | Select (1d/2-3d/4-5d/1-2w/3-4w/1-2m/Uncertain) | — |
| Reason | Textarea | required, min:10, max:500 |
| Notify manager | Checkbox (default: ON) | — |
| Create draft leave request | Checkbox (default: ON) | — |

#### Key Design Decisions
- **Official Assignment** type added — paid, not deducted from leave balance
- **Duration values are numeric** (1,3,5,14,28,60,0) for backend date computation
- **Create leave request defaults ON** — prevents leave balance bypass
- **1-step wizard** — no empty review page
- **Fully decoupled** from `attendance.php` data config (Pattern C)

---

## Files to Test

### Core Implementation
```
app/Modules/Attendance/Http/Livewire/AbsenceReportForm.php
app/Modules/Attendance/Data/wizards/absence_report.php
app/Modules/Attendance/Resources/views/livewire/absence-report-form.blade.php
app/Modules/Attendance/Providers/AttendanceServiceProvider.php
```

### Integration Points
```
app/Modules/Hr/Http/Livewire/LeaveHub.php
app/Modules/Hr/Resources/views/livewire/leave-hub.blade.php
app/Modules/Hr/Data/dashboards/dashboard_leave_hub.php
app/Modules/Hr/Config/quick-actions.php
```

### Affected Configs
```
app/Modules/Attendance/Data/attendance.php
app/Modules/Attendance/Data/wizards/sick_call_report.php
```

### Library Changes (already tested via php -l)
```
src/Widgets/ChartWidgetProcessor.php
src/Widgets/ListWidgetProcessor.php
src/Widgets/QuickActionsWidgetProcessor.php
src/Resources/views/widgets/chart.blade.php
```

---

## What to Test

### 1. AbsenceReportForm Component
- [ ] Component renders all 6 fields
- [ ] Validation rejects empty absence type
- [ ] Validation rejects future start date
- [ ] Validation rejects reason < 10 characters
- [ ] Validation accepts reason >= 10 characters
- [ ] `saveStepForm` event triggers validation
- [ ] `stepFormSaved` dispatched on successful save
- [ ] Session contains form data after save
- [ ] Default values: absenceType=sick_leave, startDate=today, expectedDuration=1, createLeaveRequest=true, notifyManager=true

### 2. LeaveHub Component
- [ ] `report-absence` tab is in allowed tabs list
- [ ] `switchTab('report-absence')` works
- [ ] Query string `?tab=report-absence` sets active tab

### 3. Wizard Config
- [ ] Config loads without errors
- [ ] Step 1 has `customComponent` set to `attendance.absence-report-form`
- [ ] Completion actions have correct URLs
- [ ] No `model` or `groups` on Step 1 (fully decoupled)

### 4. Attendance/Leave Integration
- [ ] `is_paid_absence` field is not editable
- [ ] `hours_deducted` field is not editable
- [ ] Sick call wizard model references are correct (Attendance module, not Hr)

### 5. Dashboard Integration
- [ ] Leave Hub overview shows "Report Absence" action card at w6
- [ ] "Apply for Leave" action card is at w6 (side by side)
- [ ] Quick actions include "Report Absence" in Self Service category

### 6. Library Changes (Regression)
- [ ] Charts show integer Y-axis (no decimals)
- [ ] Boolean list columns show "Active"/"Inactive"
- [ ] Chart `label_map` works for custom labels
- [ ] Quick actions `category` filter works
- [ ] Charts without explicit currency_code don't show ₦

---

## Database

- Main: `mysql://root:Honestee@127.0.0.1:3306/honestee`
- Test: `mysql://root:Honestee@127.0.0.1:3306/honestee_test`

⚠️ The test DB (`honestee_test`) is out of sync with the latest migrations. Do not rely on DB-backed integration tests.

---

## Pre-Coding Checklist
- [ ] No library `src/` files reference `App\Modules\*` (two-domain test)
- [ ] Dashboard configs live in `app/Modules/{Module}/Data/dashboards/`
- [ ] Widget configs follow the library's widget schema
- [ ] Data queries are scoped to the current company
- [ ] All labels/text use translation keys or are human-readable

## Post-Coding Checklist
- [ ] `php -l` on every changed PHP file
- [ ] `php artisan optimize:clear`
- [ ] Refresh each dashboard in the browser — no blank pages, no 500s
- [ ] No `App\Modules\*` references leak into the library `src/`
- [ ] Run `php artisan test` — all existing tests still pass

---

## Git Branch
```
feature/hr-dashboard-audit → staging → main
```

Current branch: `feature/hr-dashboard-audit`

---

## Key Docs
- Library widget system: `docs/library/07-component-catalog.md`
- Library engines: `docs/library/09-engines-and-services.md`
- HR user guide: `docs/user/hr/README.md`
- Dashboard audit report: `docs/ai-handoff-hr-dashboard-audit.md`
- This handoff: `docs/ai-handoff-absence-report-tests.md`
