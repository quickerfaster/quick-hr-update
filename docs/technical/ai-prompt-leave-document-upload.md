# AI Prompt — Add Optional Document Upload to Leave Request Wizard

> Paste this into a new AI session (Architect or Code mode) to add an optional document upload step to the leave request wizard, similar to the onboarding wizard's Step 4.

---

## Task

Add an optional document upload step to the leave request wizard at `https://hr-consuming-app.test/leave/leave-hub?tab=apply-for-employee`. The leave request wizard currently has 2 steps (Request Details → Review). Add a 3rd optional step for document upload, modeled after the onboarding wizard's Step 4 (`Step4Documents`).

A `LeaveDocumentUpload` Livewire component already exists but is NOT integrated into the wizard — it's only used on the leave request detail page. The task is to integrate it (or a similar component) as an optional wizard step.

---

## Context — Codebase Layout

You are working on a Laravel/Livewire HR application at `/Users/mac/Projects/LaravelProjects/hr-consuming-app/` that consumes a domain-agnostic UI library at `/Users/mac/Projects/Libraries/ui-library/`.

### Key Source Files

**Onboarding Document Upload (reference implementation):**
- [`app/Modules/Hr/Http/Livewire/Onboarding/Steps/Step4Documents.php`](app/Modules/Hr/Http/Livewire/Onboarding/Steps/Step4Documents.php:1) — Self-contained multi-file uploader. Uploads are immediate — each file is persisted to disk and a polymorphic `Document` record is created on every upload action. Supports document type selection (identification, certificate, contract, CV, other). Max 10 files, 10MB each.
- [`app/Modules/Hr/Resources/views/livewire/onboarding/step4-documents.blade.php`](app/Modules/Hr/Resources/views/livewire/onboarding/step4-documents.blade.php) — The blade view with file input, document type dropdown, uploaded files list with preview/delete.

**Leave Document Upload (already exists, not in wizard):**
- [`app/Modules/Leave/Http/Livewire/LeaveDocumentUpload.php`](app/Modules/Leave/Http/Livewire/LeaveDocumentUpload.php:1) — Simpler component using `HasDocuments` trait (`uploadDocument()`, `deleteDocument()`, `getDocuments()`). Currently only used on the leave request detail page.
- [`app/Modules/Leave/Resources/views/livewire/leave-document-upload.blade.php`](app/Modules/Leave/Resources/views/livewire/leave-document-upload.blade.php) — The blade view.

**Leave Request Wizard:**
- [`app/Modules/Leave/Http/Livewire/LeaveWizardForm.php`](app/Modules/Leave/Http/Livewire/LeaveWizardForm.php:1) — Extends `WizardForm`. Has custom `checkLeaveBalance()` validation. Currently 2 steps.
- [`app/Modules/Leave/Resources/views/livewire/leave-wizard-form.blade.php`](app/Modules/Leave/Resources/views/livewire/leave-wizard-form.blade.php) — The wizard blade view.

**Leave Request Model:**
- [`app/Modules/Leave/Models/LeaveRequest.php`](app/Modules/Leave/Models/LeaveRequest.php:1) — Implements `Workflowable` and `Documentable`. Uses `HasDocuments` trait.

**Library — Wizard base classes:**
- [`src/Http/Livewire/Wizards/WizardForm.php`](src/Http/Livewire/Wizards/WizardForm.php:1) — Base wizard form class. Handles step navigation, validation, and model persistence.
- [`src/Http/Livewire/Wizards/Wizard.php`](src/Http/Livewire/Wizards/Wizard.php:1) — Base wizard class (used by WorkflowDefinitionWizard, not WizardForm).

**Library — Document system:**
- [`src/Contracts/Documents/Documentable.php`](src/Contracts/Documents/Documentable.php:1) — Contract for models that can have documents.
- [`src/Traits/Documents/HasDocuments.php`](src/Traits/Documents/HasDocuments.php:1) — Trait providing `uploadDocument()`, `deleteDocument()`, `getDocuments()`.
- [`src/Models/Document.php`](src/Models/Document.php:1) — Polymorphic Document model.

**Library docs (must be reviewed):**
- [`docs/library/pilosophy.txt`](docs/library/pilosophy.txt) — Core: library must be decoupled from consuming app; consuming app modules must be self-contained.
- [`docs/library/25-library-independence-safeguards.md`](docs/library/25-library-independence-safeguards.md) — Non-negotiable: no `App\Modules\*` references in library code.
- [`docs/consuming-app/pre-coding-checklist.md`](docs/consuming-app/pre-coding-checklist.md) — **Must be reviewed before writing any code.** Covers: Blade views, Livewire components, library modifications, module file placement, navigation, row actions, boolean fields, workflow notifications, active-state logic.
- [`docs/debug-checklist.md`](docs/debug-checklist.md) — **§8 is critical**: "Library Changes Not Reflecting in Consuming App — Stale Files." When you edit a file in the library workspace and the consuming app doesn't show the change, the problem is almost always a stale copy in `vendor/quicker-faster/ui-library/` or `resources/views/vendor/qf/`. Use `grep -r "ENDPATH.*component-name" storage/framework/views/` to find where the view is actually loading from.

---

## Current Behavior

The leave request wizard has 2 steps:
1. **Request Details** — Employee, Leave Type, Start/End Date, Half Day, Reason
2. **Review** — Summary of the request + approval workflow preview

No document upload capability exists in the wizard.

## Desired Behavior

Add an optional 3rd step between "Request Details" and "Review":
1. **Request Details** (unchanged)
2. **🆕 Documents** (optional, skippable) — Upload supporting documents (medical certificates, etc.)
3. **Review** (unchanged)

The document step should:
- Be **optional** — user can skip it
- Allow uploading multiple files (PDF, JPG, PNG, DOC, DOCX)
- Allow selecting a document type (medical_certificate, supporting_document, other)
- Show uploaded files with preview and delete
- Use the existing `HasDocuments` trait on `LeaveRequest` (no need to build from scratch like Step4Documents)
- Be self-contained — uploads persist immediately, not on wizard completion

---

## Specific Analysis Required

### 1. How the Onboarding Step4Documents Works

Trace the onboarding document upload flow:
- `Step4Documents` is a standalone Livewire component (not extending any wizard base class)
- It receives an `Employee` model via `mount()`
- Uploads are immediate — each file is stored and a `Document` record is created on every `uploadDocument()` call
- The wizard blade renders `<livewire:hr.onboarding.step4-documents :employee="$employee" />`
- The step is skippable — `skipStep` listener calls `skip()` which just dispatches `stepComplete`

### 2. How the LeaveDocumentUpload Works

- Uses `HasDocuments` trait methods (`uploadDocument()`, `deleteDocument()`, `getDocuments()`)
- Receives a `LeaveRequest` via `mount()`
- Simpler than Step4Documents — no document type selection, no file count limit
- Currently registered as `leave-document-upload` in `LeaveServiceProvider`

### 3. Integration Approach

**Option A — Use existing `LeaveDocumentUpload` as a wizard step:**
- Add a 3rd step to the wizard that renders `<livewire:leave-document-upload :leaveRequest="$record" />`
- The component already works with `LeaveRequest`
- Need to add document type selection and file count limit

**Option B — Create a new wizard-specific component:**
- Model after `Step4Documents` but for `LeaveRequest`
- More control over the wizard integration

**Recommendation: Option A** — enhance the existing `LeaveDocumentUpload` with document type and limits, then add it as a wizard step. Less code, reuses existing component.

### 4. Wizard Step Integration

The `WizardForm` base class manages steps via the `$steps` array and `$currentStep` property. To add a step:
1. Add a step definition to the wizard's `mount()` or constructor
2. Add the step's blade content in the wizard view
3. Handle the step's save/skip logic

Check how the existing 2 steps are defined in `LeaveWizardForm` and its blade view.

### 5. Library Boundary Check

Per the pre-coding checklist §C:
- ✅ This change is in consuming app code (`app/Modules/Leave/`)
- ✅ No library modifications needed
- ✅ The `HasDocuments` trait and `Documentable` contract are already in the library
- ✅ The `LeaveDocumentUpload` component is already in the consuming app

### 6. Stale Files Warning

**⚠️ CRITICAL**: After making changes to any blade view or PHP class, check ALL THREE locations:
1. Workspace: `app/Modules/Leave/...`
2. Vendor: `vendor/quicker-faster/ui-library/...` (if modifying library files)
3. Published views: `resources/views/vendor/qf/...` (if a published copy exists)

Use the diagnostic from Debug Checklist §8:
```bash
grep -r "ENDPATH.*leave-document-upload" storage/framework/views/
```

Always run `php artisan optimize:clear` after changes.

---

## Output Format

1. Review the pre-coding checklist at [`docs/consuming-app/pre-coding-checklist.md`](docs/consuming-app/pre-coding-checklist.md)
2. Analyze `Step4Documents` and `LeaveDocumentUpload` to understand both approaches
3. Enhance `LeaveDocumentUpload` with document type selection and file limits
4. Add the document step to `LeaveWizardForm` (step definitions, navigation, blade content)
5. Update the wizard blade view to render the document upload component
6. Test that the step is skippable and documents persist after wizard completion
7. Sync any published views and clear caches
8. Update relevant documentation
