# Update Plan — Three Features

## Feature 1 — Name / Unit column fix

### Problem
The items table header says "Description" and the unit column shows the hardcoded
string "pcs" instead of `product_unit` from the database.

### Fix: template.html only
- Rename the first column header from "Description" → "Name / Unit"
- Remove the separate "Unit" column (`{{i18n_unit_col}}`)
- In the `<td>`, the product unit is already rendered as `.item-unit` under the
  product name via `{{#product_unit}}<span class="item-unit">{{product_unit}}</span>{{/product_unit}}`
  — but the Qty column currently shows hardcoded "pcs". Replace that with
  `{{product_unit}}` or remove the separate unit cell entirely.

**Files changed:**
| File | Change |
|---|---|
| `storage/app/templates/invoice/template.html` | Rename col, remove hardcoded "pcs", use `product_unit` |
| `storage/app/templates/invoice/i18n.json` | Update `description_col` → "Name / Unit", remove `unit_col` |

No PHP changes needed.

---

## Feature 2 — Optional sections in form.json

### Concept
A section in `form.json` can be marked `"optional": true`. On the create/edit
form it renders as a collapsed card with a checkbox toggle. When unchecked, all
its fields are excluded from the submitted data (inputs are disabled so they
don't POST). When checked, the card expands and fields are submitted normally.

### form.json schema change
```json
{
    "section": "Shipping",
    "optional": true,
    "icon": "bi-truck",
    "fields": [ ... ]
}
```

### Implementation

**`form-section.blade.php`** — detect `$section['optional']`, render a toggle
checkbox in the card header. Wrap the card body in a collapsible div. Add a
small JS snippet that enables/disables all inputs inside when toggled.
On prefill (edit mode): if any field in the section has a saved value, start
the section expanded and checked.

**`invoice/form.json`** — mark the existing "Delivery" section as
`"optional": true` with icon `"bi-truck"`. It already has the right fields
(`delivery_method`, `delivery_fee`, `hs_code`, `tracking_number`).

**`DocumentController::computeTotals()`** — already handles `delivery_fee`
correctly (it's added to total). No change needed there.

**Files changed:**
| File | Change |
|---|---|
| `resources/views/components/document/form-section.blade.php` | Optional section toggle UI |
| `storage/app/templates/invoice/form.json` | Add `"optional": true` to Delivery section |

---

## Feature 3 — Foreign currency section

### Concept
An optional "Foreign Currency" section on the form lets the user pick a
currency code and enter the conversion rate. The controller computes converted
amounts. The template renders a dual-currency items table and totals block.

### form.json — new optional section
```json
{
    "section": "Foreign Currency",
    "optional": true,
    "icon": "bi-currency-exchange",
    "fields": [
        {
            "type": "select",
            "name": "fx_currency",
            "label": "Currency",
            "options": ["USD", "GBP", "INR", "JPY", "CHF", "CAD", "AUD", "CNY"]
        },
        {
            "type": "number",
            "name": "fx_rate",
            "label": "Conversion Rate (1 EUR = ?)",
            "placeholder": "e.g. 108.89"
        }
    ]
}
```

The `select` field type needs to be added to `form-section.blade.php`
(it's already used for `bank_account` but may not be rendered).

### Controller — `computeTotals()`
When `fx_currency` and `fx_rate` are present in `$data`:
```php
$rate = (float) $data['fx_rate'];
$data['fx_subtotal']  = number_format($subtotal * $rate, 2, '.', '');
$data['fx_vat']       = number_format($vatAmount * $rate, 2, '.', '');
$data['fx_total']     = number_format($total     * $rate, 2, '.', '');
$data['fx_symbol']    = $this->currencySymbol($data['fx_currency']);
// Late payment flat fee conversion
$lateFee = config('company')['late_payment_flat_fee'] ?? 0; // read from company.json
$data['fx_late_fee']  = number_format($lateFee * $rate, 2, '.', '');
```

Bank account: when `fx_currency` is set and is not EUR, automatically switch
to the `int` bank account (already in `bank.php`) regardless of the `bank_account`
field selection.

### Template — invoice/template.html
Wrap the items table and totals in two variants using Mustache conditionals:

**Normal mode** (no `fx_currency`): current single-currency table — unchanged.

**FX mode** (`{{#fx_currency}}`): dual-column table:
- Headers: Name/Unit | Reference | Qty | EUR Unit Price | EUR Amount | {FX} Unit Price | {FX} Amount
- Totals block: two columns (EUR | FX)
- Conversion rate note below totals
- Late payment fee shown in both EUR and FX

### Currency symbols helper
Add a private `currencySymbol(string $code): string` method in
`DocumentController` mapping common codes to symbols:
`USD→$`, `GBP→£`, `INR→₹`, `JPY→¥`, `CHF→CHF`, `CAD→CA$`, `AUD→A$`, `CNY→¥`.

### Files changed
| File | Change |
|---|---|
| `app/Http/Controllers/DocumentController.php` | `computeTotals()` FX logic + `currencySymbol()` helper |
| `resources/views/components/document/form-section.blade.php` | Add `select` field type rendering |
| `storage/app/templates/invoice/form.json` | Add Foreign Currency optional section |
| `storage/app/templates/invoice/template.html` | Dual-currency table and totals |
| `storage/app/templates/invoice/i18n.json` | FX column labels |

---

## Execution order

```
1. Feature 1  →  template.html + i18n.json  (safe, no PHP)
2. Feature 2  →  form-section.blade.php + form.json  (UI only)
3. Feature 3  →  DocumentController + form.json + template.html + i18n.json
```

No migrations needed for any of the three features.
All changes are backwards-compatible — existing saved documents are unaffected
because the FX and optional fields simply resolve to empty strings in the
Mustache renderer when absent.