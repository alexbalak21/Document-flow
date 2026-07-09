# V1:

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


# NEW SESSION

# Update — Customer Table & Picker

## Task
Add a customers table. When creating a Quote or Invoice, the user can select an existing customer or type a new one. Every customer is automatically saved to the database.

---

## What Changed

### New Files

| File | Description |
|---|---|
| `database/migrations/2026_07_07_000005_create_customers_table.php` | Creates the `customers` table with all fields |
| `database/migrations/2026_07_07_000006_add_customer_id_to_documents_table.php` | Adds `customer_id` foreign key to `documents` |
| `app/Models/Customer.php` | Customer Eloquent model with `displayName` accessor |
| `app/Http/Controllers/CustomerController.php` | index, store (HTML + JSON/AJAX), edit, update, list (API) |
| `resources/views/customers/index.blade.php` | Customer list page with New Customer modal |
| `resources/views/customers/_form.blade.php` | Shared form partial (used in modal and edit page) |
| `resources/views/customers/edit.blade.php` | Edit customer page |

### Modified Files

| File | What Changed |
|---|---|
| `app/Models/Document.php` | Added `customer_id` to fillable, added `customer()` BelongsTo relationship |
| `app/Http/Controllers/DocumentController.php` | `create()` now passes `$customers`; `store()` saves or updates customer automatically; `convert()` passes `convert_customer_id` to session |
| `resources/views/documents/create.blade.php` | Added customer picker dropdown at top; New Customer modal with AJAX save; auto-fill of form fields when customer is selected |
| `routes/web.php` | Added customer routes: index, store, edit, update, and `/api/customers` JSON list |

---

## Commands to Run

```bash
php artisan migrate
php artisan view:clear
```

---

## How It Works

### Select existing customer
- Dropdown at the top of the form lists all saved customers
- Selecting one auto-fills all customer_* fields instantly (JS)

### Type a new customer
- Leave the dropdown on "Type a new customer below"
- Fill the fields manually
- On Save, the customer is automatically stored in the `customers` table
- The document is linked via `customer_id`

### New Customer modal
- Click "New Customer" button in the form header
- Fill the modal fields and click "Save & Select"
- Customer is saved via AJAX (no page reload)
- Dropdown updates and auto-fills the form immediately

### Customer list
- Visit `/customers` to view, add, and edit customers

---
---

# V3

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

---
---

# V4

# Update — Separate Guest & App Layouts

## Task
The login page was showing the sidebar. Split layouts so public/guest pages are clean and the sidebar only appears for authenticated pages.

---

## What Changed

### New Files

| File | Description |
|---|---|
| `resources/views/layouts/guest.blade.php` | Clean layout with no sidebar — used for login and any future guest pages |

### Modified Files

| File | What Changed |
|---|---|
| `resources/views/auth/login.blade.php` | Changed `@extends('layouts.auth')` to `@extends('layouts.guest')` |

---

## Commands to Run

```bash
php artisan view:clear
```

---

## Layout Reference

| Layout | Used for | Has sidebar |
|---|---|---|
| `layouts.guest` | Login page | No |
| `layouts.auth` | All authenticated pages | Yes |

---
---

# V5


# Update — Shared Entities System

## Task
Implement a shared entity system so that Customers and Products are declared in the template manifest and automatically injected into any document form — without writing PHP code per template.

---

## What Changed

### New Files

| File | Description |
|---|---|
| `app/Services/EntityResolver.php` | Core service — reads `entities` from manifest, loads records for pickers, saves/updates entities on document store, injects data into rendering |
| `database/migrations/2026_07_09_000001_update_products_table.php` | Changes `price` (integer cents) to `unit_price` (decimal float), makes `page_url` nullable |

### Modified Files

| File | What Changed |
|---|---|
| `app/Models/Product.php` | Uses `unit_price` (decimal), added `toEntityArray()`, updated `formattedPrice` accessor |
| `app/Models/Customer.php` | Added `toEntityArray()` |
| `app/Http/Controllers/DocumentController.php` | Injects `EntityResolver`, reads manifest entities in `create()`, `store()`, `preview()` |
| `resources/views/documents/create.blade.php` | Entity pickers rendered automatically from manifest — customer picker + product picker shown only when declared in manifest |
| `storage/app/templates/invoice/manifest.json` | Added `"entities": ["customer", "product"]`, bumped to v1.2 |
| `storage/app/templates/quote/manifest.json` | Added `"entities": ["customer", "product"]`, bumped to v1.1 |
| `storage/app/templates/invoice/form.json` | Removed manual customer fields — now handled by entity system |
| `storage/app/templates/quote/form.json` | Removed manual customer fields — now handled by entity system |
| `database/seeders/ProductSeeder.php` | Updated to use `unit_price` decimal format |

---

## Commands to Run

```bash
# Install doctrine/dbal (required for column changes)
composer require doctrine/dbal

# Run the migration
php artisan migrate

# Re-seed products with new format
php artisan db:seed --class=ProductSeeder

# Re-install templates (new manifest versions)
# Go to /templates → Scan & Install

# Clear view cache
php artisan view:clear
```

---

## How to Add a New Template with Shared Entities

Create your manifest with the `entities` key:

```json
{
    "name": "Purchase Order",
    "slug": "purchase-order",
    "version": "1.0",
    "entities": ["customer", "product"]
}
```

That's it. The customer picker and product picker appear automatically on the form. No PHP changes needed.

## Supported Entities

| Key | Model | Fields |
|---|---|---|
| `customer` | `Customer` | name, company, department, street, city, zip, country, phone, email, vat_number |
| `product` | `Product` | reference, name, description, product_unit, unit_price, page_url |

## Product Format

Price is now stored as decimal float:

```json
{
  "reference": "K0307-01",
  "name": "PRECICE dCK Phosphorylation Assay Kit",
  "product_unit": "(1 plate, 96 assays)",
  "unit_price": 530.00
}
```
---
---

# V6