# Storage & File Upload Deployment (cPanel)

## Problem
On the live server, uploaded document links rendered but returned no file. Root cause: the `storage/app/public` directory structure and the document-root `storage` symlink were missing.

## Server layout
The app is **not** served from the Laravel `public/` directory. From the document root `index.php`:

```
Document root:  /home/quickerf/public_html/agriwatts.quickerfaster.com/
Laravel app:    /home/quickerf/quick_hr/
```

`asset('storage/...')` resolves against the document root, so the symlink must live in the document root — not in `quick_hr/public/`.

```
/home/quickerf/public_html/agriwatts.quickerfaster.com/storage
    → /home/quickerf/quick_hr/storage/app/public
```

## Fix (already applied to `.cpanel.yml`)
[`.cpanel.yml`](../.cpanel.yml) now:

1. Exports `DOCROOT` pointing to the document root.
2. Creates `$DEPLOYPATH/storage/app/public/uploads/{hr.document,hr.employee_profile}` and `$DEPLOYPATH/storage/app/public/documents`.
3. Creates the symlink `$DOCROOT/storage → $DEPLOYPATH/storage/app/public` if missing.

The deploy script already preserves `storage/` and `.cpanel.yml` across deploys.

## Important: manual server update
The server's `.cpanel.yml` is preserved during deploys, so after editing the repo copy you must manually copy the updated file to the server (`/home/quickerf/quick_hr/.cpanel.yml`).

## One-time diagnostics / fix scripts
Kept in [`scripts/`](../scripts/):

- `storage-check.php` — diagnose symlink, directories, and file presence.
- `storage-setup.php` — legacy first attempt (wrong base path; superseded).
- `storage-fix.php` — correct auto-detecting fix that reads `index.php` to find the Laravel base.

To use: copy the script to the document root, open it in the browser, then delete it.

## Uploaded files are not in git
`storage/app/public/` is gitignored. After a fresh deploy, the files themselves must be either:

- copied from local: `scp -r storage/app/public/uploads/* user@server:.../storage/app/public/uploads/`, or
- re-uploaded through the application.
