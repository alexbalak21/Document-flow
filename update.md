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
