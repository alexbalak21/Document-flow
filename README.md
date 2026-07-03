# Document Flow — Setup Guide

## Requirements
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- A web server (Apache / Nginx / `php -S`)

## 1. Database setup

Create the database, then run the SQL files **in this order**:

```sql
CREATE DATABASE document_flow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
mysql -u root -p document_flow < SQL/products.sql
mysql -u root -p document_flow < SQL/products_data.sql
mysql -u root -p document_flow < SQL/schema.sql
```

## 2. Configure

Edit `config.php` if you need to change the database credentials or company details:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'document_flow');
define('DB_USER', 'your_user');
define('DB_PASS', 'your_password');
// BASE_URL is auto-detected from the request path.
// Override only if you need a fixed custom value.
```

Update the `COMPANY` array with your real details.

## 3. Change the default password

The default admin account is `admin@novocib.com` / `changeme123`.

**Change it immediately** by running:

```php
<?php
echo password_hash('your_new_password', PASSWORD_BCRYPT, ['cost' => 12]);
```

Then update the `users` table:

```sql
UPDATE users SET password_hash = '<hash>' WHERE email = 'admin@novocib.com';
```

Or add another user:

```sql
INSERT INTO users (email, password_hash, full_name)
VALUES ('you@company.com', '<hash>', 'Your Name');
```

## 4. Deploy

### Local dev (quick)
```bash
cd /path/to/document-flow
php -S localhost:8080
# Visit http://localhost:8080/login.php
```

### Apache
Place the folder in your `htdocs` / `www`. The `BASE_URL` in `config.php`
must match the subfolder (e.g. `/document-flow`).

### Root domain / Nginx
Set `define('BASE_URL', '');` in `config.php`.

## File structure

```
document-flow/
├── config.php            ← Edit this first
├── db.php                ← PDO connection + helpers
├── auth.php              ← Session guard
├── login.php / logout.php
├── index.php             ← Dashboard
├── clients.php           ← Client list + create/edit modal
├── client_save.php       ← Client POST handler
├── documents.php         ← Document list with filters
├── document_form.php     ← Create / edit document
├── document_save.php     ← Document POST handler
├── document_view.php     ← Printable document (quote or invoice)
├── document_status.php   ← Quick status update
├── products.php          ← Read-only product catalog
├── layout/
│   ├── header.php        ← Navbar + sidebar
│   └── footer.php
├── SQL/
│   ├── products.sql
│   ├── products_data.sql
│   └── schema.sql        ← Run this last
└── img/
    └── logo.png
```

## Workflow

1. **Login** → `login.php`
2. **Add clients** → Clients → New client
3. **Create a document** → New document → pick type (Quote / Invoice / Order confirmation)
4. **Add line items** by searching your product catalog or typing free lines
5. **Save & view** → renders the printable document
6. **Print / Save PDF** → browser print dialog → "Save as PDF"
7. **Update status** → sent / accepted / paid directly from the view page
