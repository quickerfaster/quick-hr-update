# Attendance System — Technical Reference

> **Version:** 2.0  
> **Last Updated:** September 2026  
> **Module:** `app/Modules/Attendance`  
> **User Guide:** See [`docs/user/attendance/README.md`](../user/attendance/README.md) for employee/admin workflows  
> **Geofencing:** See [`plans/geofencing-implementation.md`](../../plans/geofencing-implementation.md)

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Key Concepts & Glossary](#2-key-concepts--glossary)
3. [System Architecture](#3-system-architecture)
4. [Administrator Guide](#4-administrator-guide)
   - [4.1 Attendance Policies](#41-attendance-policies)
   - [4.2 Work Patterns](#42-work-patterns)
   - [4.3 Shifts](#43-shifts)
   - [4.4 Shift Schedules](#44-shift-schedules)
   - [4.5 Holidays & Leave](#45-holidays--leave)
   - [4.6 Geofencing & Locations](#46-geofencing--locations)
   - [4.7 Interpreting Attendance Records](#47-interpreting-attendance-records)
5. [User Guide (HR / Payroll Staff)](#5-user-guide-hr--payroll-staff)
6. [API Endpoint Reference](#6-api-endpoint-reference)
7. [Web Clock-In Flow](#7-web-clock-in-flow)
8. [Queue & Jobs](#8-queue--jobs)
9. [Troubleshooting & FAQ](#9-troubleshooting--faq)
10. [Glossary](#10-glossary)

---

## 1. Introduction

The Attendance System captures employee clock-in and clock-out events from mobile devices, web browsers, and biometric terminals, then processes them through a queue-based pipeline to produce daily attendance records with precise hour breakdowns, overtime calculations, break compliance checks, geofence validation, and violation detection.

**Key Features**

- **Multi-channel clock events** — accepts clock-in/clock-out from mobile apps (Android), web browsers, biometric devices, kiosks, and API integrations
- **Web clock-in with geofencing** — browser-based clock-in/out via the My Portal dashboard, with GPS capture and geofence validation
- **Idempotent event ingestion** — duplicate clock events are detected and silently ignored (10-second window for web, exact match for API)
- **Queue-based processing** — clock events are saved immediately; attendance calculation runs via [`ProcessAttendanceJob`](../../app/Modules/Attendance/Jobs/ProcessAttendanceJob.php)
- **Multi-tier policy resolution** — attendance policies are resolved through a priority chain: Employee → Shift → Department → Location → Company → System Default
- **Dual-threshold overtime** — daily overtime (hours beyond `overtime_daily_threshold_hours`) and weekly overtime (cumulative regular hours beyond `overtime_weekly_threshold_hours`) with double-time support
- **Multi-rule break compliance** — supports JSON arrays like `[4, 8]` for multiple break-after thresholds with corresponding durations
- **Unpaid break deduction** — policy-defined unpaid break minutes are deducted from payable (`net_hours`) while actual hours are preserved for status determination
- **Holiday and leave awareness** — approved leave and company holidays automatically override normal attendance calculation
- **Unplanned absence detection** — days with no clock events and no approved leave are flagged as unplanned absences with `needs_review = true`
- **Geofence validation** — clock-in GPS coordinates are validated against work location boundaries using Haversine distance
- **Full audit trail** — `calculation_metadata` stores the complete breakdown of every calculation; GPS coordinates, IP, and device info logged on every clock event

> **Integration with Payroll:** Attendance records are used for payroll calculation for `salaried_daily` and `hourly` employees. This integration can be enabled/disabled globally via the `PAYROLL_ATTENDANCE_INTEGRATION_ENABLED` setting.

---

## 2. Key Concepts & Glossary

### Clock Event (`clock_events` table)

A raw timestamp record of an employee action. Each event has an `event_type` of either `clock_in` or `clock_out`. Events arrive from the mobile app in Android format (`check-in` / `check-out`) and are converted to internal types by the controller. Web events are recorded via [`ClockEventRecorderService`](../../app/Modules/Attendance/Services/ClockEventRecorderService.php). Additional event types supported in the UI include `break_start`, `break_end`, `meal_start`, and `meal_end`.

| Field | Description |
|---|---|
| `employee_id` | Internal employee ID (integer FK) |
| `employee_number` | Human-readable employee number (e.g. `EMP-2025-001`) |
| `event_type` | `clock_in` or `clock_out` |
| `timestamp` | Date and time of the event |
| `method` | Source: `web`, `device`, `biometric`, `kiosk`, `api`, `manual` |
| `latitude` / `longitude` | GPS coordinates (decimal degrees, 8-11 decimal places) |
| `location_name` | Matched work location name from geofence validation (e.g. "Warehouse") |
| `device_id` / `device_name` | Device fingerprint for audit |
| `sync_status` | `pending`, `synced`, `failed`, or `manual` |
| `company_id` | Company scope for multi-tenancy |

### Attendance Record (`attendances` table)

The computed daily attendance summary for one employee on one date. Exactly one record per employee per date (enforced by a unique index on `[employee_id, date]`). Created automatically when the first clock event for a day is processed.

**Status values:**

| Status | Meaning |
|---|---|
| `present` | Normal workday, all thresholds met |
| `absent` | No hours worked on a scheduled workday |
| `late` | Clocked in after the grace period |
| `half_day` | Worked ≤ 50% of expected hours |
| `incomplete` | Worked > 50% but < 90% of expected hours, or missing clock-out |
| `early_departure` | Clocked out before the early-departure grace window |
| `unscheduled` | Worked on a day not in the employee's work pattern |
| `holiday` | Company holiday (no work expected) |
| `leave` | Approved leave covers this day |

**Key computed fields:**

| Field | Description |
|---|---|
| `net_hours` | Payable hours after unpaid break deduction |
| `regular_hours` | Hours within daily/weekly thresholds |
| `overtime_hours` | Hours beyond thresholds (paid at `overtime_multiplier`) |
| `double_time_hours` | Hours beyond `double_time_threshold_hours` (paid at `double_time_multiplier`) |
| `minutes_late` | Minutes clocked in after `scheduled_start + grace_period_minutes` |
| `minutes_early_departure` | Minutes clocked out before `scheduled_end − early_departure_grace_minutes` |
| `missed_break_minutes` | Total minutes of required breaks not taken |
| `needs_review` | `true` when violations exist or status is `incomplete`, `half_day`, or `unscheduled` |
| `calculation_metadata` | JSON with full breakdown: expected hours, overtime steps, break violations, etc. |

### Attendance Session (`attendance_sessions` table)

One continuous work period within a day. Created by pairing a `clock_in` event with the next `clock_out` event. An attendance record can have multiple sessions.

| Field | Description |
|---|---|
| `session_type` | `work`, `paid_break`, `unpaid_break`, or `overtime` |
| `start_time` / `end_time` | Session boundaries (nullable `end_time` for orphaned clock-ins) |
| `duration_hours` | Computed duration |
| `clock_in_event_id` / `clock_out_event_id` | Links back to raw clock events |
| `is_overnight` | `true` if the session spans midnight |
| `validation_status` | `valid`, `missing_clock_out`, `overlaps`, `too_short`, `too_long` |

### Attendance Policy (`attendance_policies` table)

Defines the rules for calculating attendance: grace periods, overtime thresholds, break requirements, and multipliers. Policies are assigned polymorphically — they can be attached to a Company, Department, Location, or Shift via the `PolicyAssignment` model, or directly to an employee via `EmployeePosition.attendance_policy_id`.

### Work Pattern (`work_patterns` table)

Defines which days of the week an employee is expected to work and which shift applies. The `applicable_days` field stores day-of-week numbers (1=Monday through 7=Sunday) as a comma-separated string or JSON array.

### Shift (`shifts` table)

Defines a named work period with `start_time`, `end_time`, and `duration_hours`. Supports an `is_overnight` flag for shifts that cross midnight.

### Shift Schedule (`shift_schedules` table)

An individual override that assigns a specific shift to a specific employee on a specific date. Takes precedence over work patterns and default shifts.

### Geofence

A virtual perimeter around a work location. When an employee clocks in via the web, their browser GPS coordinates are validated against their assigned location's `geofence_radius` (in meters). Clock-in outside the radius is blocked. Clock-out is always allowed. See [§4.6](#46-geofencing--locations).

### Grace Period

The number of minutes after the scheduled start time during which an employee can clock in without being marked late. Defined by `grace_period_minutes` (default: 5). A separate `early_departure_grace_minutes` (default: 5) applies to clocking out before the scheduled end time.

### Overtime

Hours worked beyond the daily threshold (`overtime_daily_threshold_hours`, default: 8) are classified as daily overtime. When cumulative regular hours for the week exceed the weekly threshold (`overtime_weekly_threshold_hours`, default: 40), today's regular hours overflow into overtime. Overtime is capped at `max_daily_overtime_hours` (default: 4). Hours beyond `double_time_threshold_hours` (default: 12) are classified as double time.

### Break Compliance

The system checks whether employees took required breaks after working a certain number of continuous hours. The `requires_break_after_hours` and `break_duration_minutes` fields support both scalar values and JSON arrays for multiple rules.

### Policy Assignment

Policies are assigned through a polymorphic `PolicyAssignment` model. The resolution order is:

1. **Employee-specific** — `EmployeePosition.attendance_policy_id`
2. **Shift-specific** — `PolicyAssignment` where `assignable_type` references Shift
3. **Department** — `PolicyAssignment` where `assignable_type` references Department
4. **Location** — `PolicyAssignment` where `assignable_type` references Location
5. **Company** — `PolicyAssignment` where `assignable_type` references Company
6. **System default** — `AttendancePolicy` where `is_default = true` and `is_active = true`

---

## 3. System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│  📱 Mobile App                    💻 Web Browser             │
│  POST /api/hr/attendance/store    My Portal → ClockInOut     │
│  (Android format)                 (Alpine.js geolocation)    │
└──────────────┬──────────────────────┬───────────────────────┘
               │                      │
┌──────────────▼──────────────────────▼───────────────────────┐
│  ENTRY POINTS                                                │
│  ClockEventController (API)    ClockEventRecorderService (Web)│
│  → Validate & Convert          → Resolve employee            │
│  → Idempotency check           → GeofenceValidator           │
│  → Save ClockEvent             → Idempotency check           │
│                                 → Save ClockEvent            │
└──────────────┬──────────────────────┬───────────────────────┘
               │                      │
               │    Dispatch ProcessAttendanceJob              │
               ▼                      ▼
┌─────────────────────────────────────────────────────────────┐
│  QUEUE LAYER                                                 │
│  ProcessAttendanceJob (3 retries, exponential backoff)       │
└──────────────────────────┬──────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────┐
│  AGGREGATOR (AttendanceAggregator)                           │
│  → Check Holiday? → Mark as holiday                         │
│  → Check Approved Leave? → Mark as leave                    │
│  → Has Clock Events? → Delegate to Calculator               │
│  → No Clock Events? → Mark as unplanned absence             │
└──────────────────────────┬──────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────┐
│  CALCULATOR (AttendanceCalculator)                           │
│  → Resolve Policy + Work Pattern + Schedule                 │
│  → Pair clock_in/out → Sessions                             │
│  → Calculate Overtime + Breaks + Lateness                   │
│  → Write Attendance Record + Sessions (DB transaction)      │
└─────────────────────────────────────────────────────────────┘
```

### Component Descriptions

**API Layer ([`ClockEventController`](../../app/Modules/Attendance/Http/Controllers/ClockEventController.php))** — Accepts clock events in Android format (`check-in`/`check-out` with millisecond timestamps), converts them to internal format, performs idempotency checks, saves raw [`ClockEvent`](../../app/Modules/Attendance/Models/ClockEvent.php) records, and dispatches [`ProcessAttendanceJob`](../../app/Modules/Attendance/Jobs/ProcessAttendanceJob.php) to the queue. Supports both single-event (`store`) and batch (`batchStore`) endpoints.

**Web Layer ([`ClockEventRecorderService`](../../app/Modules/Attendance/Services/ClockEventRecorderService.php))** — Implements the library's `ClockEventRecorder` contract. Handles browser-based clock-in/out from the My Portal dashboard. Features: employee ID resolution, company scoping, geofence validation (via [`GeofenceValidator`](../../app/Modules/Attendance/Services/GeofenceValidator.php)), 10-second idempotency window, overnight shift detection, and automatic `ProcessAttendanceJob` dispatch.

**Geofence Validator ([`GeofenceValidator`](../../app/Modules/Attendance/Services/GeofenceValidator.php))** — Validates clock-in GPS coordinates against work location boundaries using the Haversine formula. Resolution chain: employee's assigned location → company headquarters → any active non-remote location → skip. Remote locations are exempt.

**Queue Layer ([`ProcessAttendanceJob`](../../app/Modules/Attendance/Jobs/ProcessAttendanceJob.php))** — A queued job that runs after each clock event is saved. Resolves the employee, then delegates to [`AttendanceAggregator`](../../app/Modules/Attendance/Services/AttendanceAggregator.php). Configured with 3 retry attempts and exponential backoff (1 min, 5 min, 10 min).

**Aggregator ([`AttendanceAggregator`](../../app/Modules/Attendance/Services/AttendanceAggregator.php))** — The orchestrator that decides which calculation path to take. Checks for holidays first, then approved leave, then the presence of clock events. Delegates to [`AttendanceCalculator`](../../app/Modules/Attendance/Services/AttendanceCalculator.php) for full processing.

**Calculator ([`AttendanceCalculator`](../../app/Modules/Attendance/Services/AttendanceCalculator.php))** — The core computation engine. Resolves the applicable policy, work pattern, and schedule through multi-tier priority chains, processes raw clock events into paired work sessions, and computes regular/overtime/double-time hours, lateness, early departure, and break compliance.

---

## 4. Administrator Guide

### 4.1 Attendance Policies

Configured at **Attendance → Attendance Policies**.

#### All Policy Fields

| Field | Type | Default | Description |
|---|---|---|---|
| `name` | string | — | Human-readable policy name |
| `code` | string | auto | Unique policy code, auto-generated |
| `description` | text | null | Optional notes |
| `grace_period_minutes` | integer | 5 | Minutes after scheduled start before marked late (max 60) |
| `early_departure_grace_minutes` | integer | 5 | Minutes before scheduled end allowed without penalty (max 60) |
| `overtime_daily_threshold_hours` | decimal | 8 | Hours after which daily overtime begins (max 24) |
| `overtime_weekly_threshold_hours` | decimal | 40 | Cumulative regular hours after which weekly overtime begins (max 168) |
| `max_daily_overtime_hours` | decimal | 4 | Hard cap on daily overtime hours (max 24) |
| `overtime_multiplier` | decimal | 1.5 | Pay multiplier for overtime hours (1.0–3.0) |
| `double_time_threshold_hours` | decimal | 12 | Total daily hours after which double time applies (max 24) |
| `double_time_multiplier` | decimal | 2.0 | Pay multiplier for double-time hours (1.0–3.0) |
| `requires_break_after_hours` | string | `5` | Hours of continuous work after which a break is required. Supports JSON arrays |
| `break_duration_minutes` | string | `30` | Required break duration in minutes. Supports JSON arrays |
| `unpaid_break_minutes` | integer | 0 | Daily unpaid break minutes deducted from payable hours (max 240) |
| `country_code` | string | null | Applicable country (US, GB, CA, AU, IN, NG) |
| `state_code` | string | null | State/province code |
| `applies_to_shift_categories` | array | `["regular"]` | Which shift categories this policy covers |
| `effective_date` | date | required | Date the policy becomes active |
| `expiration_date` | date | null | Date the policy expires |
| `is_active` | boolean | true | Whether the policy is currently in effect |
| `is_default` | boolean | false | Whether this is the system-wide fallback policy |

#### Multi-Break Support

- **Single rule:** `requires_break_after_hours = "5"`, `break_duration_minutes = "30"` — one 30-minute break after 5 hours
- **Multiple rules:** `requires_break_after_hours = "[4, 8]"`, `break_duration_minutes = "[15, 30]"` — 15-min break after 4 hours, 30-min break after 8 hours

### 4.2 Work Patterns

Configured at **Attendance → Work Patterns**.

| Field | Type | Default | Description |
|---|---|---|---|
| `name` | string | — | Pattern name (e.g. "Standard Mon–Fri") |
| `code` | string | auto | Unique code |
| `pattern_type` | string | `recurring` | `recurring`, `rotating`, or `custom` |
| `rotation_weeks` | integer | 2 | Number of weeks in rotation cycle (2–4) |
| `shift_id` | foreign key | required | The base shift for days in this pattern |
| `applicable_days` | array | `[1,2,3,4,5]` | Day-of-week: 1=Mon through 7=Sun |
| `override_start_time` | time | null | Override the shift's start time |
| `override_end_time` | time | null | Override the shift's end time |
| `effective_date` | date | required | Date the pattern becomes active |
| `end_date` | date | null | Date the pattern expires |
| `is_active` | boolean | true | Whether the pattern is currently in effect |
| `is_default` | boolean | false | System-wide fallback pattern |

### 4.3 Shifts

Configured at **Attendance → Shifts**.

| Field | Type | Default | Description |
|---|---|---|---|
| `name` | string | — | Shift name (e.g. "Morning Shift") |
| `code` | string | auto | Unique code |
| `shift_category` | string | `regular` | `regular`, `peak`, `weekend`, `holiday`, `emergency`, `training` |
| `start_time` | time | required | Shift start time (e.g. `08:00`) |
| `end_time` | time | required | Shift end time (e.g. `17:00`) |
| `duration_hours` | decimal | computed | Total payable hours |
| `is_overnight` | boolean | false | Whether the shift crosses midnight |
| `is_active` | boolean | true | Whether the shift is available |
| `is_default` | boolean | false | System-wide fallback shift |

### 4.4 Shift Schedules

Individual overrides assigning a specific shift to a specific employee on a specific date. Highest priority in schedule resolution. Supports `start_time_override` and `end_time_override`. Only published schedules (`is_published = true`) are considered.

### 4.5 Holidays & Leave

**Holidays:** When a company holiday is detected (matching `date` or `observed_date` with `business_impact` of `office_closed` or `reduced_staff`), the attendance record is set to `status = 'holiday'` with `net_hours = 0`, `is_approved = true`, and `needs_review = false`.

**Approved Leave:** When an employee has an approved `LeaveRequest` covering the date, the attendance record is set to `status = 'leave'` with standard shift hours. The `hours_deducted` field reflects whether the leave type deducts from balance, and `is_paid_absence` reflects whether the leave is paid.

Both checks happen **before** clock event processing. If a holiday or approved leave exists, clock events for that day are ignored.

### 4.6 Geofencing & Locations

Work locations are managed at **Organization → Locations**. Each location can have GPS coordinates and a geofence radius for clock-in validation.

| Setting | Description |
|---|---|
| **Latitude / Longitude** | GPS coordinates of the location |
| **Geofence Radius** | Allowable distance in meters (default: 100m) |
| **Is Headquarters** | Used as fallback for employees without an assigned location |
| **Is Remote** | If checked, geofencing is skipped for this location |
| **Is Active** | Inactive locations are excluded from validation |

#### Validation Priority

1. Employee's assigned Location (`EmployeePosition.location_id`)
2. Company's headquarters (`is_headquarters = true`)
3. Any active, non-remote company location with coordinates
4. Skip validation (no coordinates configured)

#### Behavior Matrix

| Scenario | GPS captured? | Validated? | Location stored? | Blocked? |
|---|---|---|---|---|
| Clock-in, inside geofence | ✅ | ✅ | ✅ | ❌ |
| Clock-in, outside geofence | ✅ | ✅ | ✅ | ✅ |
| Clock-in, no GPS | ❌ | ❌ | ❌ | ❌ |
| Clock-out (any) | ✅ | ✅ (audit) | ✅ | ❌ |

> **Future:** A configurable `geofence_policy` will allow companies to choose `optional` (current), `required` (block without GPS), or `warn` (allow but flag).

### 4.7 Interpreting Attendance Records

#### All Status Values

| Status | Trigger Condition |
|---|---|
| `present` | Worked ≥ 90% of expected hours, no lateness, no early departure |
| `absent` | Zero hours worked on a scheduled day |
| `late` | Clocked in after `scheduled_start + grace_period_minutes` |
| `half_day` | Worked > 0 but ≤ 50% of expected hours |
| `incomplete` | Worked > 50% but < 90% of expected hours, or missing clock-out |
| `early_departure` | Clocked out before `scheduled_end − early_departure_grace_minutes` |
| `unscheduled` | Worked on a day not in the employee's work pattern |
| `holiday` | Company holiday — no work expected |
| `leave` | Approved leave covers this day |

#### Hour Fields

- **`regular_hours`** — Hours within the daily threshold, minus any weekly overflow into overtime
- **`overtime_hours`** — Hours beyond the daily threshold plus any weekly overflow. Capped at `max_daily_overtime_hours`
- **`double_time_hours`** — Hours beyond `double_time_threshold_hours`
- **`net_hours`** — Total payable: `regular_hours + overtime_hours + double_time_hours`, after unpaid break deduction

#### `calculation_metadata` for Debugging

```json
{
  "expected_hours": 8.0,
  "overtime_calculation": {
    "daily_regular": 8.0,
    "daily_overtime": 0.0,
    "weekly_regular_so_far": 32.0,
    "weekly_threshold": 40,
    "overflow_into_overtime": 0,
    "final_regular": 8.0,
    "final_overtime": 0.0,
    "daily_threshold": 8,
    "max_daily_overtime": 4,
    "double_time_threshold": 12
  },
  "unpaid_break_deducted": 30,
  "violations": []
}
```

---

## 5. User Guide (HR / Payroll Staff)

### Viewing Daily Attendance

Navigate to **Attendance → Attendances**. Filter by date range, employee, department, status, or `needs_review` flag. Click any record to see clock-in/out times, work sessions, hour breakdown, violations, and the policy used.

### Manual Adjustments

1. Open the attendance record
2. Click **Adjust**
3. Modify session start/end times or add missing sessions
4. Provide an adjustment reason
5. Save — the system recalculates hours

Adjustments are tracked with `is_adjusted = true`, `adjusted_by`, `adjusted_at`, and `adjustment_reason`.

### Approving Records

1. Filter by `needs_review = true`
2. Review each record's violations in `calculation_metadata`
3. If correct as-is, click **Approve** to set `is_approved = true`
4. If correction needed, create an adjustment first, then approve

### Handling Exceptions

| Scenario | Action |
|---|---|
| Employee forgot to clock out | Create an adjustment adding the missing clock-out time |
| Employee worked on a holiday | System marks as `holiday`; adjust if holiday work should be paid |
| Employee clocked in on a weekend | Record shows `unscheduled`; approve if authorized |
| Duplicate clock events | Automatically ignored — no action needed |
| Missing attendance record | Dispatch a recalculation (see [Troubleshooting](#9-troubleshooting--faq)) |
| Clock-in blocked by geofence | Employee must be within the location's geofence radius |

---

## 6. API Endpoint Reference

### POST /api/hr/attendance/store

Records a single clock event and queues attendance calculation.

**Authentication:** Required (`auth:sanctum`)

**Request Body (Android format):**

```json
{
  "employee_id": "1",
  "employee_number": "EMP-2025-001",
  "event_type": "check-in",
  "timestamp": 1766764808298,
  "device_id": "c213a4332a9f801a",
  "device_name": "INFINIX Infinix X6835B",
  "location_name": "Jahi, Federal Capital Territory, Nigeria",
  "timezone": "Africa/Lagos",
  "notes": "",
  "latitude": 9.1025352,
  "longitude": 7.4430279
}
```

| Field | Type | Required | Description |
|---|---|---|---|
| `employee_id` | string | Yes | Employee identifier (for reference) |
| `employee_number` | string | Yes | Must match an existing employee's `employee_number` |
| `event_type` | string | Yes | `check-in` or `check-out` |
| `timestamp` | numeric | Yes | Unix timestamp in **milliseconds** |
| `device_id` | string | No | Device hardware identifier |
| `device_name` | string | No | Human-readable device name |
| `location_name` | string | No | GPS-derived location description |
| `timezone` | string | No | IANA timezone (e.g. `Africa/Lagos`) |
| `notes` | string | No | Free-text notes |
| `latitude` | numeric | No | Decimal degrees (−90 to 90) |
| `longitude` | numeric | No | Decimal degrees (−180 to 180) |

**Success Response (201):**

```json
{
  "success": true,
  "message": "Clock event recorded",
  "event_id": 42,
  "data": {
    "employee_number": "EMP-2025-001",
    "timestamp": "2026-06-26 08:00:08",
    "event_type": "clock_in"
  }
}
```

**Duplicate Response (200):**

```json
{
  "success": false,
  "message": "Duplicate event ignored",
  "status": "duplicate"
}
```

### POST /api/hr/attendance/batch-store

Accepts an array of clock events in the same format. Each event is processed independently. Returns a summary with per-event results.

### Idempotency

The system prevents duplicate clock events by checking for an existing record with the same `employee_id`, `timestamp`, and `event_type` before saving (API) or within a 10-second window (web). This makes retries safe.

---

## 7. Web Clock-In Flow

The web clock-in flow uses the My Portal dashboard with browser geolocation:

1. Employee navigates to **My Portal** (`/hr/my-portal`)
2. The [`ClockInOut`](../../src/Http/Livewire/QuickActions/ClockInOut.php) Livewire component renders with the employee's ID
3. On page load, [`refreshStatus()`](../../src/Http/Livewire/QuickActions/ClockInOut.php:55) queries the latest today's event via [`ClockEventRecorderService::getLatestToday()`](../../app/Modules/Attendance/Services/ClockEventRecorderService.php:24) to determine initial state
4. On button click, Alpine.js captures `navigator.geolocation.getCurrentPosition()` (requires HTTPS)
5. Coordinates are passed to `toggle(latitude, longitude)`
6. [`ClockEventRecorderService::record()`](../../app/Modules/Attendance/Services/ClockEventRecorderService.php:82) runs geofence validation, idempotency check, saves the event, and dispatches `ProcessAttendanceJob`
7. The component updates its state and dispatches a success toast

**Overnight shifts**: If no event is found today, `getLatestToday()` checks yesterday for an unclosed clock-in session.

**Company scoping**: Clock events are created with the employee's `company_id` so they remain visible under the `HasCompanyScope` global scope after page refresh.

---

## 8. Queue & Jobs

### ProcessAttendanceJob

[`ProcessAttendanceJob`](../../app/Modules/Attendance/Jobs/ProcessAttendanceJob.php) is the queued job responsible for calculating attendance after each clock event.

**What it does:**

1. Receives an `employeeId` (integer) and `date` (string, `Y-m-d` format)
2. Looks up the employee by ID
3. Calls [`AttendanceAggregator::recalculateForDay()`](../../app/Modules/Attendance/Services/AttendanceAggregator.php:35) with the employee's `employee_number` and date
4. The aggregator checks for holidays, leave, and clock events, then delegates to the calculator

**How it's triggered:**

- Automatically dispatched by [`ClockEventController::processClockEvent()`](../../app/Modules/Attendance/Http/Controllers/ClockEventController.php:153) after saving each API clock event
- Automatically dispatched by [`ClockEventRecorderService::record()`](../../app/Modules/Attendance/Services/ClockEventRecorderService.php:148) after saving each web clock event
- Can be dispatched manually: `ProcessAttendanceJob::dispatch($employeeId, $date)`

**Retry Logic:**

| Setting | Value |
|---|---|
| `$tries` | 3 |
| `$backoff` | [60, 300, 600] (1 min, 5 min, 10 min) |

---

## 9. Troubleshooting & FAQ

### Missing Clock-Out → Attendance Marked `incomplete`

**Cause:** Employee clocked in but never clocked out. The calculator creates an orphaned session with `end_time = null` and `duration = 0`.

**Resolution:** Create an attendance adjustment to add the missing clock-out time.

### Unscheduled Day → `needs_review = true`

**Cause:** The calculation date's day-of-week is not in the work pattern's `applicable_days`.

**Resolution:** If authorized, approve the record. Update the work pattern or create a shift schedule for recurring cases.

### Incorrect Overtime → Check `calculation_metadata`

Inspect `calculation_metadata.breakdown.overtime_calculation` for the step-by-step overtime breakdown.

### Break Violations

**Cause:** The break checker looks for gaps between sessions. If sessions are contiguous, breaks aren't detected.

**Resolution:** Ensure employees clock out for breaks and clock back in. Check `calculation_metadata.breakdown.violations` for details.

### Clock-In Blocked by Geofence

**Cause:** Employee's GPS coordinates are outside the assigned location's `geofence_radius`.

**Resolution:** Employee must be physically at the work location. Check the location's coordinates and radius in **Organization → Locations**. Remote workers should have `is_remote = true` on their location.

### Web Clock-In Shows "Not Clocked In" After Refresh

**Cause:** The clock event was created without `company_id`, making it invisible under `HasCompanyScope`.

**Resolution:** This is fixed in the current version — `ClockEventRecorderService` now sets `company_id` from the employee record.

### How to Recalculate Attendance

```bash
# Via Tinker:
ProcessAttendanceJob::dispatch($employeeId, '2026-06-26');
```

Or via the aggregator:

```php
$aggregator = app(\App\Modules\Attendance\Services\AttendanceAggregator::class);
$aggregator->recalculateForDay('EMP-2025-001', '2026-06-26');
$aggregator->recalculateDateRange('EMP-2025-001', '2026-06-01', '2026-06-30');
```

### Company/Department Snapshots Not Updating

**Cause:** The `company` and `department` columns on attendance records are **historical snapshots** set only at creation time via [`getOrCreateAttendanceRecord()`](../../app/Modules/Attendance/Traits/HandlesAttendanceRecord.php). They are never updated. This is by design — records should reflect the organizational structure at the time work was performed.

---

## 10. Glossary

| Term | Definition |
|---|---|
| **Clock Event** | A raw timestamp record of an employee action (`clock_in` or `clock_out`), stored in `clock_events` |
| **Attendance Record** | The computed daily summary for one employee on one date, stored in `attendances` |
| **Attendance Session** | One continuous work period within a day, created by pairing a clock-in with a clock-out |
| **Attendance Policy** | Rules governing grace periods, overtime thresholds, break requirements, and pay multipliers |
| **Work Pattern** | Defines which days of the week an employee works and which shift applies |
| **Shift** | A named work period with start/end times |
| **Shift Schedule** | An individual override assigning a specific shift to an employee on a specific date |
| **Policy Assignment** | Polymorphic link between a policy and a company, department, location, or shift |
| **Geofence** | A virtual perimeter around a work location; clock-in outside the radius is blocked |
| **Haversine Formula** | Great-circle distance calculation between two GPS coordinates |
| **Grace Period** | Minutes after scheduled start (or before scheduled end) during which clocking in/out does not trigger a violation |
| **Overtime (Daily)** | Hours worked beyond `overtime_daily_threshold_hours` in a single day |
| **Overtime (Weekly)** | Regular hours reclassified as overtime when cumulative weekly hours exceed the threshold |
| **Double Time** | Hours worked beyond `double_time_threshold_hours`, paid at `double_time_multiplier` |
| **Break Compliance** | Verification that employees took required breaks after working continuous hours |
| **Unpaid Break** | Minutes deducted from payable hours, defined by `unpaid_break_minutes` |
| **Idempotency** | Duplicate clock events are silently ignored |
| **`net_hours`** | Payable hours after unpaid break deduction |
| **`calculation_metadata`** | JSON field storing the full step-by-step breakdown of an attendance calculation |
| **`needs_review`** | Flag indicating the record requires HR attention |
| **Orphaned Clock-In** | A clock-in event with no matching clock-out |
| **Company Scope** | Multi-tenancy mechanism ensuring data isolation between companies |
| **Snapshot** | The `company` and `department` text columns on attendance records, set at creation time and never updated |
