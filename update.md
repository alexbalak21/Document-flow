# Update — Sidebar Navigation & Document Type Pages

## Task
Add a collapsible sidebar menu with per-document-type sections, a document type landing page with Create and "Create from" buttons, and an improved dashboard with per-type stats.

---

## What Changed

### New Files

| File | Description |
|---|---|
| `resources/views/documents/page.blade.php` | Landing page for each document type. Shows Create button, Convert From button (when applicable), and last 10 documents of that type |

### Modified Files

| File | What Changed |
|---|---|
| `resources/views/layouts/auth.blade.php` | Full replacement — new fixed collapsible sidebar with document type groups, collapse toggle button, state saved in localStorage |
| `resources/views/dashboard.blade.php` | Cleaner dashboard — 4 stat cards, per-type status breakdown cards, recent documents table |
| `app/Http/Controllers/DashboardController.php` | Added `customerCount`, `typeStats` (per-type status counts) |
| `app/Http/Controllers/DocumentController.php` | Added `page()` method with convert source logic; `store()` now redirects to document page |
| `routes/web.php` | Added `GET /documents/{slug}` → `documents.page` |

---

## Commands to Run

```bash
php artisan view:clear
```

No migrations needed.

---

## How the Sidebar Works

- Fixed on the left, **240px** wide
- Click the **◀ toggle button** to collapse to **56px** (icons only)
- Collapsed state is saved in `localStorage` — persists across page loads
- Each document type has a **collapsible group** with 3 sub-links:
  - Document Page (landing)
  - New [Type] (create form)
  - History (filtered)
- Active route is highlighted with a blue left border

## How the Document Page Works

Each document type gets its own page at `/documents/{slug}`:

- **Create [Type]** — always shown, links to the create form
- **Create from [SourceType]** — shown only when:
  - A convert relationship is configured (currently: Invoice ← Quote)
  - There are accepted, unconverted source documents available
  - Includes a dropdown to pick which document to convert
- **Recent documents table** — last 10 of that type with status badges

## Convert Map

Defined in `DocumentController::page()`:

```php
$convertMap = [
    'invoice' => ['quote'],  // Invoice can be created from a Quote
];
```

To add more relationships later (e.g. Delivery Note from Invoice), just add entries here.
