# Document Flow — Template Package Authoring Guide

## Overview

A template package is a **ZIP file** containing everything needed to render and manage one document type (invoice, quote, delivery note, etc.). When you upload a ZIP through the admin UI, the app extracts it, validates it, copies the files to `storage/app/templates/<slug>/`, and registers the template in the database.

---

## Package Structure

```
my-template.zip
└── my-template/            ← folder name doesn't matter; the app finds manifest.json
    ├── manifest.json       ← required — describes the template
    ├── template.html       ← required — HTML with {{placeholders}}
    ├── style.css           ← required — A4 page layout + print styles
    ├── form.json           ← required — form fields shown in the editor
    └── i18n.json           ← optional — translated strings
```

The ZIP can place files at the root or inside one subfolder — the installer searches recursively for `manifest.json`.

---

## 1. `manifest.json`

Controls how the template is registered in the database and displayed in the sidebar.

```json
{
    "name":               "Invoice",
    "slug":               "invoice",
    "version":            "1.0",
    "author":             "Your Name",
    "description":        "Standard invoice with customer details and one product line",
    "template":           "template.html",
    "style":              "style.css",
    "form":               "form.json",
    "i18n":               "i18n.json",
    "languages":          ["en", "fr"],
    "entities":           ["customer", "product"],
    "icon":               "bi-receipt",
    "sidebar_label":      "Invoice",
    "sidebar_group":      "Sales",
    "sidebar_group_order": 1,
    "sidebar_order":      3,
    "prefix":             "INV-",
    "accent_color":       "#1a56db",
    "accent_color_light": "#f8faff"
}
```

| Field | Required | Notes |
|---|---|---|
| `name` | ✅ | Display name in the UI |
| `slug` | ✅ | Unique kebab-case ID — must match the folder name in `storage/app/templates/` |
| `version` | — | Displayed in template preview banner |
| `template` | ✅ | Filename of the HTML template |
| `form` | ✅ | Filename of the form config |
| `style` | — | CSS filename (defaults are inlined via `{{style}}`) |
| `i18n` | — | JSON file with translated strings |
| `languages` | — | Array of language codes available in `i18n.json` |
| `entities` | — | `"customer"` and/or `"product"` — enables the customer/product picker in the form |
| `icon` | — | Bootstrap Icons class (e.g. `bi-receipt`, `bi-truck`) |
| `sidebar_group` | — | Group heading in sidebar (e.g. `"Sales"`, `"Shipping"`) |
| `sidebar_group_order` | — | Integer — sort order of the group |
| `sidebar_order` | — | Integer — sort order within the group |
| `prefix` | — | Document number prefix (e.g. `"INV-"`, `"Q-"`) |
| `accent_color` | — | Hex color injected as `--accent` CSS variable |
| `accent_color_light` | — | Hex color injected as `--accent-light` CSS variable |

---

## 2. `template.html`

A standard HTML file. The engine injects CSS, replaces `{{placeholders}}`, and evaluates `{{#conditional}}` blocks before generating the PDF.

### 2.1 CSS injection

Place `{{style}}` inside a `<style>` tag in `<head>`. The app injects `style.css` (with accent color overrides prepended) here:

```html
<head>
    <meta charset="UTF-8">
    <title>{{i18n_doc_title}} {{invoice_number}}</title>
    <style>{{style}}</style>
</head>
```

### 2.2 Simple placeholders

```html
<p>Invoice number: {{invoice_number}}</p>
<p>Customer: {{customer_name}}</p>
```

Unused placeholders are removed (replaced with empty string) after rendering.

### 2.3 Conditional blocks

Show a block only when a variable has a non-empty value:

```html
{{#customer_vat_number}}
<div class="customer-vat">VAT No.: {{customer_vat_number}}</div>
{{/customer_vat_number}}
```

This is a simple truthy check — the block is shown when the value is a non-empty string, hidden otherwise. Blocks can be nested (up to 5 levels deep).

### 2.4 Available variables

#### Company (injected automatically from `storage/app/company.json` and DB)

| Variable | Description |
|---|---|
| `{{company_name}}` | Company name |
| `{{company_logo}}` | Logo as a base64 data URI (use as `<img src="{{company_logo}}">`) |
| `{{company_legal_form}}` | e.g. `SAS`, `SARL`, `LLC` |
| `{{company_share_capital}}` | Share capital |
| `{{company_street}}` | Street address |
| `{{company_zip}}` | Postal code |
| `{{company_city}}` | City |
| `{{company_country}}` | Country |
| `{{company_siren}}` | SIREN number |
| `{{company_siret}}` | SIRET number |
| `{{company_vat_number}}` | VAT number |
| `{{company_eori}}` | EORI number |
| `{{company_email}}` | Contact email |
| `{{company_website}}` | Website |
| `{{company_currency}}` | e.g. `EUR`, `USD` |
| `{{company_currency_symbol}}` | e.g. `€`, `$` |
| `{{company_vat_mention}}` | Full VAT exemption text |
| `{{company_terms_text}}` | Terms & conditions body text |
| `{{company_late_payment_text}}` | Late payment clause label |
| `{{company_late_payment_fee_text}}` | Late payment fee amount |

#### Bank (injected from `config/bank.php`, account selected by the `bank_account` form field)

| Variable | Description |
|---|---|
| `{{bank_label}}` | Account label |
| `{{bank_beneficiary}}` | Beneficiary name |
| `{{bank_name}}` | Bank name |
| `{{bank_address}}` | Bank address |
| `{{bank_iban}}` | IBAN |
| `{{bank_bic}}` | BIC / SWIFT |
| `{{bank_code}}` | Bank code |
| `{{bank_branch_code}}` | Branch code |
| `{{bank_account_number}}` | Account number |
| `{{bank_rib_key}}` | RIB key |

Wrap bank details in `{{#bank_iban}}…{{/bank_iban}}` to hide the block when no account is selected.

#### Customer (injected from the customer picker)

| Variable | Description |
|---|---|
| `{{customer_name}}` | Full name |
| `{{customer_company}}` | Company name |
| `{{customer_department}}` | Department |
| `{{customer_street}}` | Street address |
| `{{customer_zip}}` | Postal code |
| `{{customer_city}}` | City |
| `{{customer_country}}` | Country |
| `{{customer_phone}}` | Phone |
| `{{customer_email}}` | Email |
| `{{customer_vat_number}}` | VAT / GST number |

#### Product (injected from the product picker + computed by the engine)

| Variable | Description |
|---|---|
| `{{product_name}}` | Product name |
| `{{product_reference}}` | Reference / SKU |
| `{{product_unit}}` | Unit description |
| `{{product_description}}` | Long description |
| `{{product_quantity}}` | Quantity |
| `{{product_unit_price}}` | Unit price (formatted) |
| `{{subtotal}}` | Quantity × unit price |
| `{{vat_rate}}` | VAT % |
| `{{vat_amount}}` | Computed VAT amount |
| `{{total}}` | Grand total incl. VAT |
| `{{late_payment_flat_fee}}` | Late fee (from company config) |

#### Foreign currency (computed when `fx_currency` and `fx_rate` are set)

| Variable | Description |
|---|---|
| `{{fx_currency}}` | Currency code (e.g. `USD`, `INR`) |
| `{{fx_rate}}` | Conversion rate (1 EUR = ?) |
| `{{fx_symbol}}` | Currency symbol |
| `{{fx_unit_price}}` | Unit price in FX currency |
| `{{fx_subtotal}}` | Subtotal in FX currency |
| `{{fx_vat}}` | VAT in FX currency |
| `{{fx_total}}` | Grand total in FX currency |
| `{{fx_delivery_fee}}` | Delivery fee in FX currency |
| `{{fx_late_fee}}` | Late fee in FX currency |
| `{{no_fx}}` | Non-empty when **no** FX currency is set — use to show a single-currency table |

Use `{{#fx_currency}}…{{/fx_currency}}` and `{{#no_fx}}…{{/no_fx}}` to toggle between single- and dual-currency table layouts.

#### i18n strings

Any key defined in `i18n.json` is available as `{{i18n_<key>}}`. Example: key `doc_title` → `{{i18n_doc_title}}`.

---

## 3. `style.css`

All templates must use an A4-constrained page so that documents always print or export on a single page.

### Required page block

```css
.page {
    width: 210mm;
    height: 281mm;           /* A4 printable area: 297mm − 2 × 8mm margin */
    margin: 25px auto 10px;
    background: white;
    padding: 8mm;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0, 0, 0, .15);
    position: relative;
}
```

> ⚠️ **Do not use `min-height: 297mm`** — it allows the page to grow beyond A4, which causes the PDF renderer to split content onto a second page.

### Required print block

```css
@page {
    size: A4 portrait;
    margin: 8mm;
}

@media print {
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    html, body { background: white !important; }
    .page {
        width: 100%;
        height: auto;
        margin: 0;
        box-shadow: none;
        padding: 0;
        page-break-after: auto;
    }
    .no-print, .page-toolbar { display: none !important; }
    .header, .bill-to, .totals, .vat-mention, .bank-block, footer, .doc-footer {
        page-break-inside: avoid;
        break-inside: avoid;
    }
    a { color: black; text-decoration: none; }
}
```

### Accent color variables

The app prepends a `:root` override before your CSS at render time, so you can use `--accent` and `--accent-light` freely without hardcoding colors:

```css
:root {
    --accent:       #1a56db;   /* fallback — overridden at render time */
    --accent-light: #f8faff;
}
```

---

## 4. `form.json`

Defines the input fields shown in the document editor. An array of **sections**, each with a list of **fields**.

```json
[
    {
        "section": "Invoice",
        "i18n_section": "form_section_invoice",
        "fields": [
            {
                "type": "text",
                "name": "invoice_number",
                "label": "Invoice Number",
                "required": true,
                "auto": true
            },
            {
                "type": "date",
                "name": "invoice_date",
                "label": "Invoice Date",
                "required": true
            },
            {
                "type": "select",
                "name": "bank_account",
                "label": "Bank Account",
                "required": false,
                "options": ["int", "fr"]
            }
        ]
    },
    {
        "section": "Delivery",
        "optional": true,
        "icon": "bi-truck",
        "fields": [
            {
                "type": "text",
                "name": "tracking_number",
                "label": "Tracking Number",
                "required": false
            }
        ]
    }
]
```

### Section properties

| Property | Description |
|---|---|
| `section` | Section heading label |
| `i18n_section` | i18n key for the section label (optional) |
| `optional` | If `true`, the section can be collapsed in the UI |
| `icon` | Bootstrap Icons class shown next to the section title |

### Field types

| `type` | Renders as | Notes |
|---|---|---|
| `text` | Single-line text input | |
| `number` | Number input | |
| `date` | Date picker | |
| `textarea` | Multi-line text area | |
| `select` | Dropdown | Requires `options` array |

### Field properties

| Property | Description |
|---|---|
| `name` | Variable name — becomes `{{name}}` in the template |
| `label` | Display label in the editor |
| `i18n_label` | i18n key for the label (optional) |
| `required` | If `true`, the form won't submit without this field |
| `auto` | If `true`, the value is auto-generated by the document number service |
| `placeholder` | Hint text shown inside the input |
| `options` | Array of values for `select` type |

---

## 5. `i18n.json` (optional)

Provides translated labels for template content (column headers, section titles, etc.). The user's selected language is passed at render time; the engine falls back to `en` if the requested language isn't present.

```json
{
    "en": {
        "doc_title":       "INVOICE",
        "bill_to_label":   "Bill To",
        "description_col": "Name / Unit",
        "qty_col":         "Qty",
        "unit_price_col":  "Unit Price",
        "amount_col":      "Amount",
        "subtotal_label":  "Subtotal",
        "vat_label":       "VAT",
        "total_label":     "TOTAL DUE"
    },
    "fr": {
        "doc_title":       "FACTURE",
        "bill_to_label":   "Facturer à",
        "description_col": "Nom / Unité",
        "qty_col":         "Qté",
        "unit_price_col":  "Prix unitaire",
        "amount_col":      "Montant",
        "subtotal_label":  "Sous-total",
        "vat_label":       "TVA",
        "total_label":     "TOTAL TTC"
    }
}
```

All keys are available in the template as `{{i18n_<key>}}` (e.g. `{{i18n_doc_title}}`).

---

## 6. Installing a Template

### Via the admin UI (recommended)

1. Zip your template folder: `zip -r my-template.zip my-template/`
2. Go to **Templates → Upload Package**
3. Select your ZIP — the app validates, installs, and registers it immediately

### Via the filesystem

1. Copy your template folder to `storage/app/templates/<slug>/`
2. Go to **Templates → Rescan** to register or update it in the database

### Updating an existing template

Upload a ZIP with the same `slug`. The installer overwrites the files and regenerates all existing document snapshots automatically.

---

## 7. Common Patterns

### Footer that stays at the bottom

Use `flex: 1` on the content area so the footer is always pushed to the bottom of the A4 page:

```html
<div class="content">
    <!-- your content -->
    <div class="spacer"></div>  <!-- fills remaining space -->
</div>
<footer>
    <div class="thanks">Thank you for your business!</div>
</footer>
```

```css
.content { flex: 1; display: flex; flex-direction: column; }
.spacer  { flex: 1; min-height: 5mm; }
footer   { margin-top: auto; border-top: 1px solid #ddd; }
```

### Dual-currency table (EUR + foreign)

```html
{{#no_fx}}
<table class="items">
    <thead><tr>
        <th>Name</th><th>Qty</th><th>Price ({{company_currency}})</th><th>Amount</th>
    </tr></thead>
    <tbody><tr>
        <td>{{product_name}}</td>
        <td>{{product_quantity}}</td>
        <td>{{company_currency_symbol}} {{product_unit_price}}</td>
        <td>{{company_currency_symbol}} {{subtotal}}</td>
    </tr></tbody>
</table>
{{/no_fx}}

{{#fx_currency}}
<table class="items">
    <thead><tr>
        <th>Name</th><th>Qty</th><th>EUR Price</th><th>EUR Amount</th>
        <th>{{fx_currency}} Amount</th>
    </tr></thead>
    <tbody><tr>
        <td>{{product_name}}</td>
        <td>{{product_quantity}}</td>
        <td>€ {{product_unit_price}}</td>
        <td>€ {{subtotal}}</td>
        <td>{{fx_symbol}} {{fx_subtotal}}</td>
    </tr></tbody>
</table>
{{/fx_currency}}
```

### Bank details block

```html
{{#bank_iban}}
<div class="bank-block">
    <div class="bank-title">{{bank_label}}</div>
    <table class="bank-table">
        <tr><td>Beneficiary</td><td><strong>{{bank_beneficiary}}</strong></td></tr>
        <tr><td>Bank</td><td>{{bank_name}}</td></tr>
        <tr><td>IBAN</td><td><strong>{{bank_iban}}</strong></td></tr>
        <tr><td>BIC / SWIFT</td><td><strong>{{bank_bic}}</strong></td></tr>
    </table>
</div>
{{/bank_iban}}
```

---

## 8. Checklist Before Uploading

- [ ] `manifest.json` present with `name`, `slug`, `template`, and `form` filled in
- [ ] `slug` is unique and uses only lowercase letters, numbers, and hyphens
- [ ] `template.html` references `{{style}}` inside a `<style>` tag
- [ ] `style.css` uses `height: 281mm` (not `min-height: 297mm`) on `.page`
- [ ] `style.css` includes `@page { size: A4 portrait; margin: 8mm; }` and `@media print`
- [ ] All `{{#block}}` tags have a matching `{{/block}}` closing tag
- [ ] `form.json` is valid JSON and every `name` matches a `{{placeholder}}` in the template
- [ ] `i18n.json` (if used) is listed in `manifest.json` under `"i18n"`