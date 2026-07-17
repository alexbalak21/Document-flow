# PHPUnit Test Suite — Document Flow App

This implements the test plan discussed earlier in full: Priority 1 (unit),
Priority 2 (models), Priority 3 (feature/HTTP), and Priority 4
(template/data-contract coverage) tests.

## Install

1. Copy the contents of this zip into your Laravel project root, merging:
   - `phpunit.xml` → project root (overwrite if you don't already have one)
   - `database/factories/*.php` → `database/factories/`
   - `tests/**` → `tests/`

2. Make sure your `composer.json` autoloads `database/factories` under
   `autoload-dev` (standard Laravel skeleton already does this).

3. Run:
   ```bash
   composer install
   php artisan test
   ```
   or directly:
   ```bash
   vendor/bin/phpunit
   ```

No `.env.testing` is required — `phpunit.xml` forces SQLite in-memory
(`DB_DATABASE=:memory:`) and fakes mail/cache/queue so the suite runs
standalone and fast.

## What's covered

| File | Covers |
|---|---|
| `tests/Unit/DocumentControllerRenderHtmlTest.php` | The mustache-like template engine: placeholders, conditional blocks, nesting, i18n + fallback, XSS escaping, leftover-tag cleanup |
| `tests/Unit/DocumentControllerComputeTotalsTest.php` | Subtotal/VAT/total math, `no_fx` flag, FX conversion, `fx_delivery_fee`, forced `bank_account`, currency symbol map |
| `tests/Unit/DocumentNumberServiceTest.php` | Sequential per-prefix-per-day numbering |
| `tests/Unit/EntityResolverTest.php` | Create-vs-update-vs-link logic for customer/product entities, blank-field protection |
| `tests/Unit/Models/*.php` | `Document`, `Customer`, `Product`, `DocumentType` accessors and state helpers |
| `tests/Feature/DocumentStoreTest.php` | Full store flow, auto-numbering, quote→invoice conversion, auth gate |
| `tests/Feature/DocumentUpdateTest.php` | Edit/version-bump flow, draft-only guard |
| `tests/Feature/DocumentPreviewTest.php` | Preview never persists a `Document` row |
| `tests/Feature/DocumentConvertStatusTest.php` | Status transitions and quote-conversion guards |
| `tests/Feature/PdfControllerTest.php` | PDF microservice integration via `Http::fake()`, error handling, filename slugging |
| `tests/Feature/CustomerControllerTest.php`, `ProductControllerTest.php` | CRUD validation, JSON vs redirect responses, auth gate |
| `tests/Feature/ImportExportControllerTest.php` | Model/export/import JSON endpoints for documents, customers, products |
| `tests/Feature/TemplateControllerTest.php` | Toggle/rescan/destroy, malformed-package upload rejection |
| `tests/Feature/AuthTest.php` | Login/logout, guest redirects across every protected route |
| `tests/Feature/TemplateDataCoverageTest.php` | **Every installed template**, checked end-to-end: every `form.json` field must appear in the rendered output |
| `tests/Feature/DocumentPreviewDataCoverageTest.php` | Direct regression lock on the original delivery_fee/hs_code bug, through the real HTTP preview route |

## Notes / things to double check on your end

- **`\Str::slug()` in `ImportExportController::customerExport()` /
  `productExport()`** — these call the *global* `\Str` class, not
  `Illuminate\Support\Str`. Laravel 11+'s streamlined skeleton no longer
  registers class aliases by default, so unless your `bootstrap/app.php`
  explicitly re-enables them, this will throw `Class "Str" not found` at
  runtime. `ImportExportControllerTest::test_customer_export_returns_entity_array`
  and `test_product_export_returns_entity_array` will surface this
  immediately if so — worth running those first.

- **`DocumentType` factory** sets dummy `template_path`/`config_path`
  values. Those DB columns are `NOT NULL` but are never actually read —
  the model always derives the real path from `slug` + `manifest.json` on
  disk (see `DocumentTypeTest`, which locks in that behavior explicitly).

- **CSRF** is disabled per-test via
  `withoutMiddleware(VerifyCsrfToken::class)` in every Feature test that
  POSTs/PUTs, matching how Laravel feature tests normally handle this.

- **Concurrency for `DocumentNumberService`** (two requests racing for the
  same prefix+day) isn't covered here — SQLite in-memory + PHPUnit's
  single process can't meaningfully simulate that race. The code uses
  `lockForUpdate()`, which is the right primitive; if you want confidence
  here, it's best tested manually against a real MySQL/Postgres instance
  or with a dedicated concurrency/integration test outside PHPUnit.

- **`TemplateControllerTest::test_rescan_installs_real_templates_found_on_disk`**
  actually scans your real `storage/app/templates/*` folder. That's
  intentional — it's the same folder your production `rescan` route reads
  — but it does mean this one test is coupled to what templates are
  physically present when you run the suite.
