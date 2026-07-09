# Update — Document Viewer Shell

## Task
Documents opened from history were rendering as raw HTML on a white page with no visual boundary. Add a proper A4 viewer with a grey background, page shadow, and a toolbar.

---

## What Changed

### New Files

| File | Description |
|---|---|
| `resources/views/documents/viewer.blade.php` | Viewer shell — grey background, A4 white page with drop shadow, fixed toolbar with Back / History / Print buttons |

### Modified Files

| File | What Changed |
|---|---|
| `app/Http/Controllers/DocumentController.php` | `show()` now extracts the `<body>` content from the stored HTML snapshot and passes it to `documents.viewer` instead of returning raw HTML |

---

## Commands to Run

```bash
php artisan view:clear
```

No migrations needed.

---

## How It Works

- `show()` extracts everything between `<body>...</body>` from the saved `html_snapshot`
- That content is injected into `viewer.blade.php` inside a `210mm` wide A4 div
- On screen: grey `#4b5563` background + white page + drop shadow
- On print (`Ctrl+P` or Print / Save PDF button): toolbar and background hidden, clean document only  