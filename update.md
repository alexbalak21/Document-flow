# Proposal / Proposition — Template Packages

## Two packages

| File | Slug | Language | Prefix | Sidebar |
|---|---|---|---|---|
| `proposal.zip` | `proposal` | EN + FR (i18n switcher) | `PROP-` | Sales |
| `proposition.zip` | `proposition` | FR only (standalone) | `PROP-FR-` | Ventes (hidden) |

---

## Install

Go to `/templates` → **Upload Template Package** → upload each ZIP.
Then click **Scan & Install**.

---

## One controller change required

`DocumentController::computeTotals()` currently only runs totals for
`invoice` and `quote` slugs. Add `proposal` and `proposition`:

```php
// app/Http/Controllers/DocumentController.php

private function computeTotals(string $slug, array $data): array
{
    if (in_array($slug, ['invoice', 'quote', 'proposal', 'proposition'])) {
        // ... existing logic unchanged
    }
    return $data;
}
```

Without this change the subtotal / VAT / total fields will be empty on
the rendered document.

---

## Document flow position

```
Proposal  →  Quote  →  Invoice
```

The Proposal is the earliest stage — a lightweight commercial document
sent to gauge interest before committing to a formal quote. It carries
no legal acceptance block and no payment terms footer.

---

## Design choices

| | Proposal | Quote | Invoice |
|---|---|---|---|
| Accent colour | Indigo `#4f46e5` | Teal `#0d9488` | Blue `#1a56db` |
| Acceptance block | ✗ | ✓ | — |
| Payment footer | ✗ | ✓ | ✓ |
| Next Steps block | ✓ | ✗ | ✗ |
| Auto-numbering | `PROP-` | `Q-` | `INV-` |

---

## Fields

| Field | Key | Auto |
|---|---|---|
| Proposal Number | `proposal_number` | ✓ generated on save |
| Proposal Date | `proposal_date` | — |
| Valid Until | `valid_until` | — |
| Estimated Start | `estimated_start` | optional |
| Estimated Duration | `estimated_duration` | optional |
| VAT Rate | `vat_rate` | — |
| Notes | `notes` | optional |

Customer and product fields come from the `entities` resolver
(`customer`, `product`) — same as quote and invoice.

---

## i18n strings (proposal only)

The bilingual `proposal` package switches between EN and FR via the
language switcher on the create form. Key strings:

| Key | EN | FR |
|---|---|---|
| `doc_title` | PROPOSAL | PROPOSITION |
| `bill_to_label` | Proposal For | Proposition pour |
| `next_steps_label` | Next Steps | Prochaines étapes |
| `next_steps_text` | *To proceed, please approve…* | *Pour donner suite…* |

The `proposition` standalone template has all French strings hardcoded
in the HTML — no i18n file needed.
