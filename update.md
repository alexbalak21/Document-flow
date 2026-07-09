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
