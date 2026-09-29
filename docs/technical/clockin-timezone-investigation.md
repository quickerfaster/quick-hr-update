# Clock-In Timezone Discrepancy — Investigation Report

> **Date**: 2026-09-28
> **Status**: ✅ RESOLVED — Options A, B, and C implemented (see §8).
> **Symptom**: ESS overview clocking gadget shows time one hour *earlier* than actual (12:36 PM → 11:36 AM). Same wrong time persists in `clock_events.timestamp`. Changing timezone in My Preferences to `Africa/Lagos` has no effect.
> **Root cause (single sentence)**: The app runs on UTC, `Carbon::now()` captures UTC, the display formats the UTC value **without converting** to the user's timezone, and the user's timezone preference is stored but **never read** anywhere in the clock-in pipeline.

---

## 1. The One-Hour Offset — Why It Happens

`Africa/Lagos` is **UTC+1** (no DST). An employee physically in Lagos clocks in at **12:36 PM local**. The exact chain of events:

| Step | Code | Result |
|------|------|--------|
| 1. App timezone | [`config/app.php:68`](config/app.php:68) → `'timezone' => 'UTC'` | PHP/Carbon default timezone = UTC |
| 2. Timestamp captured | [`ClockEventRecorderService::record()`](app/Modules/Attendance/Services/ClockEventRecorderService.php:80) → `$now = Carbon::now()` | `Carbon::now()` = **11:36 AM UTC** (12:36 Lagos − 1h) |
| 3. Timestamp stored | same method line 146 → `'timestamp' => $now` | DB stores `2026-09-28 11:36:00` (UTC) |
| 4. `timezone` column | same method line 153 → `$meta['timezone'] ?? config('app.timezone')` | Stored as `'UTC'` (ClockInOut never sends browser timezone) |
| 5. Display | [`ClockInOut::formatTime()`](app/Modules/Attendance/Http/Livewire/ClockInOut.php:147) → `Carbon::parse($timestamp)->format('g:i A')` | Displays raw stored value **11:36 AM** — no `setTimezone()` call |

**Conclusion**: The timestamp itself is stored correctly *in UTC* (which is actually best practice), but the **display path never converts UTC → user timezone**. The one-hour error is a **presentation bug**, compounded by the fact that no code consumes the user's timezone preference.

---

## 2. Timestamp Generation Across the Full Clock-In Flow

### 2.1 Web path (ESS gadget)

```
ClockInOut.toggle()                        [app/Modules/Attendance/Http/Livewire/ClockInOut.php:87]
  → ClockEventRecorderService.record()     [app/Modules/Attendance/Services/ClockEventRecorderService.php:77]
    → $now = Carbon::now()                 ← line 80, uses config('app.timezone') = UTC
    → ClockEvent::create(['timestamp' => $now])
    → returns $now->toIso8601String()
  → ClockInOut.formatTime()                [ClockInOut.php:140]
    → Carbon::parse($timestamp)->format('g:i A')   ← line 147, NO timezone conversion
```

Key details:
- **Idempotency window** (line 129–133) also uses `$now` in UTC — harmless but UTC-based.
- **Overnight detection** [`getLatestToday()`](app/Modules/Attendance/Services/ClockEventRecorderService.php:31) uses `Carbon::today()` (UTC) and `whereDate('timestamp', $today)` — this means "today" is evaluated in **UTC**, not the user's local date. For Lagos (UTC+1), a clock-in at 12:30 AM local is still "yesterday" in UTC. This is a **secondary date-boundary bug**.

### 2.2 API path (mobile)

[`ClockEventController`](app/Modules/Attendance/Http/Controllers/ClockEventController.php) accepts the client-supplied `timezone` field (line 59, 88) and a **millisecond** timestamp. It converts to a Carbon instance. The API is slightly better positioned because it *receives* a timezone string, but the underlying stored `timestamp` is still normalized through the same Carbon/app-timezone mechanism unless explicitly converted.

### 2.3 The `timezone` column is metadata only

[`clock_events` migration](app/Modules/Attendance/Database/Migrations/2026_06_12_142529_create_clock_events_table.php:23) defines `timezone` as a plain `string` with default `'UTC'`. It is **informational** (audit) and is never used to drive display conversion. The model cast ([`ClockEvent.php`](app/Modules/Attendance/Models/ClockEvent.php:41)) casts `timestamp` → `datetime` (Carbon), but Carbon's default timezone is the app timezone (UTC).

---

## 3. Timezone Preference Retrieval — The Three Modes

The library documents a **three-tier settings cascade** (user → company → system). Here is the *actual* state of each tier.

### 3.1 User-level preference (My Preferences)

- **Storage**: Works. [`SettingsPanel::saveSetting()`](src/Http/Livewire/Settings/SettingsPanel.php:225) → `safeSetSetting()` → [`HasSettings::setSetting()`](src/Traits/HasSettings.php:28) → writes `system_settings` row via the `settingable` morph on the `User` model.
- The User model gets settings capability via [`HasUILibraryUser`](src/Traits/HasUILibraryUser.php:36) which composes [`HasSettings`](src/Traits/HasSettings.php:8).
- **Read**: Available via `Auth::user()->getSetting('timezone')`, but **the clock-in flow never calls it**. Confirmed by search: `getSetting('timezone')` appears nowhere in the Attendance module.

### 3.2 Module/company defaults

- [`SettingsManager`](src/Services/Settings/SettingsManager.php:7) is the intended cascade engine (user → company → system), but its resolvers are **all null**:

```php
// src/Config/ui-library.php:141  (and identical in consuming app config/ui-library.php:141)
'settings' => [
    'resolvers' => [
        'user' => null,
        'company' => null,
        'system' => null,
    ],
],
```

- The `SettingsManager` is bound in [`UILibraryServiceProvider`](src/Providers/UILibraryServiceProvider.php:30) and iterates resolvers, but with all three `null`, `get()` simply returns the fallback `$default`. **The cascade is not wired.**

### 3.3 System-level default

- [`config/app.php:68`](config/app.php:68) → `'timezone' => 'UTC'` is the only *effective* timezone in the entire runtime.
- [`SystemSettingsSeeder`](src/Core/System/Database/Seeders/SystemSettingsSeeder.php:16) seeds `'timezone' => env('APP_TIMEZONE', 'UTC')`, but nothing reads `system_settings` for timezone in the clock-in flow.

### 3.4 Summary table

| Tier | Config/Model | Stored? | Read by clock-in flow? |
|------|-------------|---------|------------------------|
| User | `system_settings` via `HasSettings` | ✅ Yes | ❌ **No** |
| Company | `companies.timezone` | ✅ Yes | ❌ No |
| System | `config('app.timezone')` = UTC | n/a | ✅ Indirectly via `Carbon::now()` |

---

## 4. Why the Preference Change Does Not Propagate

There are **two independent reasons**, and both must be addressed:

1. **No read path exists.** Changing the timezone in My Preferences successfully persists `system_settings.value = 'Africa/Lagos'` for the user, but zero code in the clock-in pipeline (`ClockInOut`, `ClockEventRecorderService`, `ClockEvent`, the Blade view) reads that value.

2. **The resolution cascade is disabled.** Even if code *tried* to use `SettingsManager::get('timezone')`, all three resolvers (`user`, `company`, `system`) are `null`, so the manager returns the fallback default — never the stored user value.

Additionally, a **third contributing factor**: `ClockInOut` never transmits the browser's actual timezone (`Intl.DateTimeFormat().resolvedOptions().timeZone`) into `record()`'s `$meta`, so the server cannot even fall back to the device's timezone.

---

## 5. Actionable Solutions (No Code Changes Yet)

Solutions are ordered from **fastest temporary fix** to **most correct long-term fix**.

### Option A — Temporary: Convert at display only (minimal, safest quick win)

**What**: In `ClockInOut::formatTime()`, convert the stored UTC timestamp to the user's preferred timezone before formatting.

**Files involved**:
- [`app/Modules/Attendance/Http/Livewire/ClockInOut.php`](app/Modules/Attendance/Http/Livewire/ClockInOut.php:140)

**Concept** (illustrative, not applied):
```php
protected function formatTime(?string $timestamp): ?string
{
    if (!$timestamp) return null;
    $tz = Auth::user()?->getSetting('timezone') ?: config('app.timezone', 'UTC');
    return Carbon::parse($timestamp, 'UTC')->setTimezone($tz)->format('g:i A');
}
```

**Pros**: One-line-ish fix; directly resolves the visible one-hour error; no schema change.
**Cons**: Only fixes the gadget display; does **not** fix the `ClockEvent` audit display elsewhere, the `date`-boundary bug in `getLatestToday()`, or the API path.
**Preference-mode alignment**: Reads **user-level** preference directly; falls back to **system** (`config('app.timezone')`). Skips the (unwired) `SettingsManager`.

---

### Option B — Temporary: Wire the `user` resolver in `SettingsManager`

**What**: Register a callable `user` resolver so `SettingsManager::get('timezone')` actually reads `Auth::user()->getSetting('timezone')`.

**Files involved**:
- [`config/ui-library.php`](config/ui-library.php:141) (consuming app copy) — set `'user' => fn(...)` resolver.

**Concept**:
```php
'settings' => [
    'resolvers' => [
        'user' => function (string $key) {
            $user = auth()->user();
            return $user && method_exists($user, 'getSetting') ? $user->getSetting($key) : null;
        },
        'company' => null,
        'system' => null,
    ],
],
```

**Pros**: Activates the library's intended cascade; any future code that calls `SettingsManager::get('timezone')` gets the right value.
**Cons**: On its own, still does nothing for the clock-in flow because **nothing calls `SettingsManager`** there yet — must be paired with Option A/C.
**Preference-mode alignment**: Restores **user-level** resolution through the documented mechanism.

---

### Option C — Preferred long-term: Single `UserTimezone` resolver + apply in both write and display paths

**What**: Introduce one canonical timezone-resolution helper, then use it in (1) display, (2) date-boundary logic, and optionally (3) timestamp write.

**Files involved**:
- New helper (e.g. `app/Modules/Attendance/Services/UserTimezone.php` or a consuming-app `SettingsManager` resolver)
- [`ClockInOut.php`](app/Modules/Attendance/Http/Livewire/ClockInOut.php:140) — display conversion
- [`ClockEventRecorderService.php`](app/Modules/Attendance/Services/ClockEventRecorderService.php:31) — `getLatestToday()` "today" boundary
- Optionally [`ClockEventRecorderService.php:80`](app/Modules/Attendance/Services/ClockEventRecorderService.php:80) — timestamp generation

**Resolution priority (aligns with the three modes)**:
1. **User** → `Auth::user()->getSetting('timezone')`
2. **Company** → employee's company `timezone`
3. **System** → `config('app.timezone', 'UTC')`

**Concept**:
```php
// helper: resolveTimeZone(?Employee $employee): string
$tz = Auth::user()?->getSetting('timezone')
    ?? $employee?->company?->timezone
    ?? config('app.timezone', 'UTC');
```

For **display**:
```php
Carbon::parse($timestamp, 'UTC')->setTimezone($tz)->format('g:i A');
```

For **date boundary** (`getLatestToday`):
```php
$today = Carbon::now($tz)->toDateString();
// and whereDate('timestamp', $today) with the timestamp interpreted in $tz
```

**Pros**: Complete, correct, consistent; fixes gadget + audit + date boundary; honors all three preference modes.
**Cons**: Larger change; touches multiple files; requires deciding whether `timestamp` stays UTC-in-DB (recommended) vs. local-time-in-DB.

**Preference-mode alignment**: Full user → company → system cascade, exactly matching the library's documented three-tier design.

---

### Option D — Store in user-local time instead of UTC (not recommended)

**What**: Set `date_default_timezone_set($userTz)` or `Carbon::now($userTz)` at write time, and store local wall-clock time.

**Pros**: Existing display code "just works" without conversion.
**Cons**: ❌ Strongly discouraged — breaks cross-company/cross-timezone reporting, complicates overtime/payroll math, and makes historical records ambiguous when a user changes timezone or travels. Competitors (BambooHR, Gusto, ADP) universally store UTC and convert at the presentation layer.

**Preference-mode alignment**: Only **user-level**, and it leaks presentation concerns into the data layer.

---

### Option E — Middleware to set request timezone (complement, not a fix by itself)

**What**: Add middleware that calls `date_default_timezone_set($resolvedTz)` and `config(['app.timezone' => $resolvedTz])` per request.

**Pros**: Makes all `Carbon::now()` calls (and date helpers) honor the user's timezone globally.
**Cons**: Dangerous for **async/queued jobs** (payroll, attendance aggregation) where there is no authenticated user; can produce inconsistent `now()` values between request and queue. Use only as a complement to explicit conversion.

---

## 6. Recommendation Matrix

| Option | Effort | Correctness | Preference modes honored | Risk |
|--------|--------|-------------|--------------------------|------|
| A — display-only | 🟢 Low | Partial | User + system fallback | Low |
| B — wire resolver | 🟢 Low | Partial (needs A/C) | User | Low |
| **C — canonical resolver** | 🟡 Medium | ✅ Full | User + company + system | Low |
| D — store local time | 🟡 Medium | ❌ Poor | User only | **High** |
| E — middleware | 🟡 Medium | Partial | User | Medium (queue risk) |

**Recommended path**: Apply **Option A immediately** as a stopgap (single file, resolves the user-facing symptom), then implement **Option C** as the durable fix (covers audit logs, date boundaries, and all preference modes). Avoid Option D.

---

## 7. Affected Code Index

| Area | File | Line | Issue |
|------|------|------|-------|
| App timezone default | [`config/app.php`](config/app.php:68) | 68 | `'timezone' => 'UTC'` |
| Timestamp capture (UTC) | [`ClockEventRecorderService.php`](app/Modules/Attendance/Services/ClockEventRecorderService.php:80) | 80 | `Carbon::now()` |
| `timezone` column default | [`ClockEventRecorderService.php`](app/Modules/Attendance/Services/ClockEventRecorderService.php:153) | 153 | `config('app.timezone')` |
| Display without conversion | [`ClockInOut.php`](app/Modules/Attendance/Http/Livewire/ClockInOut.php:147) | 147 | `format('g:i A')` |
| UTC "today" boundary | [`ClockEventRecorderService.php`](app/Modules/Attendance/Services/ClockEventRecorderService.php:31) | 31 | `Carbon::today()` |
| Resolver cascade (all null) | [`ui-library.php`](config/ui-library.php:141) | 141–146 | user/company/system = null |
| Preference storage | [`HasSettings.php`](src/Traits/HasSettings.php:28) | 28 | `setSetting()` works |
| Preference save flow | [`SettingsPanel.php`](src/Http/Livewire/Settings/SettingsPanel.php:225) | 225 | saves but nothing reads it |
| Timezone field metadata | [`clock_events` migration](app/Modules/Attendance/Database/Migrations/2026_06_12_142529_create_clock_events_table.php:23) | 23 | string, not used for display |
| `timestamp` cast | [`ClockEvent.php`](app/Modules/Attendance/Models/ClockEvent.php:41) | 41 | `datetime` (app tz) |

---

## 8. Resolution Implemented (2026-09-28)

The recommended combination of **Option A + Option C** was implemented, plus **Option B** to re-activate the library's settings cascade.

### Files changed

| File | Change |
|------|--------|
| [`UserTimezone.php`](app/Modules/Attendance/Services/UserTimezone.php) | **New** canonical resolver: user preference → company timezone → system default |
| [`ClockInOut.php`](app/Modules/Attendance/Http/Livewire/ClockInOut.php:144) | `formatTime()` now converts UTC → employee timezone (`g:i A`) |
| [`ClockInOut.php`](app/Modules/Attendance/Http/Livewire/ClockInOut.php:169) | **New** `formatFullDateTime()` converts UTC → employee timezone (`M j, Y g:i A`) |
| [`clock-in-out.blade.php`](app/Modules/Attendance/Resources/views/livewire/clock-in-out.blade.php:33) | Replaced raw `Carbon::parse(...)->format(...)` with `$this->formatFullDateTime($lastEventAt)` |
| [`ClockEventRecorderService.php`](app/Modules/Attendance/Services/ClockEventRecorderService.php:28) | `getLatestToday()` computes local "today" boundaries via `UserTimezone` and converts to UTC |
| [`ClockEventRecorderService.php`](app/Modules/Attendance/Services/ClockEventRecorderService.php:88) | `record()` persists the resolved timezone in `clock_events.timezone` (timestamps stay UTC) |
| [`config/ui-library.php`](config/ui-library.php:141) | Wired the `user` resolver (was `null`) so `SettingsManager` reads the user's stored preference |

### Outcome

- The gadget now renders both the "Clocked in at" time and the full date-time in the employee's timezone (e.g. `1:18 PM` / `Sep 28, 2026 1:18 PM` for a Lagos user).
- `clock_events.timestamp` remains stored in UTC (best practice for cross-company reporting and payroll math).
- `clock_events.timezone` now records the employee's effective timezone for audit.
- Overnight shifts are assigned to the correct local calendar day.

*End of Report*
