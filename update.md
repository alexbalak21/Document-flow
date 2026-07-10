# Update — Edit Draft Documents & Versioning

## Task
Allow editing of Quote and Invoice documents while they are in **Draft** status. Each save bumps the version number. Documents in any other status (Sent, Accepted, Paid, etc.) are read-only.

---

## What Changed

### New Files

| File | Description |
|---|---|
| `resources/views/documents/edit.blade.php` | Edit form — identical to create but pre-filled from `json_data`. Submits to `PUT /documents/{id}/update` |
| `database/migrations/2026_07_10_000001_add_version_to_documents_table.php` | Adds `version` integer column (default 1) to documents table |

### Modified Files

| File | What Changed |
|---|---|
| `app/Models/Document.php` | Added `version` to fillable and casts. Added `canBeEdited()` — returns true only when status is `draft` |
| `app/Http/Controllers/DocumentController.php` | Added `edit()` and `update()` methods. `renderHtml()` made `public` so `TemplateController` can call it for regeneration. `store()` now sets `version: 1` on new documents |
| `resources/views/documents/history.blade.php` | Added **Version** column with `v1`, `v2` badge. Added pencil **Edit** button — only visible when document is draft |
| `resources/views/documents/viewer.blade.php` | Toolbar now shows version badge, status badge with colour, and **Edit Draft** button (yellow, draft only) |
| `routes/web.php` | Added `GET /documents/{document}/edit`, `PUT /documents/{document}/update`, `GET /history/{document}/raw` |

---

## Commands to Run

```bash
php artisan migrate
php artisan view:clear
```

---

## How It Works

### Editing a draft
- History page → pencil icon (only on Draft rows)
- Or viewer toolbar → **✎ Edit Draft** button
- Edit form is fully pre-filled from the saved `json_data`
- Click **Save Changes** → re-renders the snapshot, bumps version from `v1` to `v2`, etc.
- Click **Preview** → opens a live preview in a new tab without saving

### Version tracking
- Every new document starts at `v1`
- Every successful edit bumps version by 1
- Version is shown as a small badge in history and in the viewer toolbar

### Read-only after draft
- Once status changes from Draft to Sent/Accepted/Paid etc., the Edit button disappears
- `canBeEdited()` on the model enforces this at the controller level too — attempting to edit a non-draft via URL returns a redirect with an error
