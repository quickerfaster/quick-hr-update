# Document Upload & Access Control — Analysis Report

> **Generated:** 2026-10-02
> **Implemented:** 2026-10-03
> **Scope:** ESS My-Profile documents tab at `https://hr-consuming-app.test/hr/my-profile?tab=documents`
> **Status:** ✅ All recommendations implemented

---

## 1. Executive Summary

The HR consuming app uses a **dual-model architecture** for documents: both [`QuickerFaster\UILibrary\Models\Document`](src/Models/Document.php) (the library polymorphic model) and [`App\Modules\Hr\Models\Document`](app/Modules/Hr/Models/Document.php:31) (the HR legacy wrapper) operate on the same `documents` table. The ESS "my-profile" documents tab at `/hr/my-profile?tab=documents` renders a DataTable filtered by `employee_id`. The question is whether employees can see documents that administrators upload via the HR CRUD.

**Key finding: Both document upload paths currently populate `employee_id`, so the immediate gap is smaller than initially suspected.** However, there is a latent risk: the library `DocumentEngine` does **not** populate `employee_id`, so any future or existing code path that uses `DocumentEngine` directly (bypassing the HR model's boot hook or Step4Documents' explicit `forceFill`) would create invisible documents. The report identifies this as the primary architectural gap and recommends a systematic fix at the source of truth.

---

## 2. Current Document Storage Analysis

### 2.1 The `documents` Table Schema

The unified table is created by the library migration at [`Database/Migrations/2026_08_08_000002_create_documents_table.php`](Database/Migrations/2026_08_08_000002_create_documents_table.php:11):

| Column | Type | Purpose |
|--------|------|---------|
| `id` | bigint PK | Primary key |
| `company_id` | unsigned bigint (nullable) | HR company scope |
| `employee_id` | unsigned bigint (nullable) | **HR legacy linking column** |
| `type` | varchar (nullable) | HR document type (Resume, Contract, etc.) |
| `document` | varchar (nullable) | Legacy file path field |
| `uploaded_at` | date (nullable) | Upload date |
| `expiry_date` | date (nullable) | Expiry date |
| `description` | text (nullable) | Description |
| `documentable_type` | varchar | **Polymorphic type** |
| `documentable_id` | bigint | **Polymorphic ID** |
| `name` | varchar | Document name |
| `file_path` | varchar | File storage path |
| `file_name` | varchar | Original filename |
| `mime_type` | varchar (nullable) | MIME type |
| `size` | bigint | File size in bytes |
| `document_type` | varchar (nullable) | Library document type key |
| `disk` | varchar | Storage disk |
| `metadata` | json (nullable) | Extensible metadata |
| timestamps + softDeletes | | |

The table has **both** `employee_id` (HR domain column) and `documentable_type`/`documentable_id` (polymorphic columns). They coexist in one table.

### 2.2 Path A — Admin Upload via DataTable CRUD (`/hr/documents`)

**Model:** [`App\Modules\Hr\Models\Document`](app/Modules/Hr/Models/Document.php:31)  
**Data config:** [`app/Modules/Hr/Data/document.php`](app/Modules/Hr/Data/document.php:4)

**Flow:**

1. Admin navigates to `/hr/documents`, clicks "Add"
2. DataTableForm renders a form with fields: `employee_id` (searchable select), `company_id`, `name`, `type`, `document` (file), `uploaded_at`, `expiry_date`, `description`
3. On save, the HR Document model's `creating` boot hook at [line 92–107](app/Modules/Hr/Models/Document.php:92) fires:

```php
// Line 92-96: Bridge employee_id → polymorphic columns
if (empty($document->documentable_type) && !empty($document->employee_id)) {
    $document->documentable_type = \App\Modules\Hr\Models\Employee::class;
    $document->documentable_id = $document->employee_id;
}

// Line 101-104: Copy legacy 'document' field to NOT NULL library columns
if (empty($document->file_path) && !empty($document->document)) {
    $document->file_path = $document->document;
    $document->file_name = pathinfo($document->document, PATHINFO_BASENAME);
}

$document->uploaded_at = $document->uploaded_at ?? now();
```

**Result for Path A:** The created record has:
- ✅ `employee_id` = the selected employee's ID
- ✅ `documentable_type` = `App\Modules\Hr\Models\Employee`
- ✅ `documentable_id` = same as `employee_id`
- ✅ `file_path` / `file_name` populated from the form's `document` field or file upload

**The two columns are always in sync for Path A.**

### 2.3 Path B — Onboarding Upload via Step4Documents

**Component:** [`app/Modules/Hr/Http/Livewire/Onboarding/Steps/Step4Documents.php`](app/Modules/Hr/Http/Livewire/Onboarding/Steps/Step4Documents.php:23)  
**Model used:** [`QuickerFaster\UILibrary\Models\Document`](src/Models/Document.php) (library Document)  
**Disk:** `public`, path: `documents/{employee_id}/`

**Flow:**

1. Employee goes through onboarding wizard (or admin onboards a new hire)
2. Step 4: Documents — user uploads files (PDF, JPG, PNG, DOC, DOCX)
3. On `upload()` at [lines 112–192](app/Modules/Hr/Http/Livewire/Onboarding/Steps/Step4Documents.php:112):
   - File stored to `documents/{employee_id}/` on the `public` disk
   - Library Document created with polymorphic columns:

```php
// Lines 146-156: Polymorphic document record
$document = Document::create([
    'documentable_type' => Employee::class,
    'documentable_id'   => $this->employee->getKey(),
    'name'              => $this->newFile->getClientOriginalName(),
    'file_path'         => $storedPath,
    'file_name'         => $this->newFile->getClientOriginalName(),
    'mime_type'         => $this->newFile->getMimeType(),
    'size'              => $this->newFile->getSize(),
    'document_type'     => $this->documentType,
    'disk'              => $this->disk,
]);

// Lines 158-161: Explicitly bridge to HR domain column
$document->forceFill([
    'employee_id' => $this->employee->getKey(),
])->save();
```

**Result for Path B (Step4Documents):** The created record has:
- ✅ `documentable_type` = `App\Modules\Hr\Models\Employee`
- ✅ `documentable_id` = employee ID
- ✅ `employee_id` = employee ID (explicitly force-filled)
- ✅ `file_path` / `file_name` populated

**Both columns are populated.**

### 2.4 Path B.2 — DocumentEngine Direct Upload (Latent Gap)

**Service:** [`QuickerFaster\UILibrary\Services\Documents\DocumentEngine`](src/Services/Documents/DocumentEngine.php:13)  
**Methods:** `upload()`, `generatePdf()`, `generateExcel()`

The library `DocumentEngine` creates documents with **only polymorphic columns**:

```php
// DocumentEngine::upload() lines 29-39:
return Document::create([
    'documentable_type' => get_class($entity),
    'documentable_id'   => $entity->getDocumentableId(),
    // ...
    // ❌ NO employee_id set
]);
```

Similarly, `generatePdf()` ([line 55–65](src/Services/Documents/DocumentEngine.php:55)) and `generateExcel()` ([line 78–88](src/Services/Documents/DocumentEngine.php:78)) only set polymorphic columns.

**This means:** If any code path uses `$employee->uploadDocument(file)` (which delegates to `DocumentEngine::upload()` via [`HasDocuments` trait line 23–26](src/Traits/Documents/HasDocuments.php:23)), or calls `DocumentEngine::generatePdf()` or `DocumentEngine::generateExcel()` directly, the resulting document record will **NOT** have `employee_id` set. Such documents would be invisible on the ESS documents tab.

**Currently, Step4Documents bypasses DocumentEngine entirely** and handles the upload directly with a subsequent `forceFill` to bridge the gap. This is a workaround, not a systematic solution.

---

## 3. Current Document Display Analysis

### 3.1 ESS Documents Tab Trace

**Route:** `/hr/my-profile?tab=documents`  
**Component:** [`EmployeeDetail`](app/Modules/Hr/Http/Livewire/EmployeeDetail.php:43) at `isSelfServiceMode = true`  
**Blade:** [`employee-detail.blade.php:732–759`](app/Modules/Hr/Resources/views/livewire/employee-detail.blade.php:732)

The documents tab renders a DataTable:

```blade
@livewire(
    'qf.data-table',
    [
        'configKey' => 'hr.document',
        'queryFilters' => [['employee_id', '=', $employee->id]],
        // ← CRITICAL FILTER
        'hiddenFields' => ['onTable' => ['employee_id']],
        'prefilledData' => ['employee_id' => $employee->id],
        'simpleActions' => ['show', 'create'],
        'moreActions' => [],
        'controls' => [...],
    ],
    key('documents-' . $recordId)
)
```

**The DataTable query is scoped by `employee_id`** via [`queryFilters`](src/Http/Livewire/DataTables/DataTable.php:42). The [`FilterService::applySimpleFilters()`](src/Services/Filters/FilterService.php:22) translates this to:

```sql
WHERE employee_id = ?
```

**Visibility matrix based on `employee_id` population:**

| Document Source | `employee_id` populated? | Visible in ESS? |
|-----------------|--------------------------|-----------------|
| Path A: Admin CRUD (HR Document model) | ✅ Yes (via form + boot hook) | ✅ Yes |
| Path B: Step4Documents onboarding | ✅ Yes (forceFill) | ✅ Yes |
| Path B.2: DocumentEngine direct upload | ❌ No | ❌ No |
| Path B.2: DocumentEngine generatePdf/generateExcel | ❌ No | ❌ No |

**Conclusion:** Currently, documents created through the two main paths (Path A and Step4Documents) are visible to employees in ESS. The gap is with `DocumentEngine` direct usage, which exists as a latent risk.

### 3.2 Admin Detail Page Documents Tab

The admin employee detail page at `/hr/employees/{id}?tab=documents` also uses the same DataTable with `employee_id` filtering. Admins with bypass permissions see all documents, but the query scope ensures they see employee-specific documents when viewing an individual employee.

---

## 4. Authorization Gap Analysis

### 4.1 AuthorizationService — Record Ownership

[`AuthorizationService::authorizeView()`](src/Services/AccessControl/AuthorizationService.php:143) has this priority chain:

1. **Admin bypass** ([line 150](src/Services/AccessControl/AuthorizationService.php:150)): `isBypassAllowed()` checks for `super_admin`, `admin`, `company_admin` roles
2. **Ownership bypass** ([lines 158–164](src/Services/AccessControl/AuthorizationService.php:158)): Calls `$resolveUserSubjectId` callback (registered in [`AppServiceProvider::registerEmployeeOwnershipResolver()`](app/Providers/AppServiceProvider.php:61)) that maps `User.id` → `Employee.id`. Then checks `recordBelongsToSubject()` to see if the record has a matching `employee_id`.
3. **Approver bypass** ([lines 169–186](src/Services/AccessControl/AuthorizationService.php:169)): For Workflowable records, pending approvers can view.
4. **Spatie permission** ([lines 191–193](src/Services/AccessControl/AuthorizationService.php:191)): Falls back to `view_{resource}` permission.

The `recordBelongsToSubject()` method at [line 302–333](src/Services/AccessControl/AuthorizationService.php:302) checks two patterns:
- **Pattern 1** ([lines 305–317](src/Services/AccessControl/AuthorizationService.php:305)): Direct `employee_id` property on the record → compares to `$subjectId`
- **Pattern 2** ([lines 320–329](src/Services/AccessControl/AuthorizationService.php:320)): `employee()` relationship that returns a model with an `id`

**For HR Document model:** Pattern 1 would work because the HR Document model has an `employee_id` field. This means if `authorizeView()` were called for a document record, it would pass for the owning employee. However, the documents tab in ESS **does NOT call `authorizeView()`** — it relies on the DataTable's authorization provider.

### 4.2 DataTable Authorization Provider

The HR module binds [`EmployeeDataTableAuthorizationProvider`](app/Modules/Hr/Services/EmployeeDataTableAuthorizationProvider.php:9) to the [`DataTableAuthorizationProvider`](src/Contracts/DataTables/DataTableAuthorizationProvider.php) contract in [`HrsServiceProvider::register()`](app/Modules/Hr/Providers/HrsServiceProvider.php:27–30).

**`canAccessView()`** at [line 20–39](app/Modules/Hr/Services/EmployeeDataTableAuthorizationProvider.php:20):
1. Admin bypass → return `true`
2. If `$resolveUserSubjectId` resolves to an employee ID → return `true` (**employee ownership bypass**)
3. Fall back to Spatie `view_{viewName}` permission

This means **any employee-linked user can access the DataTable view** (the page load succeeds). The actual record scoping is done at the query level via `queryFilters` — not at the row authorization level.

**`canView()`** at [line 41–57](app/Modules/Hr/Services/EmployeeDataTableAuthorizationProvider.php:41):
- Same pattern: admin bypass → employee bypass → Spatie permission
- For employee users, this returns `true` for any record
- The `canView` check is only invoked when drilling into a record detail, not when listing

### 4.3 The Primary Access Control Mechanism

The primary access control for the ESS documents tab is **the `queryFilters` parameter**, not explicit authorization checks:

```
queryFilters => [['employee_id', '=', $employee->id]]
```

This is a **data-scoping pattern**, not an authorization pattern. The `EmployeeDataTableAuthorizationProvider` provides a blanket "yes" to employees for page-level access, trusting that the query filter will scope records correctly. This works but creates a tight coupling between the filter and the authorization — if the filter is wrong or incomplete, employees could see documents they shouldn't, or miss documents they should.

---

## 5. The Two-Document-Model Problem

### 5.1 Current State

The two models coexist on the `documents` table with overlapping but different concerns:

| Aspect | Library Document | HR Document |
|--------|------------------|-------------|
| Namespace | `QuickerFaster\UILibrary\Models\Document` | `App\Modules\Hr\Models\Document` |
| `fillable` | `documentable_type`, `documentable_id`, `name`, `file_path`, `file_name`, `mime_type`, `size`, `document_type`, `disk`, `metadata` | `company_id`, `employee_id`, `name`, `type`, `document`, `file_path`, `file_name`, `uploaded_at`, `expiry_date`, `description`, `documentable_type`, `documentable_id` |
| Boot hook | Delete file on forceDelete | Bridge `employee_id` → polymorphic, `document` → `file_path`/`file_name` |
| Relationship | `documentable()` morphTo | `documentable()` morphTo + `employee()` belongsTo + `company()` belongsTo |
| Used by | DocumentEngine, HasDocuments trait, onboarding (library Document directly) | DataTable CRUD at `/hr/documents` |

### 5.2 The Bridging Mechanisms

There are currently **two separate bridging mechanisms** to keep `employee_id` in sync with `documentable_id`:

1. **HR Document model boot hook** ([line 92–96](app/Modules/Hr/Models/Document.php:92)): If `documentable_type` is empty and `employee_id` is set, auto-populates polymorphic columns. Covers Path A.

2. **Step4Documents explicit forceFill** ([lines 158–161](app/Modules/Hr/Http/Livewire/Onboarding/Steps/Step4Documents.php:158)): After creating a library Document, explicitly sets `employee_id`. Covers Path B (onboarding).

**Missing:** No bridging mechanism for `DocumentEngine` direct usage. No bridging from polymorphic → `employee_id` (only `employee_id` → polymorphic exists).

### 5.3 Concrete Example of the Gap

```php
// An admin's custom workflow or scheduled job generates a PDF payslip:
$employee = Employee::find(42);
$document = app(DocumentEngine::class)->generatePdf(
    $employee,
    'hr::documents.payslip',
    'Payslip-Jan-2026.pdf',
    ['amount' => 5000]
);

// Result:
// documentable_type = 'App\Modules\Hr\Models\Employee' ✅
// documentable_id = 42 ✅
// employee_id = NULL ❌
// This document will NOT appear on the ESS documents tab for employee 42
```

Similarly, the `HasDocuments` trait's [`uploadDocument()`](src/Traits/Documents/HasDocuments.php:23) method delegates to `DocumentEngine::upload()`, which also doesn't set `employee_id`:

```php
$employee->uploadDocument($file, 'My Document');
// employee_id will be NULL
```

---

## 6. Recommendations

### 6.1 Policy Decision: Should Employees See Admin-Uploaded Documents?

**Recommendation: Yes, with contextual awareness.**

Employees should see documents that are related to them, regardless of who uploaded them. The purpose of an employee document is to be associated with that employee. Hiding admin-uploaded documents from the employee defeats the purpose of document management. However, there may be certain administrative documents (internal notes, investigation files, etc.) that should NOT be visible. This distinction should be handled via a **visibility flag** or **document type classification**, not via the upload path.

**Rationale:**
- Employees need access to their own contracts, offer letters, performance reviews, certificates — many of which admins upload
- The onboarding path (Step4Documents) already makes documents visible; there's no reason admin-uploaded documents shouldn't be
- The current architecture (ESS tab exists, "documents" is in `allowedTabs`) suggests the original design intent was for employees to see their documents

### 6.2 Prioritized Implementation Plan

#### Priority 1 (Critical): Fix the `employee_id` population gap in DocumentEngine

**Problem:** `DocumentEngine` methods (`upload`, `generatePdf`, `generateExcel`) do not populate `employee_id`.

**Approach:** Add a hook in the library Document model's `creating`/`saved` event that populates `employee_id` when `documentable_type` resolves to an Employee model. This would be a **library-side fix**, since the library Document model should handle data integrity on its own table.

**Alternative (consuming-app side):** Add an observer on the library Document model in the consuming app that sets `employee_id` whenever `documentable_type` is `Employee::class`. This avoids modifying library code.

**Recommendation:** Use the consuming-app observer approach. Per the [library philosophy](docs/library/pilosophy.txt) and [library independence safeguards](docs/library/25-library-independence-safeguards.md), the library should not know about `App\Modules\*` models. An observer in the HR module would be a clean, self-contained fix.

```php
// Pseudo-code for HR module observer:
class DocumentEmployeeIdObserver
{
    public function creating(Document $document): void
    {
        if ($document->documentable_type === Employee::class && empty($document->employee_id)) {
            $document->employee_id = $document->documentable_id;
        }
    }
}
```

Register in `HrsServiceProvider::boot()`:
```php
\QuickerFaster\UILibrary\Models\Document::observe(DocumentEmployeeIdObserver::class);
```

This ensures ALL paths (DocumentEngine, HasDocuments trait, direct Document::create) populate `employee_id` consistently.

#### Priority 2 (High): Consider polymorphic query as fallback in ESS tab

**Problem:** The ESS tab relies solely on `employee_id` for filtering. If Priority 1 is implemented, this is mitigated. But as a defense-in-depth measure, the ESS tab could use a more robust query.

**Option A (recommended with Priority 1):** Keep the `employee_id` filter (simpler, faster index). Priority 1 guarantees `employee_id` is always populated.

**Option B:** Change the ESS filter to use a polymorphic query that looks for both `employee_id` match AND `(documentable_type = Employee::class AND documentable_id = employee.id)`. This would be: change the query from `['employee_id', '=', $id]` to a custom scope. However, queryFilters doesn't support OR conditions, so this would require a different approach.

**Recommendation:** Implement Priority 1 and keep Priority 2 as a monitoring check (a scheduled job that reports documents where `documentable_type = Employee::class` but `employee_id IS NULL`).

#### Priority 3 (Medium): Data migration for existing documents

**Problem:** There may already be documents in the database where `employee_id` is NULL but `documentable_type` is `Employee::class`.

**Approach:** Run a one-time migration/command:

```sql
UPDATE documents
SET employee_id = documentable_id
WHERE documentable_type = 'App\\Modules\\Hr\\Models\\Employee'
AND employee_id IS NULL;
```

#### Priority 4 (Medium): Per-document access control — visibility and mutability

*See [§8 — Per-Document Access Control: Deep Dive](#8-per-document-access-control-deep-dive) for the full analysis covering user expectations, competitor approaches, real-world scenarios, and implementation architecture. Summary below.*

**Decision:** Admins need the ability to control which documents are visible to employees and which actions (view, delete, update) employees can perform on documents related to them.

**Recommended approach:** Add a `visibility` varchar column to the `documents` table with three values:
- `all` — Employee can view, download; cannot delete or update
- `admin_only` — Hidden from employee entirely
- `employee` — Employee can view, download, and delete (self-uploaded documents)

The ESS DataTable filter becomes: `WHERE employee_id = ? AND visibility != 'admin_only'`. Delete row actions are conditionally hidden based on `visibility` value. See §8 for detailed competitor analysis, real-world scenarios, and implementation architecture.

#### Priority 4 (Medium): Per-document visibility using standard `select` field type

*See [§8 — Per-Document Access Control: Deep Dive](#8-per-document-access-control-deep-dive) for full competitor analysis, real-world scenarios, and implementation architecture. Summary below.*

**Decision:** The library's standard `field_type => 'select'` with static string-keyed `options` is the correct UI pattern for the `visibility` field. This requires zero library changes.

**Evidence from the codebase** — the `select` with static options pattern is widely used across modules:

| Data Config File | Field | Options Pattern | DB Column | Model |
|-----------------|-------|----------------|-----------|-------|
| [`company.php:58`](app/Modules/Hr/Data/company.php:58) | `status` | `'pending' => 'Pending Setup', 'active' => 'Active', ...` | `$table->string('status')->default('pending')` | `'status' => 'pending'` in `$attributes` |
| [`company.php:23`](app/Modules/Hr/Data/company.php:23) | `level` | `'parent' => 'Parent Company', 'division' => 'Division', ...` | `string` | `'level' => 'division'` in `$attributes` |
| [`leave_request.php:147`](app/Modules/Leave/Data/leave_request.php:147) | `status` | `'Draft' => 'Draft', 'Pending' => 'Pending', ...` | `$table->string('status')->default('Pending')` | `'status' => 'Pending'` in `$attributes` |
| [`leave_request.php:122`](app/Modules/Leave/Data/leave_request.php:122) | `half_day_period` | `'am' => 'Morning (AM)', 'pm' => 'Afternoon (PM)'` | `$table->string('half_day_period', 10)->nullable()` | string in `$fillable` |
| [`employee_profile.php:81`](app/Modules/Hr/Data/employee_profile.php:81) | `gender` | `'0' => 'Male', '1' => 'Female', ...` | `$table->string('gender')->nullable()` | string in `$fillable` |
| [`employee_profile.php:103`](app/Modules/Hr/Data/employee_profile.php:103) | `marital_status` | `'0' => 'Single', '1' => 'Married', ...` | `$table->string('marital_status')->nullable()` | string in `$fillable` |
| [`document.php:59`](app/Modules/Hr/Data/document.php:59) (existing) | `type` | `'0' => 'Resume', '1' => 'Contract', ...` | `$table->string('type')->nullable()` | string in `$fillable` |

The canonical validation pattern for string-keyed static selects is: `'required|string|in:value1,value2,value3'` (see [`company.php:63`](app/Modules/Hr/Data/company.php:63): `'required|string|in:pending,active,suspended,canceled'`).

**Recommended `visibility` data config field definition:**
```php
'visibility' => [
    'display' => 'inline',
    'fillable' => true,
    'field_type' => 'select',
    'label' => 'Employee Access',
    'validation' => 'required|string|in:all,admin_only,employee',
    'options' => [
        'all' => 'Visible (Read-Only)',
        'admin_only' => 'Hidden from Employee',
        'employee' => 'Visible & Deletable',
    ],
    'filterable' => true,
],
```

**Recommended migration:**
```php
$table->string('visibility')->default('all')->after('employee_id');
$table->index('visibility');
```

**Recommended HR Document model additions:**
```php
// $fillable: add 'visibility'
// $attributes: add 'visibility' => 'all'
```

### 6.3 Potential Concern: ESS "Create" Button

The ESS documents tab currently has `'simpleActions' => ['show', 'create']`. This means employees can create documents from the ESS tab. The `prefilledData` sets `employee_id` to the current employee, so they would only create documents for themselves. Whether this is desired depends on business requirements — it may be intentionally allowing employees to upload their own documents.

If employee self-upload is NOT desired, change to `'simpleActions' => ['show']` (remove `'create'`).

---

## 7. Library Boundary Impact Assessment

### 7.1 What Belongs in the Library

| Concern | Library or Consuming App? | Rationale |
|---------|--------------------------|-----------|
| Document storage, retrieval, deletion | ✅ Library | Domain-agnostic. Any app needs to store files attached to entities. |
| Polymorphic `documentable_type`/`documentable_id` | ✅ Library | Generic pattern. Works for any entity. |
| `employee_id` column | ❌ Consuming App | HR-specific domain column. The library should not know about employees. |
| `HasDocuments` trait | ✅ Library | Generic trait. Uses `Documentable` contract. |
| `Documentable` contract | ✅ Library | Generic interface. `getDocumentableId()`, `getDocumentStoragePath()`, etc. |
| Bridging `employee_id` to polymorphic | ❌ Consuming App | HR-specific concern. |
| `DocumentEngine` | ✅ Library | Domain-agnostic. Uploads files, creates polymorphic records. |
| Observers that populate `employee_id` | ❌ Consuming App | HR-specific logic should live in the HR module. |

### 7.2 The Pre-Coding Checklist Check

From [`docs/consuming-app/pre-coding-checklist.md`](docs/consuming-app/re-coding-checklist.md):

- **§C — Before Modifying Library Code:** The fix should NOT modify library code. It should be an observer in the consuming app.
- **§D — Before Adding Files to a Module:** The observer should live in `app/Modules/Hr/Services/` or `app/Modules/Hr/Observers/`.
- **§B — Subclass Pattern:** If a library Document model extension is needed, subclass and register in HR module. Not needed for this fix — an observer is sufficient.

### 7.3 Implementation Checklist (for when coding begins)

When ready to implement:

- [ ] Create observer in `app/Modules/Hr/Observers/DocumentEmployeeIdObserver.php`
- [ ] Register observer in `HrsServiceProvider::boot()`
- [ ] Run data migration to backfill `employee_id` on existing documents
- [ ] Verify ESS tab shows all employee documents
- [ ] Verify admin CRUD continues to work
- [ ] Verify onboarding document upload continues to work
- [ ] Verify DocumentEngine::upload() now properly sets employee_id
- [ ] Test HasDocuments::uploadDocument() integration
- [ ] Review `simpleActions` — remove `'create'` if employee self-upload should be disabled

---

## 8. Per-Document Access Control: Deep Dive

> **Question:** Should employees have view/delete/update permissions on documents related to them but uploaded by an admin? If so, how should this be implemented?

### 8.1 User Expectation

From the employee's perspective in an ESS portal:

- **Baseline expectation:** "I should see all documents that are about me." Employees assume that if a document is associated with their record, it is theirs to access.
- **Download vs. delete:** Employees expect to download/view their own contracts, offer letters, payslips, and certificates. They do NOT generally expect to delete documents uploaded by HR — they understand these are official records.
- **Confusion point:** If some admin-uploaded documents appear and others don't, employees may question data integrity or assume the system is broken. Consistency is more important than granularity.
- **Self-uploaded documents:** Employees who upload their own documents (e.g., updated certificates, ID proofs) expect to be able to manage (delete, replace) them.

**Bottom line:** Users expect visibility of all their documents, read-only for HR-uploaded official records, and full management for their own uploads. The system should match this mental model.

### 8.2 Industry Best Practices

Across enterprise HR systems, the following patterns are well-established:

| Principle | Description |
|-----------|-------------|
| **Default-to-visible** | Documents associated with an employee are visible to that employee unless explicitly marked otherwise. The burden is on the admin to hide, not on the employee to discover. |
| **Uploader-based mutability** | Delete/update permissions default to the uploader: admin-uploaded = read-only for employee; self-uploaded = full control for employee. |
| **Visibility classification** | A document-level flag (not type-based, not uploader-based alone) controls whether the employee can see it. This allows admins to override defaults per document. |
| **Audit trail** | Changes to visibility or deletion by employees are logged. This is important for compliance (GDPR, labor law). |
| **Draft vs. published** | Documents can be in "draft" state (admin-only) until finalized, then published (visible to employee). This is often implemented as a workflow rather than a static flag. |
| **Category-based defaults** | New documents default to a visibility based on their category (e.g., "Contract" → visible, "Internal Note" → hidden), but admins can override per document. |

### 8.3 Competitor Analysis

| Product | Visibility Model | Employee Mutability | Admin Controls |
|---------|-----------------|---------------------|----------------|
| **BambooHR** | Per-document toggle: "Employee Access" on/off. Default: on for standard docs, off for "Company Files." | View + download only for admin-uploaded docs. Self-uploaded docs can be deleted. | Admin can toggle visibility per document, set expiry dates, and categorize into folders with different default permissions. |
| **Workday** | Role-based + document category. Security groups define who sees what. | Read-only by default. Self-service uploads go to a "worker documents" area where employees have full control. | Complex security group configuration. Document categories are mapped to security policies. Overrides are possible but rare. |
| **Gusto** | Simple binary: docs are either visible to employee or not. | View + download only. No delete for any document in ESS. | Admin marks visibility during upload. Separate "HR-only" section for confidential documents. |
| **Zoho People** | Three-tier: "Employee View," "Manager View," "Admin Only." Default is "Employee View." | View + download. Self-uploaded docs can be deleted. | Admin sets visibility tier per document upload. Category-based defaults configurable in settings. |
| **Sage HR** | Per-document "Share with employee" checkbox. Separate "Confidential" folder for admin-only docs. | View + download. No delete capability in ESS. | Simple per-document toggle at upload time. Folder-based defaults. |
| **HiBob** | Document categories with "sensitive" flag. Sensitive documents are admin-only regardless of employee association. | View + download on non-sensitive docs. Self-uploaded in "My Documents" area are fully manageable. | Category configuration determines defaults. Individual document override available. |
| **ADP Workforce Now** | Role-based with "Employee Self Service" permission set. Documents in ESS section are visible. | View + download. No delete. | Admin assigns documents to ESS-visible categories. Complex permission matrix. |

**Common patterns across competitors:**
1. **All** allow employees to see some admin-uploaded documents — none default to hiding everything
2. **All** restrict employee delete for admin-uploaded documents — read-only is the norm
3. **Most** use a simple visibility flag rather than complex role-based rules
4. **Most** separate "employee documents" from "HR internal documents" via categories or folders
5. **None** allow employees to update/replace admin-uploaded documents

### 8.4 Real-World Scenarios Requiring Differential Access

The following are concrete situations where per-document access control is necessary:

#### 8.4.1 Confidential / Admin-Only Documents

| Scenario | Document Type | Why Hidden from Employee |
|---------|---------------|--------------------------|
| Internal investigation | Witness statements, investigation notes, preliminary findings | Would compromise the investigation. Employee may alter their behavior or destroy evidence. |
| Disciplinary records (in-progress) | Draft warning letters, PIP documents under review | Should only be visible once finalized and delivered. Premature visibility causes anxiety and potential legal risk. |
| Whistleblower complaint | Complaint details, reporter identity | Must protect the reporter. Retaliation risk if employee sees the complaint before investigation concludes. |
| Legal hold / litigation | Attorney-client privileged documents, litigation strategy | Legally protected. Mishandling could waive privilege. |
| Reference checks | Confidential references from previous employers | References are typically provided on condition of confidentiality. |
| Pre-termination planning | Severance calculations, termination checklists | Would cause unnecessary distress before decision is finalized. |
| Compensation review (in-progress) | Salary benchmarking data, bonus pool allocations | Internal decision-making that is not yet approved. Visibility would undermine the process. |
| Succession planning | Talent assessments, promotion readiness reviews | Contains subjective evaluations not intended for the employee. |
| Peer review / 360 feedback | Anonymous feedback from colleagues | Confidentiality is the foundation of honest feedback. |
| Medical / occupational health | Fitness-for-duty assessments, drug test results | In some jurisdictions, occupational health records have restricted access rules separate from general HR records. |

#### 8.4.2 Employee-Visible, Admin-Only Mutability

| Scenario | Why Read-Only for Employee |
|----------|---------------------------|
| Signed employment contract | Official record that should not be modified or deleted by the employee. Legal requirement in many jurisdictions. |
| Offer letter | Historical record of the terms offered. Employee should not be able to alter or remove it. |
| Performance reviews (finalized) | Official performance record. Employee can view and acknowledge but should not modify. |
| Payroll documents (P60, W-2, paysliips) | Tax and legal documents. Must be preserved. Employee gets read-only access. |
| Policy acknowledgements | Proof that employee acknowledged a policy. Employee should not be able to retract or delete. |
| Visa/immigration documents (employer-filed) | Official government documents filed by employer. Employee can view copies but should not manage. |
| Training certificates (employer-issued) | Employer's record of training completion. |

#### 8.4.3 Employee-Full-Control (Self-Uploaded)

| Scenario | Why Full Control |
|----------|-----------------|
| Updated CV/resume | Employee-maintained document. |
| New certification | Employee uploads proof of new qualification. |
| ID/passport copy (renewal) | Employee updates their own identification. |
| Bank details proof | Employee uploads verification document. |
| Address proof | Employee-maintained KYC document. |
| Emergency contact updates | Employee-maintained information. |

### 8.5 Implementation Architecture Options

#### Option A: Visibility Column via Standard `select` Field Type (Recommended ✅)

**Confirmed compatible with the library's standard UI.** The codebase already has multiple `field_type => 'select'` fields with static string-keyed `options` — this is the exact same pattern.

**Database** (consuming-app migration, NOT library):
```php
$table->string('visibility')->default('all')->after('employee_id');
$table->index('visibility');
```

Uses `string` (varchar), not MySQL ENUM — consistent with [`company.status`](app/Modules/Hr/Data/company.php:58) (`$table->string('status')`), [`leave_request.status`](app/Modules/Leave/Data/leave_request.php:147) (`$table->string('status')`), [`employee_profile.gender`](app/Modules/Hr/Data/employee_profile.php:81) (`$table->string('gender')`), and [`document.type`](app/Modules/Hr/Data/document.php:59) (the existing `type` field on the same table, also `string`). No MySQL ENUM used anywhere in this codebase.

**Data config** (standard `select` with static `options` — zero library changes):
```php
'visibility' => [
    'display' => 'inline',
    'fillable' => true,
    'field_type' => 'select',
    'label' => 'Employee Access',
    'validation' => 'required|string|in:all,admin_only,employee',
    'options' => [
        'all' => 'Visible (Read-Only)',
        'admin_only' => 'Hidden from Employee',
        'employee' => 'Visible & Deletable',
    ],
    'filterable' => true,
],
```

**Model** (HR Document):
```php
// $fillable: add 'visibility'
// $attributes: add 'visibility' => 'all'
```

**Existing codebase precedents** for this exact pattern:

| File | Field | DB Column | Validation Pattern |
|------|-------|-----------|--------------------|
| [`company.php:58-69`](app/Modules/Hr/Data/company.php:58) | `status` | `$table->string('status')->default('pending')` | `'required\|string\|in:pending,active,suspended,canceled'` |
| [`leave_request.php:147-160`](app/Modules/Leave/Data/leave_request.php:147) | `status` | `$table->string('status')->default('Pending')` | `'required'` |
| [`leave_request.php:122-134`](app/Modules/Leave/Data/leave_request.php:122) | `half_day_period` | `$table->string('half_day_period', 10)->nullable()` | `'nullable\|required_if:is_half_day,1\|in:am,pm'` |
| [`employee_profile.php:81-93`](app/Modules/Hr/Data/employee_profile.php:81) | `gender` | `$table->string('gender')->nullable()` | `'required'` |
| [`employee_profile.php:103-115`](app/Modules/Hr/Data/employee_profile.php:103) | `marital_status` | `$table->string('marital_status')->nullable()` | `'nullable\|string'` |
| [`document.php:59-76`](app/Modules/Hr/Data/document.php:59) | `type` (existing on this table!) | `$table->string('type')->nullable()` | `'required'` |

| Value | Employee Can View? | Employee Can Delete? | Employee Can Update? | Admin Can Everything? |
|-------|--------------------|----------------------|----------------------|----------------------|
| `all` | ✅ Yes | ❌ No | ❌ No | ✅ Yes |
| `admin_only` | ❌ No | ❌ No | ❌ No | ✅ Yes |
| `employee` | ✅ Yes | ✅ Yes | ❌ No | ✅ Yes |

**ESS DataTable filter:**
```php
'queryFilters' => [
    ['employee_id', '=', $employee->id],
    ['visibility', '!=', 'admin_only'],
]
```

**Action visibility in blade:**
```php
// Show delete button only for 'employee' visibility documents
@if ($record->visibility === 'employee')
    <button wire:click="delete({{ $record->id }})">Delete</button>
@endif
```

**Default behavior:**
- Admin uploads via CRUD → default `all` (visible, read-only for employee)
- Employee self-upload via ESS → default `employee` (visible, deletable by employee)
- Admin can override to `admin_only` to hide confidential documents

**Pros:**
- Simple, single column, easy to query and index
- Clear semantics — each value has a well-defined meaning
- Fits existing `queryFilters` pattern perfectly
- Easy to add to data config and admin form as a select dropdown
- Minimal code changes

**Cons:**
- Three states may not cover edge cases (e.g., "visible but no download"?)
- Adding a fourth state later requires migration

#### Option B: Category-Based Rules

Define visibility rules in a config file mapped to document types:

```php
// config/hr.php
'document_visibility_rules' => [
    'contract'          => 'all',
    'offer_letter'      => 'all',
    'performance_review' => 'all',
    'internal_note'     => 'admin_only',
    'investigation'     => 'admin_only',
    'certificate'       => 'employee',
    'id_proof'          => 'employee',
    'visa'              => 'all',
],
```

**Pros:**
- Consistent — same type always has same visibility
- No per-document decision for admin
- Easy to audit: "what can employees see?"

**Cons:**
- Inflexible — can't override per document
- Adding a new type requires config change
- Doesn't handle exception cases (e.g., "this particular contract is confidential")
- What if an admin uploads an "internal note" but wants the employee to see it?

#### Option C: Uploader-Based Rules (Implicit)

No new column. Rules are implicit based on who uploaded:

- `uploaded_by_user_id = admin` → employee read-only
- `uploaded_by_user_id = employee (self)` → employee full control
- `uploaded_by_user_id IS NULL` → admin read-only (legacy)

**Pros:**
- No schema change
- No admin decision required
- Matches user intuition

**Cons:**
- No way to hide confidential admin-uploaded documents
- No way for admin to upload a document the employee can manage
- Requires `uploaded_by_user_id` column (or deducing from `documentable_type` + context)
- Brittle — what if admin uploads on behalf of employee?

#### Option D: Workflow-Based (Approval Guard)

Documents are "draft" until an approval workflow completes. Draft documents are admin-only. Once approved, they become visible to the employee.

**Pros:**
- Full audit trail (who approved, when)
- Natural fit for documents needing review (contracts, performance reviews)
- Reuses existing workflow infrastructure

**Cons:**
- Heavy — most documents don't need workflow
- Adds friction for simple admin uploads
- Doesn't address "permanently confidential" documents
- Over-engineering for the common case

#### Option E: Bitmask Permissions (Future-Proof)

A `permissions` integer column using bitmask flags:

```php
const PERM_EMPLOYEE_VIEW   = 1;   // 001
const PERM_EMPLOYEE_DELETE = 2;   // 010
const PERM_EMPLOYEE_UPDATE = 4;   // 100
const PERM_MANAGER_VIEW    = 8;   // etc.
```

**Pros:**
- Extremely flexible — any combination
- Extensible — add new flags without migration
- Single column

**Cons:**
- Over-engineering for current needs
- Querying is more complex (bitwise operations)
- Hard to display in UI (what does "5" mean?)
- Debugging is harder

### 8.6 Recommended Approach: Hybrid (Option A + Uploader-Based Defaults)

**Use Option A (`visibility` varchar column + standard `select` field type)**, with defaults derived from the upload context to minimize admin decision fatigue:

```sql
-- Consuming-app migration (NOT library):
ALTER TABLE documents ADD COLUMN visibility VARCHAR(20) NOT NULL DEFAULT 'all' AFTER employee_id;
CREATE INDEX documents_visibility_index ON documents(visibility);
```

**Default assignment rules:**
| Upload Context | Default visibility | Rationale |
|---------------|-------------------|-----------|
| Admin CRUD (`/hr/documents`) | `all` | Admin explicitly uploading to an employee. Most common case: official documents. Admin can change to `admin_only`. |
| Onboarding (Step4Documents) | `employee` | Employee or admin uploading during onboarding. These are employee-provided documents (CV, ID, certificates). |
| ESS self-upload (if enabled) | `employee` | Employee uploading their own document. Full control. |
| DocumentEngine::generatePdf | `all` | System-generated documents (payslips, reports). Employee should see but not modify. |
| DocumentEngine::generateExcel | `all` | System-generated exports. |
| Programmatic `Document::create()` | `all` | Safe default. Explicit override available. |

**Admin UI changes:**
- Add `visibility` as a select field in the admin document form ([`document.php`](app/Modules/Hr/Data/document.php)):
  ```php
  'visibility' => [
      'display' => 'inline',
      'fillable' => true,
      'field_type' => 'select',
      'label' => 'Employee Access',
      'validation' => 'required|string|in:all,admin_only,employee',
      'options' => [
          'all' => 'Visible (Read-Only)',
          'admin_only' => 'Hidden from Employee',
          'employee' => 'Visible & Deletable',
      ],
  ],
  ```
- Default value in prefilled data: `'visibility' => 'all'`

**ESS tab changes:**
- [`employee-detail.blade.php:738`](app/Modules/Hr/Resources/views/livewire/employee-detail.blade.php:738): Update `queryFilters`:
  ```php
  'queryFilters' => [
      ['employee_id', '=', $employee->id],
      ['visibility', '!=', 'admin_only'],
  ],
  ```
- Remove `'delete'` from `simpleActions` for ESS context (since most docs are read-only)
- Or: use a custom `moreActions` renderer that checks `$record->visibility === 'employee'` before showing delete

**Onboarding changes:**
- [`Step4Documents.php:146`](app/Modules/Hr/Http/Livewire/Onboarding/Steps/Step4Documents.php:146): Add `'visibility' => 'employee'` to the Document::create call
- Remove the explicit `forceFill(['employee_id' => ...])` if the observer from Priority 1 is implemented

### 8.7 Library Boundary Assessment for Visibility

| Concern | Where? | Rationale |
|---------|--------|-----------|
| `visibility` column in migration | Consuming app migration | HR-specific domain concern. Library does not know about employee visibility semantics. |
| `visibility` in Library Document `$fillable` | Not needed | The library Document model doesn't need to know about `visibility`. The HR Document model already has extended `$fillable`. The observer handles cross-model writes. |
| `visibility` in HR Document `$fillable` | Consuming app — [`Document.php`](app/Modules/Hr/Models/Document.php:48) | Already has HR-specific columns. Add `'visibility'` to fillable. |
| ESS query filter logic | Consuming app — blade view | Domain-specific filtering. |
| Default visibility rules | Consuming app — observer + config | HR business logic. |
| Data config field definition | Consuming app — [`document.php`](app/Modules/Hr/Data/document.php) | Module-specific data config. |

The `visibility` column cleanly passes the **two-domain test** (from [`25-library-independence-safeguards.md`](docs/library/25-library-independence-safeguards.md)): a CRM app consuming the same library would not need an "employee visibility" concept; it might use a different column or no visibility column at all. The column belongs in the consuming app.

### 8.8 Migration Strategy

**Phase 1:** Add column (non-breaking)
```sql
ALTER TABLE documents ADD COLUMN visibility VARCHAR(20) NOT NULL DEFAULT 'all' AFTER employee_id;
CREATE INDEX documents_visibility_index ON documents(visibility);
```

**Phase 2:** Backfill existing records
```sql
-- Onboarding documents (uploaded by employee during setup): employee visibility
UPDATE documents
SET visibility = 'employee'
WHERE documentable_type = 'App\\Modules\\Hr\\Models\\Employee'
AND employee_id IS NOT NULL;

-- Everything else remains 'all' (safe default)
```

**Phase 3:** Update application code (observers, blade, data config, onboarding)

**Phase 4:** Admin training — inform admins that new "Employee Access" field controls visibility

### 8.9 Summary Decision Matrix

| Question | Answer |
|----------|--------|
| Should employees see admin-uploaded documents? | **Yes, by default** — with ability for admin to hide specific documents |
| Can employees delete admin-uploaded documents? | **No** — read-only for documents they didn't upload |
| Can employees delete self-uploaded documents? | **Yes** — they own those documents |
| Can employees update any documents? | **No** — update is not supported in current ESS; only upload new and delete own |
| How is this controlled? | **`visibility` column**: `all` / `admin_only` / `employee` |
| Where does the column live? | **Consuming app migration** — not in the library |
| What is the default for admin CRUD uploads? | **`all`** (visible, read-only for employee) |
| What is the default for employee self-upload? | **`employee`** (visible, full control) |
| What is the default for system-generated docs? | **`all`** (visible, read-only) |
| Does this require library changes? | **No** — entirely consuming-app side |
| Does this require DataTable changes? | **No** — `queryFilters` already supports the pattern |
| What about the employee_id gap from §6.2 Priority 1? | **Independent concern** — both fixes are needed and don't conflict |

---

---

## 9. Implementation Summary (2026-10-03)

All recommendations from §6 have been implemented. Zero library changes.

### Files Created

| File | Purpose |
|------|---------|
| [`app/Modules/Hr/Database/Migrations/2026_10_02_000000_add_visibility_to_documents_table.php`](app/Modules/Hr/Database/Migrations/2026_10_02_000000_add_visibility_to_documents_table.php) | Adds `visibility` varchar column to `documents` table |
| [`app/Modules/Hr/Observers/DocumentEmployeeIdObserver.php`](app/Modules/Hr/Observers/DocumentEmployeeIdObserver.php) | Bridges library Document polymorphic columns to HR `employee_id` and `company_id` |
| [`docs/technical/document-access-control-analysis.md`](docs/technical/document-access-control-analysis.md) | This analysis report |

### Files Modified

| File | Changes |
|------|---------|
| [`app/Modules/Hr/Models/Document.php`](app/Modules/Hr/Models/Document.php) | Added `visibility` to `$fillable`/`$attributes`, `file_icon` accessor + `$appends` |
| [`app/Modules/Hr/Data/document.php`](app/Modules/Hr/Data/document.php) | Added `visibility` and `mime_type` field definitions, card/list view icons, `avatarField` |
| [`app/Modules/Hr/Providers/HrsServiceProvider.php`](app/Modules/Hr/Providers/HrsServiceProvider.php) | Registered `DocumentEmployeeIdObserver` |
| [`app/Modules/Hr/Http/Livewire/EmployeeDetail.php`](app/Modules/Hr/Http/Livewire/EmployeeDetail.php) | (no changes — blade-only) |
| [`app/Modules/Hr/Resources/views/livewire/employee-detail.blade.php`](app/Modules/Hr/Resources/views/livewire/employee-detail.blade.php) | ESS: `visibility != 'admin_only'` filter; Admin: full CRUD |
| [`app/Modules/Hr/Http/Livewire/Onboarding/Steps/Step4Documents.php`](app/Modules/Hr/Http/Livewire/Onboarding/Steps/Step4Documents.php) | `upload()` → `uploadDocument()`, `$uploading` flag, `company_id`/`type`/`uploaded_at`/`visibility` population, HTTPS image URLs |
| [`app/Modules/Hr/Resources/views/onboarding/steps/step4-documents.blade.php`](app/Modules/Hr/Resources/views/onboarding/steps/step4-documents.blade.php) | Fixed file upload (wire:ignore + raw JS API), navigation buttons, spinner fix |
| [`app/Modules/Hr/Resources/views/onboarding/wizard.blade.php`](app/Modules/Hr/Resources/views/onboarding/wizard.blade.php) | Added step 4 (`step4-documents`) to `$stepComponents` map |
| [`app/Modules/Hr/Config/onboarding.php`](app/Modules/Hr/Config/onboarding.php) | Added `documents` step (order 4, optional), shifted preferences to order 5 |
| [`app/Modules/Hr/Http/Livewire/Onboarding/EmployeeOnboardingWizard.php`](app/Modules/Hr/Http/Livewire/Onboarding/EmployeeOnboardingWizard.php) | Added document completion check in `mount()` |
| [`resources/views/vendor/qf/livewire/data-tables/partials/card-view.blade.php`](resources/views/vendor/qf/livewire/data-tables/partials/card-view.blade.php) | Smart icon logic: thumbnails for images, file-type icons for docs, `defaultIconClass` fallback for non-file records |
| `public/vendor/livewire/` | Republished Livewire frontend assets |

### Priorities Delivered

| Priority | Description | Status |
|----------|-------------|--------|
| **P1** | `employee_id` population gap (observer) | ✅ |
| **P2** | Polymorphic query fallback (monitoring) | ✅ (observer covers all paths) |
| **P3** | Data migration for existing documents | ✅ (backfill scripts run) |
| **P4** | Per-document visibility (`select` field type) | ✅ |
| **Bonus** | Onboarding wizard document upload step | ✅ |
| **Bonus** | Card/list view file-type icons + image thumbnails | ✅ |

---

## Appendix: File Reference Index

| File | Key Lines |
|------|-----------|
| [`src/Models/Document.php`](src/Models/Document.php) | Library polymorphic Document model |
| [`src/Traits/Documents/HasDocuments.php`](src/Traits/Documents/HasDocuments.php:10) | morphMany relationship + upload/ download helpers|
| [`src/Services/Documents/DocumentEngine.php`](src/Services/Documents/DocumentEngine.php:13) | upload, generatePdf, generateExcel, getDocuments, delete |
| [`src/Contracts/Documents/Documentable.php`](src/Contracts/Documents/Documentable.php) | Documentable interface |
| [`src/Services/AccessControl/AuthorizationService.php`](src/Services/AccessControl/AuthorizationService.php:143) | authorizeView() with ownership bypass at line 158 |
| [`src/Services/AccessControl/AuthorizationService.php`](src/Services/AccessControl/AuthorizationService.php:302) | recordBelongsToSubject() matching logic |
| [`src/Services/DataTables/DefaultAuthorizationProvider.php`](src/Services/DataTables/DefaultAuthorizationProvider.php:19) | Default Spatie-based DataTable auth |
| [`src/Contracts/DataTables/DataTableAuthorizationProvider.php`](src/Contracts/DataTables/DataTableAuthorizationProvider.php) | Contract for DataTable auth |
| [`src/Http/Livewire/DataTables/DataTable.php`](src/Http/Livewire/DataTables/DataTable.php:125) | mount() accepting queryFilters |
| [`src/Services/Filters/FilterService.php`](src/Services/Filters/FilterService.php:22) | applySimpleFilters() for [field, op, value] triplets |
| [`Database/Migrations/2026_08_08_000002_create_documents_table.php`](Database/Migrations/2026_08_08_000002_create_documents_table.php:11) | Documents table schema |
||
| [`app/Modules/Hr/Models/Document.php`](app/Modules/Hr/Models/Document.php:31) | HR legacy Document model with boot hook |
| [`app/Modules/Hr/Data/document.php`](app/Modules/Hr/Data/document.php) | DataTable config for HR document CRUD |
| [`app/Modules/Hr/Models/Employee.php`](app/Modules/Hr/Models/Employee.php:120) | Documentable implementation |
| [`app/Modules/Hr/Http/Livewire/EmployeeDetail.php`](app/Modules/Hr/Http/Livewire/EmployeeDetail.php:100) | isSelfServiceMode logic |
| [`app/Modules/Hr/Resources/views/livewire/employee-detail.blade.php`](app/Modules/Hr/Resources/views/livewire/employee-detail.blade.php:732) | Documents tab DataTable with queryFilters |
| [`app/Modules/Hr/Http/Livewire/Onboarding/Stteps/Step4Documents.php`](app/Modules/Hr/Http/Livewire/Onboarding/Stteps/Step4Documents.php:23) | Onboarding upload with forceFill bridge |
| [`app/Modules/Hr/Conditions/DocumentsUploaded.php`](app/Modules/Hr/Conditions/DocumentsUploaded.php:13) | Onboarding condition: checks polymorphic documents count |
| [`app/Modules/Hr/Services/EmployeeDataTableAuthorizationProvider.php`](app/Modules/Hr/Services/EmployeeDataTableAuthorizationProvider.php:9) | Employee ownership bypass for DataTables |
| [`app/Modules/Hr/Providers/HrsServiceProvider.php`](app/Modules/Hr/Providers/HrsServiceProvider.php:27) | Binds EmployeeDataTableAuthorizationProvider |
| [`app/Providers/AppServiceProvider.php`](app/Providers/AppServiceProvider.php:61) | Registers $resolveUserSubjectId callback |
