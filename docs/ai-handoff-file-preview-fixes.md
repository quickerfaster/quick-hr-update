# AI Handoff Prompt — File Preview & Modal Issues

Paste this entire message into a new session.

---

## Context

I'm building an HR application on top of the QuickerFaster UI Library (`quicker-faster/ui-library`). The app follows a modular architecture under `app/Modules/{ModuleName}/`. The library provides the DataTable, Form, Navigation, Workflow, and multi-tenant infrastructure.

We just completed a large session covering:
- Progressive Module Rollout (Phase 1: HR + Admin + Organization only)
- Login/Reset Password fixes
- Password visibility toggles
- Onboarding wizard improvements
- Incomplete Onboarding page
- Company switcher scroll fix
- All-companies permission fix
- Seeder environment guards

All changes are committed and pushed to both repos.

## Current Task: Fix File Preview Issues in DataTable Forms

The DataTable form supports file uploads via `FileField` and `ImageField` components. There are three preview issues:

### Issue 1: Image Preview — Second Click Fails
- **FileField** with `isImage=true` shows a "Preview" button
- First click opens the modal correctly
- After closing the modal, clicking "Preview" again does nothing
- Page refresh restores functionality

### Issue 2: PDF Preview — Blank Modal
- Selecting a PDF and clicking "Preview" shows an empty modal
- No error, just blank

### Issue 3: .docx Preview — Works Correctly
- .docx files can be previewed multiple times without saving
- This is the expected behavior for all file types

---

## Project Structure

```
Library:          /Users/mac/Projects/Libraries/ui-library/
Consuming App:    /Users/mac/Projects/LaravelProjects/hr-consuming-app/
HR Module:        app/Modules/Hr/
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

---

## Root Cause Analysis (Already Completed)

### Architecture of File Preview

Three components involved:

```
file.blade.php (Alpine x-data)
  └─ FileReader.readAsDataURL() → data URL
       └─ Livewire.dispatch('openDocumentPreview')
            └─ DocumentPreviewModal (Livewire)
                 └─ DocumentPreview (Livewire)
                      └─ preview-{type}.blade.php
```

### Issue 1: Image Preview Second Click Fails

**Root Cause A — `openPreview()` ignores `previewUrl`:**

In [`file.blade.php:28-46`](src/Resources/views/components/fields/file.blade.php:28), `handleFileSelect()` stores image data URLs in `previewUrl` but document data URLs in `tempFileDataUrl`:

```js
handleFileSelect(file) {
    if (isImage && file.type.startsWith('image/')) {
        reader.onload = (e) => { this.previewUrl = e.target.result; };      // image → previewUrl
    } else if (!isImage) {
        reader.onload = (e) => { this.tempFileDataUrl = e.target.result; }; // doc → tempFileDataUrl
    }
}
```

But `openPreview()` at line 15-27 only reads `tempFileDataUrl`, never `previewUrl`:

```js
openPreview() {
    const url = this.tempFileDataUrl || this.existingFileUrl;
    //               ↑ used for docs      ↑ used for already-saved files
    //               previewUrl is NEVER checked!
}
```

For a new image upload: `tempFileDataUrl` = null, `existingFileUrl` = null → `url` = null → preview dispatches with no URL.

**Root Cause B — Modal won't reopen after close:**

The `DocumentPreviewModal` at [`document-preview-modal.blade.php:2`](src/Resources/views/livewire/modals/document-preview-modal.blade.php:2) has `wire:ignore.self`:

```html
<div class="modal fade" id="document-preview-modal" wire:ignore.self ...>
```

The `open-bs-modal` event handler in [`navigation-layout.blade.php:331-333`](src/Resources/views/components/layouts/navigation-layout.blade.php:331) only calls `hideDrawer()` — it NEVER calls `bootstrap.Modal.show()`:

```js
Livewire.on('open-bs-modal', function (event) {
    if (event[0].modalId === 'document-preview-modal') hideDrawer();
    // ← Missing: new bootstrap.Modal(modalEl).show();
});
```

After Bootstrap closes the modal (via `data-bs-dismiss`), the modal stays in "hidden" state. No code re-opens it.

### Issue 2: PDF Preview Blank

PDF data URLs are stored in `tempFileDataUrl` (same as .docx), so the URL IS available. But:

1. `FileReader.readAsDataURL()` produces base64 with ~33% overhead — a 2MB PDF becomes ~2.7MB
2. The data URL is passed as a Livewire property (`$fileUrl`) in [`DocumentPreviewModal.php:22`](src/Http/Livewire/Modals/DocumentPreviewModal.php:22)
3. Livewire has a ~2MB payload size limit — the data URL is truncated or rejected
4. The `<embed>` tag in [`preview-pdf.blade.php:1`](src/Resources/views/livewire/documents/partials/preview-pdf.blade.php:1) receives empty/truncated URL → blank

### Issue 3: .docx Works

.docx files are typically small (10-500KB), so the data URL fits within Livewire's payload limit. `tempFileDataUrl` is correctly set and read by `openPreview()`.

---

## Key Files for This Task

### Library Source (modify these)

| File | Purpose |
|------|---------|
| [`src/Resources/views/components/fields/file.blade.php`](src/Resources/views/components/fields/file.blade.php) | FileField Blade — Alpine x-data with `openPreview()`, `handleFileSelect()` |
| [`src/Resources/views/components/fields/image.blade.php`](src/Resources/views/components/fields/image.blade.php) | ImageField Blade — Alpine x-data with inline preview |
| [`src/Components/FieldTypes/FileField.php`](src/Components/FieldTypes/FileField.php) | FileField PHP class |
| [`src/Components/FieldTypes/ImageField.php`](src/Components/FieldTypes/ImageField.php) | ImageField PHP class |
| [`src/Http/Livewire/Modals/DocumentPreviewModal.php`](src/Http/Livewire/Modals/DocumentPreviewModal.php) | Modal Livewire component |
| [`src/Http/Livewire/DocumentPreview.php`](src/Http/Livewire/DocumentPreview.php) | Preview renderer — detects file type |
| [`src/Resources/views/livewire/modals/document-preview-modal.blade.php`](src/Resources/views/livewire/modals/document-preview-modal.blade.php) | Modal Blade view |
| [`src/Resources/views/livewire/documents/document-preview.blade.php`](src/Resources/views/livewire/documents/document-preview.blade.php) | Preview dispatcher |
| [`src/Resources/views/livewire/documents/partials/preview-pdf.blade.php`](src/Resources/views/livewire/documents/partials/preview-pdf.blade.php) | PDF preview (`<embed>`) |
| [`src/Resources/views/livewire/documents/partials/preview-image.blade.php`](src/Resources/views/livewire/documents/partials/preview-image.blade.php) | Image preview (`<img>`) |
| [`src/Resources/views/livewire/documents/partials/preview-office.blade.php`](src/Resources/views/livewire/documents/partials/preview-office.blade.php) | Office preview (Mammoth.js) |
| [`src/Resources/views/components/layouts/navigation-layout.blade.php`](src/Resources/views/components/layouts/navigation-layout.blade.php) | Layout — `open-bs-modal` / `close-bs-modal` handlers |

### Published Views (must also update — these take precedence!)

| File | Purpose |
|------|---------|
| `resources/views/vendor/qf/components/fields/file.blade.php` | Published copy of FileField Blade |
| `resources/views/vendor/qf/components/fields/image.blade.php` | Published copy of ImageField Blade |
| `resources/views/vendor/qf/components/layouts/navigation-layout.blade.php` | Published copy of layout |

### Vendor Copies (must also update)

| File | Purpose |
|------|---------|
| `vendor/quicker-faster/ui-library/src/Resources/views/components/fields/file.blade.php` | Vendor copy |
| `vendor/quicker-faster/ui-library/src/Resources/views/components/fields/image.blade.php` | Vendor copy |
| `vendor/quicker-faster/ui-library/src/Resources/views/components/layouts/navigation-layout.blade.php` | Vendor copy |
| `vendor/quicker-faster/ui-library/src/Http/Livewire/Modals/DocumentPreviewModal.php` | Vendor copy |
| `vendor/quicker-faster/ui-library/src/Http/Livewire/DocumentPreview.php` | Vendor copy |

---

## Recommended Fixes

### Fix 1: `openPreview()` Should Use `previewUrl` for Images

In [`file.blade.php:17`](src/Resources/views/components/fields/file.blade.php:17), change:

```js
// Before
const url = this.tempFileDataUrl || this.existingFileUrl;

// After
const url = this.previewUrl || this.tempFileDataUrl || this.existingFileUrl;
```

This ensures image data URLs (stored in `previewUrl`) are used when available.

### Fix 2: Add `bootstrap.Modal.show()` to Modal Open Handler

In [`navigation-layout.blade.php:331-333`](src/Resources/views/components/layouts/navigation-layout.blade.php:331), add modal show:

```js
Livewire.on('open-bs-modal', function (event) {
    if (event[0].modalId === 'document-preview-modal') {
        hideDrawer();
        var modalEl = document.getElementById('document-preview-modal');
        if (modalEl) {
            var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.show();
        }
    }
});
```

And in the close handler, properly hide:

```js
Livewire.on('close-bs-modal', function (event) {
    if (event[0].modalId === 'document-preview-modal') {
        showDrawer();
        var modalEl = document.getElementById('document-preview-modal');
        if (modalEl) {
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }
    }
});
```

### Fix 3: Use Blob URL Instead of Data URL for Large Files (PDFs)

In [`file.blade.php:38-45`](src/Resources/views/components/fields/file.blade.php:38), instead of `readAsDataURL()`, use `URL.createObjectURL()`:

```js
} else if (!isImage) {
    // Use blob URL instead of data URL to avoid Livewire payload size limits
    this.tempFileDataUrl = URL.createObjectURL(file);
}
```

This creates a blob URL like `blob:http://...` which is a small reference, not the full file content. The `<embed>` tag can render blob URLs for PDFs. Note: blob URLs must be revoked with `URL.revokeObjectURL()` when no longer needed.

### Fix 4: Remove `wire:ignore.self` from Modal

In [`document-preview-modal.blade.php:2`](src/Resources/views/livewire/modals/document-preview-modal.blade.php:2), consider removing `wire:ignore.self` so Livewire can properly manage the modal's `show` class. If Bootstrap JS handles show/hide, this may not be needed.

---

## Known Issues from Previous Sessions

1. **Published views**: `resources/views/vendor/qf/` contains published copies of library views. These take precedence over vendor copies. Any library view change must be applied to BOTH the library source AND the published copy.
2. **ModelConfigRepository cache**: Caches model configs for 24 hours. Run `app(ModelConfigRepository::class)->flush()` after config changes.
3. **Vendor is gitignored**: The consuming app's `vendor/` is in `.gitignore`. Library fixes reach production via `composer update` on deploy (cPanel uses `composer update --no-dev`).
4. **`.cpanel.yml` is preserved on server**: The server's copy is never overwritten by deploys. Manual update needed for deploy script changes.

---

## Notes

- The `file.blade.php` component handles BOTH images (`isImage=true`) and documents (`isImage=false`)
- The `image.blade.php` component is a separate field type for image-only uploads with crop support
- Alpine.js `x-data` state is destroyed when Livewire re-renders the parent component
- `wire:ignore.self` prevents Livewire from updating an element but allows children to be updated
- Bootstrap 5 modals need explicit `.show()` / `.hide()` calls when managed programmatically
- `URL.createObjectURL()` is preferred over `FileReader.readAsDataURL()` for large files — it creates a lightweight blob reference instead of a multi-MB base64 string
