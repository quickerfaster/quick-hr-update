# Attendance Module — User Guide

## Overview

The Attendance module allows employees to record their working time (clock in/out) and administrators to manage attendance policies, shifts, work patterns, locations, and geofencing rules.

---

## For Employees

### Clocking In / Out

1. Navigate to **My Portal** from the sidebar or dashboard.
2. Locate the **Clock In / Out** card.
3. Click **Clock In** when you start work.
4. Click **Clock Out** when you finish.

The card shows your current state:
- **Not Clocked In** — you haven't started your work day
- **Clocked In** — shows the time you clocked in (e.g., "Clocked in at 8:00 AM")

### Location Permission

When you click Clock In or Clock Out, your browser may ask for permission to use your location. This is used to:

1. **Verify you're at an approved work location** (geofencing) — clocking in from outside the office radius is blocked
2. **Record your location for audit** — every clock event stores GPS coordinates for record-keeping

| Action | What happens |
|--------|-------------|
| You **allow** location | Your GPS coordinates are captured and validated |
| You **deny** location | Clock-in still works (depending on company policy), but no location is recorded |
| Browser on **HTTP** | Location is blocked by the browser — clock-in works without GPS |

> **Note**: If your company requires location verification and you deny permission, you may be unable to clock in. Contact your HR administrator.

### Geofence Rules

- Clocking in **outside** your assigned work location's geofence radius is **blocked** — you'll see a message like "Outside geofence (Main Office: 2174.7m > 200m)"
- Clocking out is **always allowed**, even outside the geofence — so you're never stuck clocked in
- Remote work locations are exempt from geofencing

### Understanding Your Attendance Record

- Navigate to **My Attendance** in the sidebar to see your daily records
- Each day shows:
  - **Status**: present, absent, late, half_day, leave, holiday
  - **Net Hours**: total payable hours
  - **Sessions**: individual clock-in/clock-out periods
  - **Minutes Late / Early Departure**: flagged deviations from your schedule

### Overnight Shifts

If you clock in before midnight and clock out after, the system tracks the session across days. The Clock In/Out card will still show "Clocked In" after midnight until you clock out.

---

## For Administrators (HR Role)

### Clock Events Log

Navigate to **Attendance → Clock Events** to see all raw clock events:

| Column | Description |
|--------|-------------|
| Employee | Employee number and name |
| Company | Company the employee belongs to |
| Event Type | Clock In / Clock Out |
| Event Time | Timestamp of the event |
| Clock Method | `web` (browser) or `device` (Android app) |
| Latitude / Longitude | GPS coordinates captured at the event |
| Location Name | The matched work location (if within a geofence) |
| Timezone | Timezone at the time of the event |

### Managing Work Locations (Geofencing)

Navigate to **Organization → Locations** to manage work locations.

| Setting | Description |
|---------|-------------|
| **Name** | Display name (e.g., "Head Office", "Warehouse") |
| **Type** | office, warehouse, branch, etc. |
| **Latitude / Longitude** | GPS coordinates of the location |
| **Geofence Radius** | Allowable distance in meters (default: 100m) |
| **Is Headquarters** | Used as fallback for employees without an assigned location |
| **Is Remote** | If checked, geofencing is skipped for this location |
| **Is Active** | Inactive locations are excluded from validation |

#### Geofence Validation Priority

When an employee clocks in, the system validates in this order:

1. **Employee's assigned Location** (`EmployeePosition.location_id`)
2. **Company's headquarters** (if no assigned location)
3. **Any active, non-remote company location** with coordinates
4. **Skip validation** (if no locations have coordinates configured)

### Setting Up Attendance Policies

Navigate to **Attendance → Attendance Policies**:

| Setting | Description |
|---------|-------------|
| **Grace Period** | Minutes after shift start before an arrival is considered late |
| **Early Departure Grace** | Minutes before shift end that count as early departure |
| **Unpaid Break Minutes** | Break time deducted from payable hours |
| **Overtime Daily Threshold** | Hours before daily overtime begins |
| **Overtime Weekly Threshold** | Hours before weekly overtime begins |
| **Max Daily Overtime** | Cap on daily overtime hours |
| **Double Time Threshold** | Hours before double-time rates apply |

### Setting Up Shifts & Work Patterns

- **Shifts**: Define start/end times and duration (e.g., "Day Shift 9:00-17:00")
- **Work Patterns**: Define which days of the week a shift applies (e.g., Mon-Fri)
- **Shift Schedules**: Assign specific shifts to specific employees on specific dates
- **Policy Assignments**: Attach policies to employees, departments, shifts, locations, or companies

### Attendance Aggregation

The system automatically calculates attendance from clock events:

1. **Clock events** are recorded (web or device)
2. **ProcessAttendanceJob** runs to aggregate events
3. **AttendanceCalculator** processes events into **AttendanceSessions**
4. **Attendance** record is created/updated with hours, status, and violations

Special cases handled automatically:
- **Holidays**: Marked as `holiday` with zero hours
- **Approved Leave**: Marked as `leave` with standard hours
- **Unplanned Absence**: Marked as `absent` with hours deducted

### Recalculating Attendance

If clock events were added/corrected, you can recalculate:

1. Navigate to **Attendance → Attendances**
2. Find the record
3. Click **Recalculate Hours** (requires `hr_admin` or `system_admin` role)

> **Note**: Approved attendance records cannot be recalculated until unapproved.

---

## Geofencing — Technical Notes

### How Distance Is Calculated

The system uses the **Haversine formula** to calculate great-circle distance between the employee's GPS position and the location's coordinates, in meters.

### When GPS Is Unavailable

| Cause | Behavior |
|-------|----------|
| Browser denies permission | Clock-in allowed, no location recorded |
| HTTP (non-HTTPS) origin | Clock-in allowed, no location recorded |
| Device without GPS | Clock-in allowed, no location recorded |

**Future enhancement**: A configurable `geofence_policy` will allow companies to choose:
- `optional` — allow clock-in without GPS (current)
- `required` — block clock-in without GPS
- `warn` — allow but flag for manager review

### Audit Trail

Every clock event stores:
- GPS coordinates (when available)
- Matched location name (when within geofence)
- IP address
- Device name (user agent)
- Timezone

This provides a complete audit trail for compliance and dispute resolution.
