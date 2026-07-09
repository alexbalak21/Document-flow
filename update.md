# Update — Separate Guest & App Layouts

## Task
The login page was showing the sidebar. Split layouts so public/guest pages are clean and the sidebar only appears for authenticated pages.

---

## What Changed

### New Files

| File | Description |
|---|---|
| `resources/views/layouts/guest.blade.php` | Clean layout with no sidebar — used for login and any future guest pages |

### Modified Files

| File | What Changed |
|---|---|
| `resources/views/auth/login.blade.php` | Changed `@extends('layouts.auth')` to `@extends('layouts.guest')` |

---

## Commands to Run

```bash
php artisan view:clear
```

---

## Layout Reference

| Layout | Used for | Has sidebar |
|---|---|---|
| `layouts.guest` | Login page | No |
| `layouts.auth` | All authenticated pages | Yes |
