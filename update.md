# Update — Template Re-scan & Snapshot Regeneration

## Task
Add ability to rescan templates after editing files, update the DB records, and regenerate all saved document snapshots so existing documents reflect the latest template.

---

## What Changed

### Modified Files

| File | What Changed |
|---|---|
| `app/Http/Controllers/TemplateController.php` | Added `rescan()` — updates all DB records + regenerates all snapshots. Added `regenerate(DocumentType)` — regenerates snapshots for one template only. Refactored shared scan logic into `scanTemplates()` |
| `app/Models/DocumentType.php` | Added `documents()` HasMany relationship (needed for `withCount`) |
| `resources/views/templates/index.blade.php` | Added **Rescan & Update All** button (yellow), per-template **Regenerate** button, document count badge, template folder path |
| `routes/web.php` | Added `POST /templates/rescan` and `POST /templates/{template}/regenerate` |

---

## Commands to Run

```bash
php artisan view:clear
```

No migrations needed.

---

## How It Works

### Scan & Install (blue button)
- Scans `storage/app/templates/`
- Only installs templates that don't exist yet in the DB
- Safe to run anytime — won't touch existing templates

### Rescan & Update All (yellow button)
- Scans all template folders
- Updates every existing DB record (name, version, paths, description)
- Re-renders and saves the `html_snapshot` for every existing document
- Installs any new templates found
- Use this after editing `template.html`, `style.css`, or `manifest.json`

### Regenerate (per-template button)
- Only regenerates snapshots for one specific template type
- Useful when you only changed one template and don't want to touch others
- Shows the number of documents that will be regenerated before confirming
