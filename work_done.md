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


# Update — Company Config & Settings

## Task
Add company information (name, address, legal identifiers, accounting defaults, legal mentions) to the platform via a config file. Inject company data automatically into all document templates. Add a Company Settings page to edit the config from the UI.

---

## What Changed

### New Files

| File | Description |
|---|---|
| `config/company.php` | All company fields, readable via `config('company.name')` etc. Values pulled from `.env` so they can be overridden per environment |
| `app/Http/Controllers/CompanySettingsController.php` | Shows and saves the settings form — writes values to `.env` directly |
| `resources/views/settings/company.blade.php` | Settings form with sections: Identity, Address, Identifiers, Contact, Accounting, Legal |
| `storage/app/templates/invoice/style.css` | Invoice now has its own CSS (was missing) |

### Modified Files

| File | What Changed |
|---|---|
| `app/Http/Controllers/DocumentController.php` | `renderHtml()` now injects all `company_*` placeholders automatically from `config('company')` before rendering |
| `storage/app/templates/invoice/template.html` | Company block now uses `{{company_name}}`, `{{company_logo}}`, `{{company_street}}`, `{{company_siret}}`, `{{company_vat_number}}`, `{{company_currency_symbol}}`, `{{company_vat_mention}}`, `{{company_terms_text}}` etc. |
| `storage/app/templates/quote/template.html` | Same as invoice |
| `storage/app/templates/quote/style.css` | Added `.brand-logo`, `.company-address`, `.company-legal`, `.vat-mention`, `.doc-footer` |
| `routes/web.php` | Added `GET/PUT /settings/company` routes |
| `resources/views/layouts/auth.blade.php` | Added **Company Settings** link in sidebar under Management |

---

## Commands to Run

```bash
php artisan config:clear
php artisan view:clear
```

No migrations needed.

---

## How It Works

1. `config/company.php` reads values from `.env` with fallbacks
2. Every time a document is rendered, `renderHtml()` reads `config('company')` and maps all fields to `company_*` placeholders
3. Templates use `{{company_name}}`, `{{company_siret}}` etc. — no PHP code needed in templates
4. The Settings page at `/settings/company` saves changes directly to `.env` and clears the config cache

## Available Company Placeholders in Templates

```
{{company_name}}
{{company_logo}}
{{company_legal_form}}
{{company_share_capital}}
{{company_street}}
{{company_city}}
{{company_zip}}
{{company_country}}
{{company_siren}}
{{company_siret}}
{{company_vat_number}}
{{company_eori}}
{{company_email}}
{{company_website}}
{{company_currency}}
{{company_currency_symbol}}
{{company_vat_mention}}
{{company_terms_text}}
{{company_late_payment_text}}
{{company_late_payment_fee_text}}
```

---
---

# V7

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

---
---

# V8

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

---
---

# V9

# Update — Company Logo in Database

## Task
Move company logo out of `.env` (where it was a massive base64 string causing Windows env block overflow) into a dedicated `company_assets` database table. Upload via file input on the Company Settings page.

Also move long text fields (VAT mention, payment terms, etc.) out of `.env` into a JSON file in `storage/app/company_extra.json` to prevent the Windows env block size error.

---

## What Changed

### New Files

| File | Description |
|---|---|
| `database/migrations/2026_07_10_000002_create_company_assets_table.php` | Creates `company_assets` table with `key`, `mime_type`, `filename`, `data_base64` columns |
| `app/Models/CompanyAsset.php` | Model with `logo()`, `storeLogo()`, `deleteLogo()` helpers and `data_uri` accessor |

### Modified Files

| File | What Changed |
|---|---|
| `app/Http/Controllers/CompanySettingsController.php` | Handles file upload via `CompanyAsset::storeLogo()`. Long text fields saved to `storage/app/company_extra.json` instead of `.env`. Removes `COMPANY_LOGO` from `.env` on save |
| `app/Http/Controllers/DocumentController.php` | `renderHtml()` loads logo from `CompanyAsset::logo()->data_uri` instead of config. Long text fields loaded from `company_extra.json` |
| `resources/views/settings/company.blade.php` | Logo field replaced with file upload input + current logo preview + remove checkbox |

---

## Commands to Run

```bash
php artisan migrate
php artisan view:clear

# Remove the old COMPANY_LOGO line from your .env manually
# It's a huge base64 string — just delete that line
```

Then go to `/settings/company` and upload your logo PNG via the file input.

---

## How It Works

- Logo is stored as base64 in `company_assets` table under `key = 'logo'`
- `CompanyAsset::logo()->data_uri` returns `data:image/png;base64,...` ready to embed in HTML
- The template `{{company_logo}}` gets the full data URI — no external file dependencies
- Long text fields (VAT mention, payment terms) saved to `storage/app/company_extra.json` — no size limits
- `.env` only stores short string values — no more Windows env block overflow

---
---

# V10

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

---
---

# V11

# Update — JSON Import / Export & Products Page

## Tasks
1. Download blank JSON model for documents, customers, products
2. Import JSON (file upload or paste) to auto-fill forms
3. Export saved documents, customers, products as JSON
4. Full Products management page (was missing)

---

## What Changed

### New Files

| File | Description |
|---|---|
| `app/Http/Controllers/ImportExportController.php` | All import/export logic — model download, JSON parsing, field mapping for documents, customers, products |
| `app/Http/Controllers/ProductController.php` | Products CRUD — index, store, edit, update |
| `resources/views/products/index.blade.php` | Products list with New, Import JSON, Export JSON buttons |
| `resources/views/products/_form.blade.php` | Shared product form partial |
| `resources/views/products/edit.blade.php` | Edit product page |

### Modified Files

| File | What Changed |
|---|---|
| `resources/views/documents/create.blade.php` | Added collapsible Import/Export panel at top — download model, upload file, paste JSON |
| `resources/views/documents/history.blade.php` | Added Export JSON button per document row |
| `resources/views/documents/viewer.blade.php` | Added Export JSON button in toolbar |
| `resources/views/customers/index.blade.php` | Added JSON Model download, Import JSON modal, Export button per customer row |
| `resources/views/layouts/auth.blade.php` | Added Products link in sidebar |
| `routes/web.php` | Added all import/export routes + product CRUD routes |

---

## Commands to Run

```bash
php artisan view:clear
```

No migrations needed.

---

## How It Works

### Download blank JSON model
- Click **JSON Model** on `/templates`, `/customers`, `/products`, or on the document create form
- Downloads a pre-structured JSON with all field names and empty values
- Fill it in any text editor and import it back

### Import JSON to fill a form
On the document create form — click **Import / Export JSON** to expand the panel:
- **Upload file** — select a `.json` file → click "Fill form from file"
- **Paste text** — paste raw JSON → click "Fill form from text"
- Fields are matched by name and filled instantly — no page reload

For customers and products — click **Import JSON** button → upload or paste → saves directly to DB.

### Export JSON
- Document viewer toolbar → **↓ Export JSON**
- History page → download icon per row
- Customers list → download icon per customer
- Products list → download icon per product

### JSON format for documents

```json
{
    "_template": "quote",
    "customer": {
        "name": "Dr. Hana Li",
        "company": "GCCM",
        "email": "hana.li@gccm.cn"
    },
    "product": {
        "reference": "K0307-01",
        "name": "PRECICE dCK Kit",
        "unit_price": 530.00,
        "quantity": 1
    },
    "quote_number": "Q-2026-001",
    "quote_date": "2026-07-11",
    "vat_rate": 0
}
```

---
---

