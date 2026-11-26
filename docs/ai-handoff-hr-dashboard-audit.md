# AI Handoff Prompt — HR Module Dashboard Audit & UX Analysis

> **Copy this entire file into a new AI session.**

---

## Your Role

You are a senior Laravel + Livewire engineer and a UX/product analyst. Your job is to **audit all 7 HR module dashboards**, identify gaps, and recommend what widgets to swap, remove, add, or fix.

---

## Project Structure

```
Library (UI framework):  /Users/mac/Projects/Libraries/ui-library/
Consuming HR app:       /Users/mac/Projects/LaravelProjects/hr-consuming-app/
HR module:              app/Modules/Hr/
```

The library provides the generic DataTable/Form/Wizard/Workflow infrastructure, widget processors, and dashboard config resolution. The HR module defines dashboard configs that the library renders.

**Critical rule**: The library (`src/`) must NEVER reference `App\Modules\*` (two-domain test). Business logic stays in `app/Modules/{Module}/`.

---

## Dashboards to Audit

Open each of these URLs in a browser while logged in:

| # | URL | Config File |
|---|-----|------------|
| 1 | `https://hr-consuming-app.test/hr/dashboard` | [`app/Modules/Hr/Data/dashboards/dashboard.php`](app/Modules/Hr/Data/dashboards/dashboard.php) |
| 2 | `https://hr-consuming-app.test/hr/onboarding-overview` | *(find the config)* |
| 3 | `https://hr-consuming-app.test/hr/dashboard-people-overview` | *(find the config)* |
| 4 | `https://hr-consuming-app.test/hr/dashboard-manage-overview` | *(find the config)* |
| 5 | `https://hr-consuming-app.test/hr/dashboard-organization-overview` | *(find the config)* |
| 6 | `https://hr-consuming-app.test/hr/dashboard-policies-overview` | *(find the config)* |
| 7 | `https://hr-consuming-app.test/hr/dashboard-loans-overview` | *(find the config)* |

---

## What to Analyze

For each dashboard, evaluate every widget against these criteria:

### 1. Relevance
- Does this widget belong on this dashboard?
- Is it the right audience (HR Manager vs HR Officer vs Admin)?
- Could it be more useful on a different dashboard?

### 2. Data Accuracy
- Does the widget show correct data?
- Are counts/totals accurate?
- Are there empty states handled gracefully?

### 3. UX Quality
- Is the widget type appropriate (stat, chart, list, metric, action card)?
- Is the layout logical?
- Are labels clear?

### 4. Gaps
- What's missing that SHOULD be here?
- What competitor dashboards show that we don't?

---

## Key Files to Read First

### Dashboard Configs (find all in HR module)
```
app/Modules/Hr/Data/dashboards/*.php
```

### Library Dashboard System
| File | What it does |
|------|-------------|
| [`src/Services/Widgets/WidgetProcessor.php`](src/Services/Widgets/WidgetProcessor.php) | Resolves and processes widget configs |
| [`src/Services/Config/Dashboards/DashboardResolver.php`](src/Services/Config/Dashboards/DashboardResolver.php) | Resolves dashboard configs |
| [`src/Services/Config/Dashboards/CompositeDashboardResolver.php`](src/Services/Config/Dashboards/CompositeDashboardResolver.php) | Merges multiple dashboard configs |

### Widget Processors (each renders a widget type)
| Processor | Widget Type |
|-----------|------------|
| [`src/Widgets/StatWidgetProcessor.php`](src/Widgets/StatWidgetProcessor.php) | Single stat/KPI |
| [`src/Widgets/ChartWidgetProcessor.php`](src/Widgets/ChartWidgetProcessor.php) | Charts (bar, line, pie) |
| [`src/Widgets/ListWidgetProcessor.php`](src/Widgets/ListWidgetProcessor.php) | Data lists |
| [`src/Widgets/GroupedListWidgetProcessor.php`](src/Widgets/GroupedListWidgetProcessor.php) | Grouped data lists |
| [`src/Widgets/MetricWidgetProcessor.php`](src/Widgets/MetricWidgetProcessor.php) | Metric with trend |
| [`src/Widgets/TrendWidgetProcessor.php`](src/Widgets/TrendWidgetProcessor.php) | Trend indicator |
| [`src/Widgets/ActionCardWidgetProcessor.php`](src/Widgets/ActionCardWidgetProcessor.php) | Action button card |
| [`src/Widgets/ProgressWidgetProcessor.php`](src/Widgets/ProgressWidgetProcessor.php) | Progress bar |
| [`src/Widgets/ProfileHeaderWidgetProcessor.php`](src/Widgets/ProfileHeaderWidgetProcessor.php) | Profile summary |
| [`src/Widgets/OnboardingWidgetProcessor.php`](src/Widgets/OnboardingWidgetProcessor.php) | Onboarding checklist |
| [`src/Widgets/AnalyticsWidgetProcessor.php`](src/Widgets/AnalyticsWidgetProcessor.php) | Analytics display |
| [`src/Widgets/ActivityLogWidgetProcessor.php`](src/Widgets/ActivityLogWidgetProcessor.php) | Activity feed |
| [`src/Widgets/QuickActionsWidgetProcessor.php`](src/Widgets/QuickActionsWidgetProcessor.php) | Quick action buttons |

### Library Docs (Widget System)
| Doc | Content |
|-----|---------|
| [`docs/library/07-component-catalog.md`](docs/library/07-component-catalog.md) | Widget types and their config options |
| [`docs/library/09-engines-and-services.md`](docs/library/09-engines-and-services.md) | Dashboard engine, widget processing |
| [`docs/library/01-core-concepts.md`](docs/library/01-core-concepts.md) | Config-driven architecture |

### Consuming App Docs
| Doc | Content |
|-----|---------|
| [`docs/user/hr/README.md`](docs/user/hr/README.md) | HR module user guide |
| [`docs/user/payroll/README.md`](docs/user/payroll/README.md) | Payroll user guide (recently updated with Nigerian compliance) |
| [`docs/ai-handoff-payroll-compliance-session-summary.md`](docs/ai-handoff-payroll-compliance-session-summary.md) | What was just implemented |

---

## Library Philosophy (from project docs)

1. **Config-driven**: Everything is defined in PHP config files, not hardcoded in Blade
2. **Domain-agnostic**: The library knows nothing about HR, Payroll, Leave — only generic widgets, tables, forms
3. **Module isolation**: Each module (`Hr`, `Payroll`, `Leave`, etc.) owns its own dashboards, data configs, routes
4. **Composite dashboards**: Multiple config files can merge into one dashboard view
5. **Widget types**: Each widget type has a processor class that resolves config → rendered output

---

## Pre-Coding Checklist

- [ ] No library `src/` files reference `App\Modules\*` (two-domain test)
- [ ] Dashboard configs live in `app/Modules/Hr/Data/dashboards/`
- [ ] Widget configs follow the library's widget schema (check [`WidgetProcessor.php`](src/Services/Widgets/WidgetProcessor.php))
- [ ] Data queries are scoped to the current company (`withoutCompanyScope()` or `HasCompanyScope`)
- [ ] All labels/text use translation keys or are human-readable

---

## Post-Coding Checklist

- [ ] `php -l` on every changed PHP file
- [ ] `php artisan optimize:clear`
- [ ] Refresh each dashboard in the browser — no blank pages, no 500s
- [ ] No `App\Modules\*` references leak into the library `src/`
- [ ] Run `php artisan test` — all existing tests still pass

---

## Git Branching Strategy

```
feature/*  →  staging  →  main (production)
```

| Branch | Purpose | Deploys to |
|--------|---------|-----------|
| `main` | Production — tested code only | Live server |
| `staging` | Pre-production testing | Staging server |
| `feature/hr-dashboard-audit` | This task | Local only |

**Workflow:**
```bash
git checkout main && git pull
git checkout -b feature/hr-dashboard-audit
# ... work, commit, push ...
git checkout staging && git merge feature/hr-dashboard-audit && git push  # test
git checkout main && git merge staging && git push  # deploy
```

---

## Deliverables

1. **Audit report** — for each of the 7 dashboards, a table showing:
   - Widget name | Current status | Recommendation (keep/remove/swap/fix) | Reason
2. **Gap analysis** — what's missing across all dashboards
3. **Implementation** — apply the agreed changes (config edits, new widgets, removed widgets)
4. **Documentation** — update [`docs/user/hr/README.md`](docs/user/hr/README.md) if dashboard changes affect user workflows

---

## Database

- Main: `mysql://root:Honestee@127.0.0.1:3306/honestee`
- Test: `mysql://root:Honestee@127.0.0.1:3306/honestee_test`

⚠️ The test DB (`honestee_test`) is out of sync with the latest migrations. Do not rely on DB-backed integration tests.

---

## Current Git State

- Consuming app: latest commit `626d609` (already pushed) — Nigerian payroll compliance complete
- Library: analytics + preview fixes already committed/pushed
