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

# V12

## Implemented disabling & deleting template modules