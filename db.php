<?php
// ============================================================
// Document Flow — PDO Database Connection (singleton)
// ============================================================
require_once __DIR__ . '/config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Show a clean error rather than leaking credentials
            http_response_code(500);
            die('<h1>Database connection failed</h1><p>Please check your config.php settings.</p>');
        }
    }
    return $pdo;
}

// ---- Helpers ------------------------------------------------

/**
 * Generate the next document number: e.g. INV-2026-0042
 */
function next_doc_number(string $type): string {
    $prefix = DOC_PREFIX[$type] ?? 'DOC';
    $year   = date('Y');
    $row    = db()->query(
        "SELECT MAX(CAST(SUBSTRING_INDEX(number, '-', -1) AS UNSIGNED)) AS last
         FROM documents
         WHERE number LIKE '{$prefix}-{$year}-%'"
    )->fetch();
    $next = ($row['last'] ?? 0) + 1;
    return sprintf('%s-%s-%04d', $prefix, $year, $next);
}

/**
 * Calculate document totals from its lines + tax rate.
 * Returns: subtotal, tax_amount, total (all in float)
 */
function doc_totals(array $lines, float $tax_rate): array {
    $subtotal = 0.0;
    foreach ($lines as $l) {
        $subtotal += (float)$l['qty'] * (float)$l['unit_price'];
    }
    $tax_amount = round($subtotal * $tax_rate / 100, 2);
    return [
        'subtotal'   => $subtotal,
        'tax_amount' => $tax_amount,
        'total'      => $subtotal + $tax_amount,
    ];
}
