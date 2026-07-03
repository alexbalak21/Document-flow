-- ============================================================
-- Document Flow — Full Schema
-- Run this AFTER importing products.sql + products_data.sql
-- ============================================================

-- Users (authentication)
CREATE TABLE IF NOT EXISTS users (
  id            INT NOT NULL AUTO_INCREMENT,
  email         VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name     VARCHAR(255) NOT NULL,
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

-- Default admin user (password: changeme123 — change immediately)
INSERT INTO users (email, password_hash, full_name) VALUES
  ('admin@novocib.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin');

-- Clients
CREATE TABLE IF NOT EXISTS clients (
  id            INT NOT NULL AUTO_INCREMENT,
  name          VARCHAR(255) NOT NULL,          -- Contact name
  company       VARCHAR(255) DEFAULT NULL,      -- Company / institution
  department    VARCHAR(255) DEFAULT NULL,
  street        VARCHAR(255) DEFAULT NULL,
  city          VARCHAR(255) DEFAULT NULL,
  zip           VARCHAR(20)  DEFAULT NULL,
  country       VARCHAR(100) DEFAULT NULL,
  email         VARCHAR(255) DEFAULT NULL,
  phone         VARCHAR(50)  DEFAULT NULL,
  vat_number    VARCHAR(100) DEFAULT NULL,      -- GST / EU VAT
  notes         TEXT         DEFAULT NULL,
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

-- Documents (quotes, invoices, order confirmations)
CREATE TABLE IF NOT EXISTS documents (
  id              INT NOT NULL AUTO_INCREMENT,
  number          VARCHAR(50)  NOT NULL UNIQUE,  -- e.g. QUO-2026-0001
  type            ENUM('quote','invoice','order_confirmation') NOT NULL,
  status          ENUM('draft','sent','accepted','rejected','paid','cancelled') NOT NULL DEFAULT 'draft',
  client_id       INT NOT NULL,
  issue_date      DATE NOT NULL,
  valid_until     DATE DEFAULT NULL,             -- Quotes: validity date
  service_date    DATE DEFAULT NULL,             -- Invoices: date of service
  due_date        DATE DEFAULT NULL,             -- Invoices: payment due
  po_reference    VARCHAR(100) DEFAULT NULL,     -- Client PO / quote ref
  payment_method  VARCHAR(100) DEFAULT 'Bank Transfer (Wire)',
  currency        VARCHAR(10)  DEFAULT 'EUR',
  currency_symbol VARCHAR(5)   DEFAULT '€',
  tax_rate        DECIMAL(5,2) DEFAULT 0.00,     -- VAT % (0, 5.5, 10, 20)
  vat_mention     VARCHAR(255) DEFAULT NULL,     -- Legal VAT mention
  late_payment_rate DECIMAL(5,2) DEFAULT 4.50,  -- % (3× ECB rate)
  late_payment_fee  DECIMAL(8,2) DEFAULT 40.00, -- €40 fixed B2B fee
  notes           TEXT DEFAULT NULL,
  created_by      INT DEFAULT NULL,
  created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (client_id)   REFERENCES clients(id) ON DELETE RESTRICT,
  FOREIGN KEY (created_by)  REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Document line items
CREATE TABLE IF NOT EXISTS document_lines (
  id           INT NOT NULL AUTO_INCREMENT,
  document_id  INT NOT NULL,
  sort_order   INT NOT NULL DEFAULT 0,
  product_id   INT DEFAULT NULL,               -- NULL = free-text line
  reference    VARCHAR(80)  DEFAULT NULL,
  description  TEXT NOT NULL,
  qty          DECIMAL(10,3) NOT NULL DEFAULT 1,
  unit_price   DECIMAL(10,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id)  REFERENCES products(ID)  ON DELETE SET NULL
) ENGINE=InnoDB;
