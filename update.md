# Update — Quote & Invoice Conversion

## Task
Implement Quote document type and Quote → Invoice conversion flow.

---

## What Changed

### New Files

| File | Description |
|---|---|
| `storage/app/templates/quote/manifest.json` | Quote template package manifest |
| `storage/app/templates/quote/form.json` | Quote form fields (same as invoice but with quote_number / valid_until) |
| `storage/app/templates/quote/template.html` | Quote HTML template with teal accent and signature line |
| `storage/app/templates/quote/style.css` | Quote styles (teal color scheme) |
| `database/migrations/2026_07_07_000004_add_status_and_parent_to_documents_table.php` | Adds `status` and `parent_id` columns to the documents table |

### Modified Files

| File | What Changed |
|---|---|
| `app/Models/Document.php` | Added status constants, `$statusColors`, `parent()`, `convertedInvoice()`, `isQuote()`, `isInvoice()`, `canBeConverted()` |
| `app/Http/Controllers/DocumentController.php` | Added `store()`, `updateStatus()`, `convert()`, refactored `renderHtml()` and `computeTotals()` into private helpers |
| `routes/web.php` | Added `documents.store`, `documents.status`, `documents.convert` routes |
| `resources/views/documents/create.blade.php` | Added Save button, Preview button, prefill from convert session |
| `resources/views/documents/history.blade.php` | Added Status column, status dropdown, Convert to Invoice button, link to generated invoice |

---

## Commands to Run

```bash
# 1. Run the new migration
php artisan migrate

# 2. Clear view cache
php artisan view:clear

# 3. Install the Quote template
# Go to http://localhost:8000/templates → click Scan & Install
```

---

## Flow

```
New Quote → fill form → Save
    ↓
History → change status to "Accepted"
    ↓
[Convert to Invoice] button appears
    ↓
Invoice form pre-filled with quote data
    ↓
Save Invoice → stored with parent_id pointing to the original quote
```

---

## Status Reference

| Status | Used on |
|---|---|
| draft | Quote, Invoice |
| sent | Quote, Invoice |
| accepted | Quote only |
| rejected | Quote only |
| invoiced | Quote (auto-set on convert) |
| paid | Invoice only |
| cancelled | Invoice only |
