# Update — Template Preview with Placeholders

## Task
Add a Preview button on the Templates page that renders the raw template HTML with its CSS applied, but keeps all {{placeholders}} visible and highlighted — so you can see exactly what the template looks like and which placeholders it uses.

---

## What Changed

### Modified Files

| File | What Changed |
|---|---|
| `app/Http/Controllers/TemplateController.php` | Added `preview(DocumentType $template)` — renders `template.html` with CSS injected, highlights all `{{placeholders}}` in yellow, adds a fixed info banner |
| `resources/views/templates/index.blade.php` | Added blue **Preview** button to each template card, opens in a new tab |
| `routes/web.php` | Added `GET /templates/{template}/preview` → `templates.preview` |

---

## Commands to Run

```bash
php artisan view:clear
```

No migrations needed.

---

## How It Works

1. Click **Preview** on any template card — opens in a new tab
2. Controller reads `template.html` + `style.css` from disk
3. CSS is injected into `{{style}}`
4. All remaining `{{placeholders}}` are wrapped in a highlighted span:
   - Yellow background `#fef9c3`
   - Dashed orange border
   - Monospace font
5. Fixed dark toolbar at the top shows template name, version, and a Back button

This lets you visually inspect layout and verify all placeholder names before generating real documents.