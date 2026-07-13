# Update — Template Accent Color Picker

## Task
Replace hardcoded hex colors in all template CSS files with CSS custom properties (`--accent`, `--accent-light`). Add a color picker per template on the `/templates` page. Changing the color updates the DB, regenerates all document snapshots, and the new color takes effect immediately.

---

## What Changed

### New Files

| File | Description |
|---|---|
| `database/migrations/2026_07_12_000001_add_accent_color_to_document_types.php` | Adds `accent_color` column to `document_types` table |

### Modified Files

| File | What Changed |
|---|---|
| `app/Models/DocumentType.php` | Added `accent_color` to fillable. Added `readManifest()` helper to read manifest.json |
| `app/Http/Controllers/DocumentController.php` | `renderHtml()` injects `:root{--accent:X;--accent-light:Y;}` override before template CSS |
| `app/Http/Controllers/TemplateController.php` | `scanTemplates()` reads `accent_color` from manifest. Added `updateColor()` AJAX endpoint — saves color to DB and regenerates all snapshots |
| `resources/views/templates/index.blade.php` | Color picker added to each card. Shows hex value. Updates via AJAX on change. Shows "Saving..." then success/error message |
| `resources/views/layouts/auth.blade.php` | Added `<meta name="csrf-token">` for AJAX calls |
| `routes/web.php` | Added `PATCH /templates/{template}/color` → `templates.color` |
| `storage/app/templates/*/style.css` | All hardcoded hex accent colors replaced with `var(--accent)` and `var(--accent-light)`. `:root` block with defaults added at top of each file |
| `storage/app/templates/*/manifest.json` | Added `accent_color` and `accent_color_light` fields |

---

## Commands to Run

```bash
php artisan migrate
php artisan view:clear
```

Then go to `/templates` → **Rescan & Update All** to pick up the new manifest fields and updated CSS files.

---

## How It Works

### CSS variable injection
Every `style.css` now starts with:
```css
:root {
    --accent:       #0d9488;
    --accent-light: #f0fdfa;
}
```

All color references use `var(--accent)` and `var(--accent-light)` instead of hardcoded hex.

At render time, `DocumentController::renderHtml()` prepends:
```css
:root{--accent:#NEW_COLOR;--accent-light:#NEW_LIGHT;}
```

This overrides the defaults in the CSS file — so the DB color wins.

### Color picker flow
1. Open `/templates`
2. Each card shows a color swatch input and hex label
3. Pick a new color → AJAX `PATCH` to `/templates/{id}/color`
4. Controller saves to DB + regenerates all document snapshots
5. Shows "X document(s) regenerated" confirmation

### Default accent colors

| Template | Accent |
|---|---|
| Invoice | `#1a56db` (blue) |
| Quote | `#0d9488` (teal) |
| Quote FR | `#0d9488` (teal) |
| Facture FR | `#1a56db` (blue) |
| Proposal | `#4f46e5` (indigo) |
| Delivery Note | `#1a56db` (blue) |
| TSCA Statement | `#1a56db` (blue) |
| USDA Statement | `#1a56db` (blue) |
