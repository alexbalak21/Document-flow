# Update — Auto-generated Document Numbers

## What This Does

Document numbers (invoice numbers, quote numbers, etc.) are now generated
automatically on save. No more typing them manually.

Format: `{PREFIX}{YYYYMMDD}-{N}`

Examples:
- `INV-20260711-1`
- `Q-20260711-3`
- `FACT-20260711-1`
- `DEV-20260711-2`

The counter resets each day. Each prefix has its own independent counter.

---

## Files Changed

### New Files

| File | Description |
|---|---|
| `database/migrations/2026_07_11_000001_create_document_counters_table.php` | New table: one row per prefix per day, stores the running counter |
| `app/Services/DocumentNumberService.php` | Generates the next number atomically (DB transaction + row lock) |

### Modified Files

| File | What Changed |
|---|---|
| `app/Http/Controllers/DocumentController.php` | `store()` now calls `DocumentNumberService` to inject the number before saving; added `resolveNumberKey()` helper |
| `resources/views/components/document/form-section.blade.php` | Fields with `"auto": true` render as read-only display + hidden input instead of a text input |
| `storage/app/templates/invoice/manifest.json` | Added `"prefix": "INV-"` |
| `storage/app/templates/invoice/form.json` | Added `"auto": true` on `invoice_number` field |
| `storage/app/templates/quote/manifest.json` | Added `"prefix": "Q-"` |
| `storage/app/templates/quote/form.json` | Added `"auto": true` on `quote_number` field |
| `storage/app/templates/quote-fr/manifest.json` | Added `"prefix": "DEV-"` |
| `storage/app/templates/quote-fr/form.json` | Added `"auto": true` on `quote_number` field |
| `storage/app/templates/facture-fr/manifest.json` | Added `"prefix": "FACT-"` |
| `storage/app/templates/facture-fr/form.json` | Added `"auto": true` on `invoice_number` field |

---

## Commands to Run

```bash
php artisan migrate
php artisan view:clear
```

---

## How It Works

### database: `document_counters`

```
prefix  | date       | counter
--------|------------|--------
INV-    | 2026-07-11 | 3
Q-      | 2026-07-11 | 1
FACT-   | 2026-07-11 | 1
DEV-    | 2026-07-11 | 2
```

One row per prefix per day. The counter increments inside a transaction
with `lockForUpdate()` so concurrent saves never produce duplicate numbers.

### manifest.json — new `prefix` field

Each template now declares its prefix:

```json
{
    "prefix": "INV-"
}
```

This is where prefixes live — no hardcoding in PHP. Adding a new template
with a new prefix requires only adding `"prefix"` to its `manifest.json`.

### form.json — new `"auto": true` flag

The number field in each template's `form.json` is marked:

```json
{
    "type": "text",
    "name": "invoice_number",
    "label": "Invoice Number",
    "required": true,
    "auto": true
}
```

This flag tells the Blade component to render it as read-only and tells
the controller to generate the value server-side.

### On the create form

The number field shows a greyed-out placeholder:
> *Will be generated on save*

A hidden input with an empty value is submitted. The controller detects
the empty value and generates the number.

### On the edit form (drafts only)

The existing number is shown read-only and submitted via hidden input.
It is never regenerated — the number assigned on creation is permanent.

### Quote → Invoice conversion

The session data from the quote contains `quote_number` but not
`invoice_number`. Since `invoice_number` arrives empty, a fresh `INV-`
number is generated for the new invoice automatically.

---

## Adding a New Template with Auto-numbering

1. Add `"prefix": "XYZ-"` to the template's `manifest.json`
2. Add `"auto": true` to the number field in `form.json`
3. Done — no PHP changes needed

---

## Prefix Reference

| Template | Prefix |
|---|---|
| Invoice (EN/FR bilingual) | `INV-` |
| Quote (EN/FR bilingual) | `Q-` |
| Facture (FR standalone) | `FACT-` |
| Devis (FR standalone) | `DEV-` |