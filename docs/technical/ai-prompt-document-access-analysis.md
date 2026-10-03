# AI Prompt — Document Upload Access Control Analysis

> Paste this into a new AI session (Architect or Code mode) to continue the analysis.

---

## Task

Analyze the document upload and access control system to determine whether employees should be allowed to view documents uploaded by administrators that are related to them, through the ESS (Employee Self-Service) "my-profile" documents tab at `https://hr-consuming-app.test/hr/my-profile?tab=documents`. Identify the current behavior, gaps, and make recommendations. **Do NOT change code yet.**

---

## Context — Codebase Layout

You are working on a Laravel/Livewire HR application at `/Users/mac/Projects/LaravelProjects/hr-consuming-app/` that consumes a domain-agnostic UI library at `/Users/mac/Projects/Libraries/ui-library/`.

### The Document Dual-Model Architecture

There are **two Document models** pointing at the same `documents` table:

| Model | Namespace | Purpose |
|-------|-----------|---------|
| Library Document | [`QuickerFaster\UILibrary\Models\Document`](src/Models/Document.php) | Polymorphic model with `documentable_type`/`documentable_id`. Used by `HasDocuments` trait, `DocumentEngine`, and onboarding uploads. |
| HR Document | [`App\Modules\Hr\Models\Document`](app/Modules/Hr/Models/Document.php:31) | Legacy wrapper with `employee_id` column. Used by the DataTable CRUD at `/hr/documents` and the employee detail documents tab. |

The HR Document model's docblock explicitly states: "This model coexists on the same 'documents' table only to serve the HR DataTable CRUD interface. The Employee model's documents() MorphMany relationship (via HasDocuments trait) always resolves to the library Document."

### Key Source Files

**Document models & traits:**
- [`src/Models/Document.php`](src/Models/Document.php) — Library polymorphic Document (documentable_type, documentable_id, name, file_path, etc.)
- [`src/Traits/Documents/HasDocuments.php`](src/Traits/Documents/HasDocuments.php:10) — morphMany relationship + upload/download helpers
- [`src/Services/Documents/DocumentEngine.php`](src/Services/Documents/DocumentEngine.php:13) — upload, generatePdf, generateExcel, getDocuments, delete
- [`app/Modules/Hr/Models/Document.php`](app/Modules/Hr/Models/Document.php:31) — HR legacy Document model (employee_id, documentable_type, documentable_id)
- [`app/Modules/Hr/Data/document.php`](app/Modules/Hr/Data/document.php) — DataTable config for HR document CRUD

**Employee model (Documentable):**
- [`app/Modules/Hr/Models/Employee.php`](app/Modules/Hr/Models/Employee.php:29) — implements `Documentable`, uses `HasDocuments` trait
- Lines 120–140: `getDocumentableId()`, `getDocumentType()`, `getDocumentStoragePath()`, `getDocumentTemplateData()`

**ESS / Self-Service:**
- [`app/Modules/Hr/Http/Livewire/EmployeeDetail.php`](app/Modules/Hr/Http/Livewire/EmployeeDetail.php:100) — `isSelfServiceMode` when URL is `hr/my-profile`. Controls which tabs are visible and whether edit buttons are hidden.
- [`app/Modules/Hr/Resources/views/livewire/employee-detail.blade.php:732`](app/Modules/Hr/Resources/views/livewire/employee-detail.blade.php:732) — Documents tab renders a DataTable with `configKey='hr.document'`, filtered by `employee_id`.
- [`app/Modules/Hr/Resources/views/hr/my-portal.blade.php`](app/Modules/Hr/Resources/views/hr/my-portal.blade.php:41) — ESS portal landing page

**Authorization & Access Control:**
- [`src/Services/AccessControl/AuthorizationService.php`](src/Services/AccessControl/AuthorizationService.php:33) — `$resolveUserSubjectId` callback (set by consuming app). `authorizeView()` checks record ownership via `recordBelongsToSubject()`.
- [`app/Providers/AppServiceProvider.php:61`](app/Providers/AppServiceProvider.php:61) — registers `$resolveUserSubjectId` callback: maps `User` → `Employee.id`
- [`src/Services/AccessControl/AuthorizationService.php:145`](src/Services/AccessControl/AuthorizationService.php:145) — `authorizeView()`: admin bypass → ownership bypass → approver bypass → `view_{resource}` permission
- [`src/Services/AccessControl/AuthorizationService.php:265`](src/Services/AccessControl/AuthorizationService.php:265) — `recordBelongsToSubject()`: checks if a property on the record matches the subject ID

**Onboarding:**
- [`app/Modules/Hr/Http/Livewire/Onboarding/Steps/Step4Documents.php`](app/Modules/Hr/Http/Livewire/Onboarding/Steps/Step4Documents.php:23) — Onboarding document upload (uses library Document, polymorphic)
- [`app/Modules/Hr/Conditions/DocumentsUploaded.php`](app/Modules/Hr/Conditions/DocumentsUploaded.php:13) — checks `$employee->documents()->count() > 0` (polymorphic)

---

## Context — Key Documentation

**Library philosophy (must be respected):**
- [`docs/library/pilosophy.txt`](docs/library/pilosophy.txt) — Core: library must be decoupled from consuming app; consuming app modules must be self-contained.
- [`docs/library/25-library-independence-safeguards.md`](docs/library/25-library-independence-safeguards.md) — Non-negotiable: no `App\Modules\*` references in library code.
- [`docs/library/27-architecture-boundary.md`](docs/library/27-architecture-boundary.md) — Two-domain test and capability-vs-noun test.

**Consuming app checklists (must be reviewed before any code):**
- [`docs/consuming-app/pre-coding-checklist.md`](docs/consuming-app/pre-coding-checklist.md) — Blade views, Livewire components, library modifications, module file placement, navigation, row actions, boolean fields, workflow notifications, active-state logic. **Must be reviewed before writing any code.**
- [`docs/consuming-app/data-configs.md`](docs/consuming-app/data-configs.md) — Data config schema.
- [`docs/consuming-app/module-structure.md`](docs/consuming-app/module-structure.md) — Module directory conventions.
- [`docs/consuming-app/18-workflow-approval-testing-checklist.md`](docs/consuming-app/18-workflow-approval-testing-checklist.md)
- [`docs/consuming-app/19-notification-consuming-app-guide.md`](docs/consuming-app/19-notification-consuming-app-guide.md)

**Library docs:**
- [`docs/library/01-core-concepts.md`](docs/library/01-core-concepts.md) — 7-layer architecture
- [`docs/library/08-contracts-and-interfaces.md`](docs/library/08-contracts-and-interfaces.md)
- [`docs/library/data-table-record-events.md`](docs/consuming-app/data-table-record-events.md)

**Existing analysis reports (context on recent work):**
- [`docs/technical/clockin-attendance-payroll-analysis.md`](docs/technical/clockin-attendance-payroll-analysis.md) — Previous analysis including library boundary cleanup
- [`docs/technical/payroll-calculation-parameters-analysis.md`](docs/technical/payroll-calculation-parameters-analysis.md)
- [`docs/technical/leave-holiday-payroll-impact-analysis.md`](docs/technical/leave-holiday-payroll-impact-analysis.md)

**User-facing guides:**
- [`docs/user/hr/README.md`](docs/user/hr/README.md)
- [`docs/user/time-to-pay-journey.md`](docs/user/time-to-pay-journey.md)
- [`docs/user/hr-office-daily-operations.md`](docs/user/hr-office-daily-operations.md)

---

## Specific Analysis Required

### 1. How Documents Are Currently Stored

Trace the two paths for document creation:

**Path A — Admin upload via DataTable CRUD** (`/hr/documents`):
- Uses `App\Modules\Hr\Models\Document` with `employee_id` column
- Data config: [`document.php`](app/Modules/Hr/Data/document.php) with `employee_id` as a searchable select
- Document records are created with `employee_id`, `documentable_type`, `documentable_id`
- Identify: what exactly gets stored? Does it populate both `employee_id` AND `documentable_type/documentable_id`? Are they always in sync?

**Path B — Onboarding/Programmatic upload** (`Step4Documents`, `DocumentEngine`):
- Uses `QuickerFaster\UILibrary\Models\Document` with polymorphic `documentable_type`/`documentable_id`
- `documentable_type = 'App\Modules\Hr\Models\Employee'`, `documentable_id = employee->id`
- Identify: does this path ever set `employee_id`? Where does the `employee_id` column get populated?

### 2. How Documents Are Currently Displayed

Trace the documents tab in ESS:

- [`employee-detail.blade.php:734`](app/Modules/Hr/Resources/views/livewire/employee-detail.blade.php:734) renders a DataTable with `configKey='hr.document'`
- Filter: `queryFilters => [['employee_id', '=', $employee->id]]`
- This means only documents with a matching `employee_id` column appear
- **Critical question**: If a document was uploaded via Path B (polymorphic, `documentable_type`/`documentable_id`), does it have `employee_id` populated? If not, the employee won't see it.

### 3. Authorization Gap Analysis

- The `AuthorizationService::authorizeView()` ownership bypass works via `recordBelongsToSubject()` which checks a property on the record.
- For the HR Document model, which property is checked? Is it `employee_id`? Is the DataTable's default authorization provider configured?
- The `DefaultAuthorizationProvider` (library) — how does it determine which records a user can see? Does it apply the ownership check to the DataTable query?
- The `EmployeeDataTableAuthorizationProvider` (consuming app) — does it exist? If so, what does it do?

### 4. The Two-Document-Model Problem

- Documents uploaded via Path A (admin CRUD) use the HR Document model with `employee_id`
- Documents uploaded via Path B (onboarding) use the polymorphic library Document
- Both live in the same `documents` table
- The ESS tab filters by `employee_id` — so Path B documents are invisible to employees
- Is this intentional? Should employees see documents uploaded by admins?

### 5. Recommendations

Based on findings, produce:
- Whether employees SHOULD see admin-uploaded documents (policy decision with rationale)
- If yes: how to bridge the gap between the two document models (options: populate `employee_id` on all documents, change the ESS filter to use polymorphic query, or unify the models)
- If no: how to clearly separate admin-only documents from employee-visible documents, and how to document this distinction
- Any library boundary considerations (does the fix belong in the library or consuming app?)

---

## Output Format

Produce a markdown report at `docs/technical/document-access-control-analysis.md` with:
1. Executive summary
2. Current document storage analysis (both paths traced with code references)
3. Current document display analysis (ESS tab trace)
4. Authorization gap analysis
5. The two-model problem with concrete examples
6. Prioritized recommendations with implementation approach
7. Library boundary impact assessment

Use clickable file references: [`filename`](relative/path.ext:line).

Before any code changes, review the pre-coding checklist at [`docs/consuming-app/pre-coding-checklist.md`](docs/consuming-app/pre-coding-checklist.md).
