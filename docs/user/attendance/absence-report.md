# Absence Report — User Guide

## Overview

The Absence Report feature lets employees quickly notify their manager of an unplanned absence — sickness, family emergency, personal matter, or official company assignment — without going through the full leave request process. It's designed for speed: one step, minimal fields, and an optional automatic draft leave request for formal tracking.

> **Related**: [Attendance Guide](README.md) · [Leave Guide](../leave/README.md) · [HR Guide](../hr/README.md)

---

## Table of Contents

1. [What Is an Absence Report?](#1-what-is-an-absence-report)
2. [How to Access It](#2-how-to-access-it)
3. [Filling Out the Form](#3-filling-out-the-form)
4. [Absence Types Explained](#4-absence-types-explained)
5. [What Happens After You Submit](#5-what-happens-after-you-submit)
6. [Absence Reports vs Leave Requests](#6-absence-reports-vs-leave-requests)
7. [For Administrators](#7-for-administrators)
8. [Troubleshooting](#8-troubleshooting)

---

## 1. What Is an Absence Report?

An absence report is a **quick notification** — not a formal leave request. Think of it as the digital equivalent of calling in sick. It tells your manager you won't be in today (or for a few days) and why, without requiring approval workflows, balance checks, or multi-step forms.

Key characteristics:

| | Absence Report | Leave Request |
|---|---|---|
| **Purpose** | Notify of unplanned absence | Request planned time off |
| **Approval** | None (notification only) | Multi-step workflow |
| **Balance check** | No | Yes |
| **Form steps** | 1 | 3+ |
| **Creates attendance record** | Yes | Yes |
| **Creates leave request** | Optional (draft) | Always |

---

## 2. How to Access It

There are three ways to open the absence report form:

### From My Portal (Home Dashboard)

1. Navigate to **My Portal** (`/hr/my-portal`).
2. Find the **Report Absence** action card (orange/warning color, phone icon).
3. Click **Report** — the form opens in a drawer overlay.

### From the Leave Hub

1. Navigate to **Leave Hub** (`/hr/leave-hub`).
2. On the **Overview** tab, find the **Report Absence** action card.
3. Click **Report** — the form opens in a drawer overlay.
4. Alternatively, click the **Report Absence** tab at the top of the page.

### From Quick Actions (Cmd+K / Ctrl+K)

1. Press **Cmd+K** (Mac) or **Ctrl+K** (Windows/Linux).
2. Type "absence" or "sick" to filter.
3. Select **Report Absence** from the list.

---

## 3. Filling Out the Form

The form has four fields and two checkboxes:

| # | Field | Type | Required | Description |
|---|-------|------|----------|-------------|
| 1 | **Absence Type** | Dropdown | ✅ Yes | The reason category for your absence |
| 2 | **Start Date** | Date picker | ✅ Yes | When your absence begins (today or earlier) |
| 3 | **Expected Duration** | Dropdown | No | How long you expect to be away |
| 4 | **Reason** | Text area | ✅ Yes | Brief explanation (10–500 characters) |
| 5 | **Notify manager immediately** | Checkbox | — | Sends a notification to your line manager (default: ON) |
| 6 | **Create draft leave request** | Checkbox | — | Creates a draft leave request for formal tracking (default: ON) |

### Field Details

#### Absence Type

Choose the category that best describes your situation. See [§4 Absence Types Explained](#4-absence-types-explained) for guidance on which to pick.

#### Start Date

- Defaults to **today's date**.
- You can select a **past date** (e.g., if you're reporting yesterday's absence).
- You **cannot** select a future date — absence reports are for unplanned absences that have already started.

#### Expected Duration

| Value | Label | Use When |
|-------|-------|----------|
| `1` | 1 day | You expect to be back tomorrow |
| `3` | 2–3 days | Short illness or personal matter |
| `5` | 4–5 days (about a week) | Moderate illness or emergency |
| `14` | 1–2 weeks | Extended absence |
| `28` | 3–4 weeks (about a month) | Serious medical or family situation |
| `60` | 1–2 months | Long-term absence |
| `0` | Uncertain | You don't know how long you'll be away |

> **Tip**: The duration is an estimate for your manager's planning. You can update it later if your situation changes.

#### Reason

- Minimum **10 characters**.
- Maximum **500 characters**.
- Be specific enough that your manager understands the situation, but you don't need to share private medical details.

**Good examples**:
- "Woke up with a fever and sore throat. Will see a doctor today."
- "Family emergency requiring my immediate attention. Will update tomorrow."
- "Attending a 3-day training workshop at the Lagos office as directed by management."

**Poor examples**:
- "Sick" (too short — minimum 10 characters)
- "Out" (too vague)

#### Notify Manager Immediately

- **Default: ON** — your line manager receives a notification as soon as you submit.
- Turn it off if you've already spoken to your manager directly.

#### Create Draft Leave Request

- **Default: ON** — the system creates a draft leave request linked to your absence.
- This ensures your absence is **formally tracked** and your leave balance is updated.
- The draft is **not submitted** — you can complete and submit it later from **My Leaves**.
- Turn it off for **Official Assignment** absences (these don't deduct from your leave balance).

> **Important**: If you turn off "Create draft leave request" for a Sick Leave or Personal Matter absence, your leave balance won't be updated. You'll need to create a leave request manually later.

---

## 4. Absence Types Explained

| Type | Value | Paid? | Deducts Balance? | Use For |
|------|-------|-------|------------------|---------|
| **Sick Leave** | `sick_leave` | Depends on policy | Yes (if leave request created) | Illness, injury, medical appointments |
| **Family Emergency** | `emergency` | Depends on policy | Yes (if leave request created) | Urgent family matters requiring your presence |
| **Personal Matter** | `personal` | Depends on policy | Yes (if leave request created) | Personal reasons not covered by other categories |
| **Official Assignment** | `official_assignment` | ✅ Yes | ❌ No | Company-directed duty: training, business trips, secondments, off-site meetings |
| **Other** | `other` | Depends on policy | Depends | Anything not covered above — explain in the reason field |

### Official Assignment — Special Rules

**Official Assignment** is for management-directed work outside your normal workplace. Key differences from other absence types:

- **Paid** — your regular hours are credited (no pay deduction).
- **Not deducted from leave balance** — this is work, not time off.
- **No leave request needed** — the "Create draft leave request" checkbox can be turned off.
- **Examples**: attending a training course, representing the company at a conference, temporary secondment to another branch, off-site client meetings.

> **Note**: Official Assignment is not for personal errands or working from home by choice. It's for company-directed duty that takes you away from your normal work location.

---

## 5. What Happens After You Submit

### Immediate Effects

1. **Attendance record created** — an attendance entry is logged for the date with your absence type and reason.
2. **Manager notified** — if "Notify manager immediately" was checked, your line manager receives a notification.
3. **Draft leave request created** — if "Create draft leave request" was checked, a draft appears in your **My Leaves** tab.

### Completion Screen

After submitting, you'll see a confirmation with three options:

| Action | Where It Goes |
|--------|---------------|
| **View My Attendance** | Your attendance history (`/hr/my-profile?tab=attendance`) |
| **Complete Leave Request** | The draft leave request for you to finalize and submit |
| **Back to Leave Hub** | The Leave Hub overview |

### What Your Manager Sees

Your manager receives:
- A notification that you've reported an absence
- The absence type, date, expected duration, and reason
- A link to your attendance record

### Completing the Draft Leave Request

If you chose to create a draft leave request:

1. Go to **Leave Hub → My Leaves**.
2. Find the draft (status: "Draft").
3. Review the dates — the start date is pre-filled from your absence report.
4. Set the **end date** based on your expected duration.
5. Add any additional details.
6. Click **Submit** to send it through the approval workflow.

> **Tip**: You don't have to submit the draft immediately. You can wait until you know your exact return date.

---

## 6. Absence Reports vs Leave Requests

Use this decision guide to choose the right tool:

| Scenario | Use |
|----------|-----|
| You woke up sick and can't come in today | **Absence Report** |
| You have a family emergency and need to leave immediately | **Absence Report** |
| You're planning a vacation next month | **Leave Request** |
| You have a scheduled doctor's appointment | **Leave Request** |
| Your manager assigned you to a 3-day training | **Absence Report** (Official Assignment) |
| You want to take a personal day next Friday | **Leave Request** |
| You're already absent and need to notify retroactively | **Absence Report** (backdate the start date) |

### How They Work Together

The absence report and leave request systems are designed to complement each other:

1. **Report the absence** — quick notification when the absence starts.
2. **Complete the leave request** — formal tracking when you know the full details.

This two-step approach means your manager knows immediately, but your leave balance and payroll are handled properly through the formal leave process.

---

## 7. For Administrators

### How Absence Reports Appear in the System

When an employee submits an absence report, the system creates an **Attendance** record with:

| Field | Value |
|-------|-------|
| `status` | `absent` |
| `absence_type` | The type selected by the employee |
| `absence_reason` | The reason text |
| `is_paid_absence` | Set automatically by payroll (not employee-editable) |
| `hours_deducted` | Calculated automatically (not employee-editable) |
| `is_approved` | `false` (requires admin review) |
| `needs_review` | `true` |

### Reviewing Absence Records

1. Navigate to **Attendance → Attendances**.
2. Filter by `needs_review = true` to see records requiring attention.
3. Review each record:
   - Verify the absence type matches company policy.
   - Check if a corresponding leave request was created.
   - Adjust `is_paid_absence` and `hours_deducted` if needed.
4. Click **Approve** to set `is_approved = true`.

> **Important**: Unapproved absence records are excluded from payroll. Always review and approve absence records before processing payroll for the affected period.

### Paid vs Unpaid Absence

The `is_paid_absence` field determines how the absence affects payroll:

| `is_paid_absence` | Effect |
|-------------------|--------|
| `true` | Hours are credited — employee is paid for the day |
| `false` | Hours are deducted — employee is not paid for the day |

This field is **not employee-editable** — it's set by payroll/HR based on company policy and the absence type. Official Assignments are always paid; other types depend on your company's leave and attendance policies.

### Absence Details Group

On the Attendance form, absence-related fields are grouped under **Leave & Absence**:

- `leave_request_id` — links to the draft leave request (if created)
- `absence_type` — the category selected by the employee
- `absence_reason` — the explanation text
- `is_paid_absence` — payroll flag (admin-only)
- `hours_deducted` — calculated deduction (admin-only)

---

## 8. Troubleshooting

### "Reason must be at least 10 characters"

**Cause**: The reason field requires a minimum of 10 characters.

**Fix**: Write a brief but complete explanation. For example, "Feeling unwell with a fever" is 28 characters and sufficient.

### "Start date cannot be in the future"

**Cause**: Absence reports are for unplanned absences that have already begun. You cannot report a future absence.

**Fix**: If you know about an absence in advance, use the **Leave Request** form instead (Leave Hub → Apply tab).

### "Please select an absence type"

**Cause**: The absence type dropdown is required.

**Fix**: Choose the category that best matches your situation. If none fit exactly, select **Other** and explain in the reason field.

### Draft Leave Request Not Appearing

**Cause**: The "Create draft leave request" checkbox may have been turned off, or there may be a delay in processing.

**Fix**:
1. Check that the checkbox was ON when you submitted.
2. Navigate to **Leave Hub → My Leaves** and refresh.
3. If it still doesn't appear, create a leave request manually from the **Apply** tab.

### Manager Didn't Receive Notification

**Cause**: The "Notify manager immediately" checkbox may have been turned off, or your employee record may not have a manager assigned.

**Fix**:
1. Verify your employee position has a **Reports To** manager assigned (check with HR).
2. For future reports, ensure the checkbox is ON.
3. Notify your manager directly if the automated notification didn't go through.
