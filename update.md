# Update — Bank Details, New Templates, Delivery & Footer

## Tasks
1. Bank config with two accounts (FR/International)
2. Updated invoice: bank details block, "Thank you" footer, delivery line, payment terms
3. Updated quote: delivery line
4. Three new template packages: Delivery Note, TSCA Statement, USDA Statement

---

## What Changed

### New Files

| File | Description |
|---|---|
| `config/bank.php` | Two bank accounts: `fr` (EUR) and `int` (international wire). Injected as `{{bank_*}}` placeholders in all templates |
| `storage/app/templates/delivery-note/` | Full Delivery Note package |
| `storage/app/templates/tsca-statement/` | TSCA Statement package for US shipments |
| `storage/app/templates/usda-statement/` | USDA Statement package for biological material |

### Modified Files

| File | What Changed |
|---|---|
| `app/Http/Controllers/DocumentController.php` | `renderHtml()` injects `bank_*` placeholders from `config/bank.php`. `computeTotals()` extended to include `delivery-note` slug |
| `storage/app/templates/invoice/template.html` | Bank details block, payment terms block, HS code mention, "Thank you for your business!" footer, delivery row |
| `storage/app/templates/invoice/form.json` | Added: `payment_terms`, `bank_account` (select: int/fr), `delivery_method`, `delivery_fee`, `hs_code`, `tracking_number` |
| `storage/app/templates/invoice/style.css` | Added: `.bank-block`, `.footer-thankyou`, `.delivery-row`, `.hs-mention`, `.payment-terms-block` |
| `storage/app/templates/invoice/manifest.json` | Bumped to v1.4 |
| `storage/app/templates/quote/template.html` | Added optional delivery row |
| `storage/app/templates/quote/form.json` | Added Delivery section: `delivery_method`, `delivery_fee`, `hs_code` |
| `storage/app/templates/quote/style.css` | Added `.delivery-row` style |
| `storage/app/templates/quote/manifest.json` | Bumped to v1.3 |

---

## Commands to Run

```bash
php artisan config:clear
php artisan view:clear
```

Then go to `/templates` → **Rescan & Update All** to pick up updated invoice/quote templates.

Then upload the three new template ZIPs (or just click **Scan & Install** since the folders are already on disk).

---

## Bank Placeholders Available in Templates

```
{{bank_label}}
{{bank_beneficiary}}
{{bank_name}}
{{bank_bic}}
{{bank_iban}}
{{bank_code}}
{{bank_branch_code}}
{{bank_account_number}}
{{bank_rib_key}}
```

The `bank_account` field on the invoice form lets the user choose `int` or `fr` per document.

---

## New Template: Delivery Note

- Slug: `delivery-note` — Sidebar group: **Shipping**
- Fields: delivery number, date, invoice ref, purchase order, customer ID, delivery method, tracking, HS code, ATTN
- Shows: company header, ship-to block, items table (no prices), HS code mention

## New Template: TSCA Statement

- Slug: `tsca-statement` — Sidebar group: **Compliance**
- Fields: date, signatory name/title/email, invoice reference
- Shows: checkbox block (ARE NOT SUBJECT to TSCA pre-checked), signature lines

## New Template: USDA Statement

- Slug: `usda-statement` — Sidebar group: **Compliance**
- Fields: date, city, USDA guideline number, signatory name/title/phone, product details
- Shows: declaration list, location/date, signatory block
