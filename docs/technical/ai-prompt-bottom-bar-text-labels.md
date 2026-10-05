# AI Prompt — Mobile Bottom Bar: Add Text Labels Below Icons

> Paste this into a new AI session (Architect or Code mode) to implement text labels below icons on the mobile bottom navigation bar, similar to the YouTube mobile app.

---

## Task

Add text labels below each icon on the mobile bottom navigation bar (`BottomBar` component). Currently the bar shows **icons only** — users have requested text labels below each icon (like YouTube's mobile app) for better usability and clarity.

---

## Context — Codebase Layout

You are working on a Laravel/Livewire HR application at `/Users/mac/Projects/LaravelProjects/hr-consuming-app/` that consumes a domain-agnostic UI library at `/Users/mac/Projects/Libraries/ui-library/`.

### Key Source Files

**Library — BottomBar component:**
- [`src/Http/Livewire/Layouts/Navs/BottomBar.php`](src/Http/Livewire/Layouts/Navs/BottomBar.php:1) — The Livewire component. Alpine-free by design — uses native `onclick` handlers and `window.Livewire.dispatch()`. No `x-data`, `x-show`, or `@click` directives.
- [`src/Resources/views/livewire/navs/bottom-bar.blade.php`](src/Resources/views/livewire/navs/bottom-bar.blade.php:1) — The blade view. **Currently icons only** — no text labels below icons. The tab bar section (lines ~30-55) renders each visible group as an `<a>` tag with just an icon.
- [`src/Resources/views/components/layouts/navigation-layout.blade.php:200`](src/Resources/views/components/layouts/navigation-layout.blade.php:200) — Renders `<livewire:qf.bottom-bar>` with `contextGroups`, `activeContext`, `moduleName`.

**Library — Bottom bar menu configs (examples):**
- [`src/Core/Admin/Config/bottom_bar_menu.php`](src/Core/Admin/Config/bottom_bar_menu.php) — Admin module bottom bar config. Each item has `title`, `icon`, `url`, `permission`, `key`.
- [`src/Core/System/Config/bottom_bar_menu.php`](src/Core/System/Config/bottom_bar_menu.php) — System module bottom bar config.

**Consuming app — Existing bottom-bar-links (already have text labels!):**
- [`app/Modules/Hr/Resources/views/components/layouts/navbars/auth/people/bottom-bar-links.blade.php`](app/Modules/Hr/Resources/views/components/layouts/navbars/auth/people/bottom-bar-links.blade.php) — Example: `<i class="fas fa-user d-block mb-1"></i><small>People Overviews</small>`
- [`app/Modules/Hr/Resources/views/components/layouts/navbars/auth/leave/bottom-bar-links.blade.php`](app/Modules/Hr/Resources/views/components/layouts/navbars/auth/leave/bottom-bar-links.blade.php)
- [`app/Modules/Hr/Resources/views/components/layouts/navbars/auth/time/bottom-bar-links.blade.php`](app/Modules/Hr/Resources/views/components/layouts/navbars/auth/time/bottom-bar-links.blade.php)
- [`app/Modules/Hr/Resources/views/components/layouts/navbars/auth/policies/bottom-bar-links.blade.php`](app/Modules/Hr/Resources/views/components/layouts/navbars/auth/policies/bottom-bar-links.blade.php)
- [`app/Modules/Hr/Resources/views/components/layouts/navbars/auth/company profile/bottom-bar-links.blade.php`](app/Modules/Hr/Resources/views/components/layouts/navbars/auth/company profile/bottom-bar-links.blade.php)
- [`app/Modules/Payroll/Resources/views/components/layouts/navbars/auth/payroll/bottom-bar-links.blade.php`](app/Modules/Payroll/Resources/views/components/layouts/navbars/auth/payroll/bottom-bar-links.blade.php)

**Library docs (must be reviewed):**
- [`docs/library/pilosophy.txt`](docs/library/pilosophy.txt) — Core: library must be decoupled from consuming app; consuming app modules must be self-contained.
- [`docs/library/25-library-independence-safeguards.md`](docs/library/25-library-independence-safeguards.md) — Non-negotiable: no `App\Modules\*` references in library code.
- [`docs/library/27-architecture-boundary.md`](docs/library/27-architecture-boundary.md) — Two-domain test and capability-vs-noun test.
- [`docs/consuming-app/pre-coding-checklist.md`](docs/consuming-app/pre-coding-checklist.md) — **Must be reviewed before writing any code.** Covers: Blade views, Livewire components, library modifications, module file placement, navigation, row actions, boolean fields, workflow notifications, active-state logic.
- [`docs/debug-checklist.md`](docs/debug-checklist.md) — §11 covers BottomBar-specific issues: `request()->url()` vs `url()->previous()`, `wire:navigate` + Bootstrap dropdown incompatibility.

**Library docs — navigation:**
- [`docs/library/06-navigation-system.md`](docs/library/06-navigation-system.md) — Navigation system overview
- [`docs/library/04-routing-and-views.md`](docs/library/04-routing-and-views.md) — Routing and views
- [`docs/library/sidebar-active-state-pitfalls.md`](docs/library/sidebar-active-state-pitfalls.md) — Active state pitfalls

**Existing analysis reports (context on recent work):**
- [`docs/technical/document-access-control-analysis.md`](docs/technical/document-access-control-analysis.md) — Recent document access control implementation
- [`docs/technical/clockin-attendance-payroll-analysis.md`](docs/technical/clockin-attendance-payroll-analysis.md)
- [`docs/technical/payroll-calculation-parameters-analysis.md`](docs/technical/payroll-calculation-parameters-analysis.md)

---

## Current Behavior

The `bottom-bar.blade.php` renders each tab as:

```blade
<a href="{{ $url }}"
   class="btn btn-sm d-flex align-items-center justify-content-center flex-shrink-0 border-0
          {{ $isActive ? 'text-primary' : 'text-muted' }}"
   style="width: 56px; height: 44px;"
   wire:key="bb-tab-{{ $key }}"
   title="{{ $group['label'] ?? $key }}">
    @if (!empty($group['icon']))
        <i class="{{ $group['icon'] }} fs-5 {{ $isActive ? 'opacity-100' : 'opacity-50' }}"></i>
    @else
        <span class="fw-bold {{ $isActive ? 'opacity-100' : 'opacity-50' }}" style="font-size: 0.7rem;">
            {{ \Illuminate\Support\Str::limit($group['label'] ?? $key, 3, '') }}
        </span>
    @endif
</a>
```

Each tab is 56px wide × 44px tall, icon-only, with a `title` attribute for the tooltip. The "More" overflow button already has a text label (`<span>More</span>`) below its icon.

## Desired Behavior

Each tab should show:
1. **Icon** (top) — same as current
2. **Text label** (bottom) — the group's `label` or `title`, truncated to fit, like YouTube's mobile app

The tab should be taller to accommodate both icon and text. The "More" overflow button already has this pattern — the fix should make the regular tabs consistent with it.

---

## Specific Analysis Required

### 1. Current BottomBar Architecture

Trace how the BottomBar receives its data:
- `navigation-layout.blade.php` passes `:contextGroups="$contextGroups"` and `:activeContext="$activeContext"`
- `$contextGroups` comes from the navigation config (e.g., `Config/navigation.php` in each module)
- Each context group has: `label`, `icon`, `route`/`url`, `items[]`
- The BottomBar's `visibleGroups` property returns the first `$maxVisible` groups
- The `overflowGroups` property returns the remaining groups

### 2. The Two Bottom-Bar Systems

There are **two separate bottom bar implementations** in the consuming app:

| System | Location | Has Text Labels? |
|--------|----------|-----------------|
| Library `BottomBar` component | `src/Http/Livewire/Layouts/Navs/BottomBar.php` + `bottom-bar.blade.php` | ❌ Icons only |
| Consuming app `bottom-bar-links.blade.php` | `app/Modules/*/Resources/views/.../bottom-bar-links.blade.php` | ✅ Icons + text labels |

The consuming app's `bottom-bar-links` files already have the desired pattern:
```blade
<button role="link" href="hr/employees" class="btn btn-light flex-shrink-0 text-center" style="min-width:70px;" wire:navigate>
    <i class="fas fa-user-tie d-block mb-1"></i>
    <small>Employees</small>
</button>
```

**Question:** Are these `bottom-bar-links` files still in use, or have they been superseded by the library's `BottomBar` component? If they're still used, where are they rendered?

### 3. Implementation Approach

The fix should be in the **library's** `bottom-bar.blade.php` since the `BottomBar` component is the canonical mobile navigation. The consuming app's `bottom-bar-links` files may be legacy.

**Changes needed in `bottom-bar.blade.php`:**
1. Add text label below each icon using `$group['label']` (or `$group['title']` from the config)
2. Adjust tab dimensions: wider (to fit text) and taller (to fit icon + text)
3. Truncate long labels to prevent overflow
4. Ensure the "More" overflow button remains consistent
5. Test that active state highlighting still works

**Design considerations:**
- YouTube uses ~5 tabs with short labels (Home, Shorts, Subscriptions, You, Library)
- The HR app may have longer labels (e.g., "Employee Groups", "People Overviews")
- Labels should be truncated with `Str::limit()` or CSS `text-overflow: ellipsis`
- Tab width should be flexible (`flex-grow-1` or percentage-based) rather than fixed 56px
- The `maxVisible` property (default 4) controls how many tabs show before overflow

### 4. Library Boundary Check

Per the pre-coding checklist §C:
- ✅ This change is in library code (`src/Resources/views/livewire/navs/bottom-bar.blade.php`)
- ✅ No `App\Modules\*` references needed — the label comes from `$group['label']` which is already passed in
- ✅ Passes the two-domain test: any app consuming the library would benefit from text labels on bottom nav icons
- ✅ Backward compatible: existing configs already have `label`/`title` fields

### 5. Active State & wire:navigate Considerations

From the debug checklist and CHANGELOG:
- `BottomBar::isItemActive()` uses `url()->previous()` (not `request()->url()`) — this is correct and should not be changed
- `wire:navigate` was **removed** from BottomBar links because it caused Bootstrap dropdown incompatibility — do NOT re-add it
- The "More" overflow button already has text — the fix should make regular tabs consistent

---

## Output Format

1. Analyze the two bottom-bar systems and determine if `bottom-bar-links.blade.php` files are still in use
2. Modify `src/Resources/views/livewire/navs/bottom-bar.blade.php` to add text labels below icons
3. Adjust tab sizing for icon + text layout
4. Test that active state, overflow, and context sheet still work
5. Update any relevant documentation

**Before any code changes, review the pre-coding checklist at [`docs/consuming-app/pre-coding-checklist.md`](docs/consuming-app/pre-coding-checklist.md).**
