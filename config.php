<?php
// ============================================================
// Document Flow — Configuration
// ============================================================

// --- Database ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'document_flow');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// --- App ---
define('APP_NAME', 'Document Flow');
define('BASE_URL', '/document-flow');   // No trailing slash. Adjust if hosted at root: ''

// --- Company defaults (used in document headers) ---
define('COMPANY', [
    'name'          => 'Novocib',
    'legal_form'    => 'SAS',
    'share_capital' => '10 000',
    'street'        => 'Halle à Marée, Quai Jean Voisin',
    'city'          => 'Boulogne-sur-Mer',
    'zip'           => '62200',
    'country'       => 'France',
    'siren'         => '482 379 377',
    'siret'         => '482 379 377 00012',
    'vat_number'    => 'FR90482379377',
    'phone'         => '+33 3 21 99 00 00',
    'email'         => 'contact@novocib.com',
    'logo'          => BASE_URL . '/img/logo.png',
]);

// --- Document numbering prefixes ---
define('DOC_PREFIX', [
    'quote'               => 'QUO',
    'invoice'             => 'INV',
    'order_confirmation'  => 'ORD',
]);

// --- VAT mention presets ---
define('VAT_MENTIONS', [
    'VAT not applicable – export outside the European Union (Article 259 of the French Tax Code)',
    'Reverse charge – Article 283-2 of the French Tax Code',
    'VAT not applicable – Article 293 B of the French Tax Code',
    'VAT exemption – Article 262 of the French Tax Code',
    'Standard VAT rate 20% applicable',
]);
