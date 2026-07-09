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
