# Update — PDF Generation with Browsershot + Puppeteer

## Task
Replace "Print to PDF" browser workaround with server-side PDF generation using Browsershot (headless Chromium). Full CSS fidelity — renders exactly as the browser does.

---

## Installation (run these first)

```bash
# 1. Install Puppeteer (headless Chromium)
npm install puppeteer

# 2. Install Laravel Browsershot package
composer require spatie/browsershot

# 3. Linux/VPS only — Chromium system dependencies
# (skip on Windows/Mac — Puppeteer bundles Chromium automatically)
# sudo apt-get install -y chromium-browser libnss3 libatk1.0-0 libx11-xcb1 libxcomposite1 libxdamage1 libxrandr2 libgbm1 libasound2
```

---

## What Changed

### New Files

| File | Description |
|---|---|
| `app/Http/Controllers/PdfController.php` | `download()` streams PDF as file attachment. `preview()` opens PDF inline in browser tab. Both use `html_snapshot` — no re-render needed |

### Modified Files

| File | What Changed |
|---|---|
| `routes/web.php` | Added `GET /pdf/{document}/download` and `GET /pdf/{document}/preview` |
| `resources/views/documents/viewer.blade.php` | Replaced single "Print / Save PDF" button with: **View PDF** (opens in tab) + **Download PDF** (file download) + small printer icon fallback |
| `resources/views/components/document/table-row.blade.php` | Added red PDF download button next to JSON export in history table |

---

## Commands to Run

```bash
# After composer install
php artisan view:clear
```

---

## How It Works

```
User clicks "Download PDF"
    ↓
PdfController::download($document)
    ↓
Browsershot::html($document->html_snapshot)
    ↓
Headless Chromium renders HTML + CSS (all styles embedded in snapshot)
    ↓
Chromium outputs A4 PDF with showBackground() + emulateMedia('print')
    ↓
Laravel streams PDF as file download
```

## Browsershot Options Used

| Option | Why |
|---|---|
| `format('A4')` | A4 page size |
| `margins(0,0,0,0)` | Templates manage their own padding |
| `showBackground()` | Renders CSS backgrounds, borders, colors |
| `emulateMedia('print')` | Uses `@media print` CSS rules |
| `waitUntilNetworkIdle()` | Waits for embedded images/fonts |
| `noSandbox()` | Required on Linux servers |
| `timeout(60)` | 60 second timeout for large documents |

## Troubleshooting

**"Could not find Chrome"** → Run `npm install puppeteer` from the project root.

**Blank PDF / missing styles** → The `html_snapshot` is self-contained (CSS embedded). If blank, regenerate the document snapshot via `/templates` → Regenerate.

**Linux: sandbox error** → `noSandbox()` is already set in `PdfController`. If still failing, add to `.env`: `BROWSERSHOT_CHROME_PATH=/usr/bin/chromium-browser`
