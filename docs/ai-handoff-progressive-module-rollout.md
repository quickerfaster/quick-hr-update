# AI Handoff Prompt — Progressive Module Rollout

Paste this entire message into a new session.

---

## Context

I'm building an HR application on top of the QuickerFaster UI Library (`quicker-faster/ui-library`). The app follows a modular architecture under `app/Modules/{ModuleName}/`. The library provides the DataTable, Form, Navigation, Workflow, and multi-tenant infrastructure.

We just completed a comprehensive Payroll investigation and implementation (8 recommendations), and a Holiday module implementation. All changes are committed and pushed to both repos.

## Task: Progressive Module Rollout

We want to deploy the app incrementally — starting with only **HR and Admin modules**, then progressively unlocking additional modules (Attendance, Leave, Payroll, etc.) as users become comfortable and each module stabilizes.

### Phase Plan

| Phase | Modules | What Happens |
|-------|---------|-------------|
| **Phase 1** | HR, Admin, Organization | Deploy. Guide users. Fix bugs. Stabilize. |
| **Phase 2** | + Attendance | Unlock. Guide users. Fix bugs. Stabilize. |
| **Phase 3** | + Leave | Unlock. Guide users. Fix bugs. Stabilize. |
| **Phase 4** | + Payroll | Unlock. Guide users. Fix bugs. Stabilize. |
| **Phase 5** | + Loans, Holiday, Policies | Unlock. Full app. |

### How Module Enable/Disable Works

The module registry is in [`config/ui-library.php`](config/ui-library.php:11). Each module has an `enabled` key:

```php
'modules' => [
    'hr' => [
        'enabled' => true,   // ← Set to false to hide
        'label' => 'HR',
        'user_facing' => true,
        ...
    ],
]
```

**System behavior when `enabled = false`:**
- Module is excluded from navigation (sidebar, top nav, module switcher) — see [`NavigationHub.php:47`](src/Http/Livewire/Layouts/Navs/NavigationHub.php:47) and [`Sidebar.php:197`](src/Http/Livewire/Layouts/Navs/Sidebar.php:197)
- Module is excluded from user-facing module lists — see [`ModuleServiceProvider.php:247`](src/Providers/ModuleServiceProvider.php:247)
- Quick actions depending on disabled modules are hidden — see [`ActionRegistry.php:155`](src/Services/QuickActions/ActionRegistry.php:155)
- **Routes are still accessible** unless explicitly guarded — disabling a module doesn't block its routes
- **Service providers still boot** — they're registered in [`bootstrap/providers.php`](bootstrap/providers.php) and must run for migrations, relationships, etc.

### Additional Hiding Strategy

For a cleaner Phase 1, also:
1. **Navigation items**: Individual nav items in each module's `Config/navigation.php` can be disabled
2. **Dashboard widgets**: Module dashboards can be hidden from the main dashboard
3. **DataTable configs**: Can remain — they won't be discovered if the module route prefix isn't used
4. **Context groups**: In `config/ui-library.php` → `context_groups`, non-HR contexts can be hidden

### Phase 1 Implementation Steps

1. **Disable non-Phase-1 modules** in `config/ui-library.php` by setting `enabled => false` for: `attendance`, `leave`, `payroll`, `loans`, `holiday`, `policies`
2. **Keep enabled**: `hr`, `admin`, `organization`, `system`, `common`
3. **Set `user_facing` to `true`** for `hr` module (it should already be)
4. **Run `php artisan optimize:clear`**
5. **Verify**: Sidebar shows only HR-related navigation. Module switcher shows only HR.
6. **Deploy** and guide users through employee management, positions, departments, locations, onboarding
7. **Collect bugs**, fix, redeploy until stable
8. **Proceed to Phase 2**

### What You'll Need to Do Each Phase

For each module being unlocked:
1. Set `enabled => true` in `config/ui-library.php`
2. `php artisan optimize:clear`
3. Guide users through the new module's features
4. Monitor for issues and fix
5. Repeat until stable, then move to next phase

### Key Files for Module Registration

| File | Purpose |
|------|---------|
| [`config/ui-library.php:11-45`](config/ui-library.php:11) | Module registry with `enabled` flag |
| [`bootstrap/providers.php`](bootstrap/providers.php) | Service provider registration (all modules) |
| [`src/Providers/ModuleServiceProvider.php:247`](src/Providers/ModuleServiceProvider.php:247) | Module filtering by `enabled` + `user_facing` |
| [`src/Http/Livewire/Layouts/Navs/NavigationHub.php:47`](src/Http/Livewire/Layouts/Navs/NavigationHub.php:47) | Navigation hub module filtering |
| [`src/Http/Livewire/Layouts/Navs/Sidebar.php:197`](src/Http/Livewire/Layouts/Navs/Sidebar.php:197) | Sidebar module filtering |

### Existing Modules

| Module | Dir | Phase |
|--------|-----|-------|
| HR | `app/Modules/Hr/` | Phase 1 |
| Admin | (library core) | Phase 1 |
| Organization | `app/Modules/Organization/` | Phase 1 |
| Attendance | `app/Modules/Attendance/` | Phase 2 |
| Leave | `app/Modules/Leave/` | Phase 3 |
| Payroll | `app/Modules/Payroll/` | Phase 4 |
| Loans | `app/Modules/Loans/` | Phase 5 |
| Holiday | `app/Modules/Holiday/` | Phase 5 |
| Policies | `app/Modules/Policies/` | Phase 5 |

---

## Project Structure

```
Library:          /Users/mac/Projects/Libraries/ui-library/
Consuming App:    /Users/mac/Projects/LaravelProjects/hr-consuming-app/
HR Module:        app/Modules/Hr/
Admin:            Library core + app/Modules/Organization/
Key Docs:         docs/library/ (library), docs/consuming-app/ + docs/user/ (consuming app)
```

## UI Library Philosophy

**Two-Domain Test**: Library code (`src/`) must NEVER reference `App\Modules\*`. Ask: "Would this work in a CRM?" If no, it belongs in the consuming app. See [`docs/library/25-library-independence-safeguards.md`](docs/library/25-library-independence-safeguards.md).

**Architecture Rules:**
- Consuming app code can reference library contracts and models ✅
- Library code must NOT reference consuming app modules ❌
- Use contracts and service container binding for cross-boundary communication
- All module files must be self-contained under `app/Modules/{ModuleName}/`

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
- [ ] If library files are modified, copy to `vendor/quicker-faster/ui-library/` AND `resources/views/vendor/qf/` (published views take precedence!)
- [ ] Check `storage/framework/views/*.php` for stale compiled views
- [ ] Clear `bootstrap/cache/` if config changes
- [ ] No `App\Modules\*` references in library `src/` files
- [ ] Verify existing module functionality isn't broken
- [ ] Run `php artisan queue:restart` after code changes if queue workers are running

## Database Connection

```
DB_CONNECTION: mysql
DB_HOST: 127.0.0.1
DB_PORT: 3306
DB_DATABASE: honestee
DB_USERNAME: root
DB_PASSWORD: Honestee
```

Test DB (`phpunit.xml`): `DB_DATABASE: honestee_test`

## Key Documentation References

- [`docs/library/25-library-independence-safeguards.md`](docs/library/25-library-independence-safeguards.md) — The two-domain test
- [`docs/library/27-architecture-boundary.md`](docs/library/27-architecture-boundary.md) — Boundary rules
- [`docs/consuming-app/pre-coding-checklist.md`](docs/consuming-app/pre-coding-checklist.md) — Pre-coding steps
- [`docs/user/payroll/README.md`](docs/user/payroll/README.md) — Payroll user guide (recently updated)
- [`docs/technical/payroll-calculation-parameters-analysis.md`](docs/technical/payroll-calculation-parameters-analysis.md) — Resolution status

## Known Issues from Previous Sessions

1. **Published views**: `resources/views/vendor/qf/` contains 130+ published copies of library views. These take precedence over vendor copies. Any library view change must be applied to BOTH the library source AND the published copy.
2. **Queue workers**: After code changes, run `php artisan queue:restart` — queue workers hold old code in memory.
3. **Test DB migrations**: The test database (`honestee_test`) may need manual column additions — it doesn't auto-migrate from module paths.
4. **`pay_type` values**: Must be exactly `salaried_full`, `salaried_daily`, or `hourly`. Using `salary` (legacy) will cause calculation failures.

## Notes

- Current phase: Starting Phase 1 (HR + Admin only)
- All payroll/holiday changes from previous sessions are committed and pushed
- The module disable mechanism uses `config/ui-library.php` — no code changes needed for the disable/enable itself
- Disabling a module hides it from UI but doesn't prevent direct URL access to its routes
- To fully prevent access, add middleware or remove routes for disabled modules
