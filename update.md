# Update — ZIP Upload, Multi-language, Sidebar Icons & Groups

## Tasks
1. Upload template packages as ZIP directly from the UI
2. Multi-language support via separate template packages
3. Custom sidebar icons and groups per template

---

## What Changed

### New Files

| File | Description |
|---|---|
| `database/migrations/2026_07_10_000003_add_sidebar_fields_to_document_types.php` | Adds `icon`, `sidebar_label`, `sidebar_group` columns to `document_types` |
| `storage/app/templates/quote-fr/manifest.json` | Example French quote manifest to demonstrate multi-language setup |

### Modified Files

| File | What Changed |
|---|---|
| `app/Models/DocumentType.php` | Added `icon`, `sidebar_label`, `sidebar_group` to fillable. Added `icon_display` and `sidebar_label_display` accessors |
| `app/Http/Controllers/TemplateController.php` | Added `upload()` — handles ZIP extraction, validation, file copy, DB install/update. `scanTemplates()` now reads `icon`, `sidebar_label`, `sidebar_group` from manifest. Added `onlySlug` param |
| `resources/views/templates/index.blade.php` | Added ZIP upload form at top. Cards now show template icon and group badge. Added multi-language guide section at bottom |
| `resources/views/layouts/auth.blade.php` | Sidebar now groups templates by `sidebar_group`. Uses `icon_display` for custom icons and `sidebar_label_display` for custom labels |
| `storage/app/templates/quote/manifest.json` | Added `icon`, `sidebar_label`, `sidebar_group` |
| `storage/app/templates/invoice/manifest.json` | Added `icon`, `sidebar_label`, `sidebar_group` |
| `routes/web.php` | Added `POST /templates/upload`. Removed duplicate `raw` route |

---

## Commands to Run

```bash
php artisan migrate
php artisan view:clear
```

Then go to `/templates` → **Rescan & Update All** to pick up the new manifest fields.

---

## Feature 1 — ZIP Upload

Go to `/templates` → Upload a `.zip` containing:
- `manifest.json`
- `template.html`
- `form.json`
- `style.css`

The ZIP can have files at the root or inside one subfolder. The system:
1. Extracts to a temp folder
2. Validates manifest fields and referenced files
3. Copies files to `storage/app/templates/{slug}/`
4. Installs (new) or updates (existing) the DB record
5. Regenerates all document snapshots if updating

---

## Feature 2 — Multi-language Templates

Create one package per language with a unique slug:

```
storage/app/templates/
    quote/          slug: "quote"       sidebar_group: "Sales"
    quote-fr/       slug: "quote-fr"    sidebar_group: "Ventes"
    invoice/        slug: "invoice"     sidebar_group: "Sales"
    invoice-fr/     slug: "invoice-fr"  sidebar_group: "Ventes"
```

Each has its own `template.html` (e.g. "DEVIS" vs "QUOTE"), `form.json` (French labels vs English), and `style.css`. Upload each as a ZIP. They appear as separate entries in the sidebar, grouped by `sidebar_group`.

---

## Feature 3 — Sidebar Icons & Groups

In `manifest.json`:

```json
{
    "icon": "bi-receipt",
    "sidebar_label": "Invoice",
    "sidebar_group": "Sales"
}
```

- `icon` — any Bootstrap Icons class (see icons.getbootstrap.com)
- `sidebar_label` — short label shown in sidebar (defaults to `name`)
- `sidebar_group` — section header in sidebar (templates with same group are listed together)

## New manifest.json Fields Reference

| Field | Required | Default | Description |
|---|---|---|---|
| `icon` | No | `bi-file-earmark-text` | Bootstrap Icons class |
| `sidebar_label` | No | same as `name` | Short label for sidebar |
| `sidebar_group` | No | `Documents` | Section group in sidebar |
