<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_login();

$db     = db();
$edit_id = (int)($_POST['id'] ?? 0);

// Validate
$type      = $_POST['type']       ?? 'quote';
$client_id = (int)($_POST['client_id'] ?? 0);
$issue_date = $_POST['issue_date'] ?? date('Y-m-d');

if (!$client_id || !in_array($type, ['quote','invoice','order_confirmation'])) {
    header('Location: ' . BASE_URL . '/document_form.php?error=missing_fields');
    exit;
}

// VAT mention: prefer custom input if set
$vat_mention = trim($_POST['vat_mention_custom'] ?? '');
if ($vat_mention === '') $vat_mention = trim($_POST['vat_mention'] ?? '');

$doc_data = [
    'type'             => $type,
    'status'           => $_POST['status']         ?? 'draft',
    'client_id'        => $client_id,
    'issue_date'       => $issue_date,
    'valid_until'      => $_POST['valid_until']     ?: null,
    'service_date'     => $_POST['service_date']    ?: null,
    'due_date'         => $_POST['due_date']        ?: null,
    'po_reference'     => $_POST['po_reference']    ?: null,
    'payment_method'   => $_POST['payment_method']  ?? 'Bank Transfer (Wire)',
    'currency'         => $_POST['currency']        ?? 'EUR',
    'currency_symbol'  => $_POST['currency_symbol'] ?? '€',
    'tax_rate'         => (float)($_POST['tax_rate'] ?? 0),
    'vat_mention'      => $vat_mention              ?: null,
    'notes'            => $_POST['notes']           ?: null,
    'created_by'       => $_SESSION['user_id'],
];

$lines_raw = $_POST['lines'] ?? [];
$lines = [];
foreach ($lines_raw as $l) {
    $desc = trim($l['description'] ?? '');
    if ($desc === '') continue;
    $lines[] = [
        'product_id' => ($l['product_id'] ?? '') !== '' ? (int)$l['product_id'] : null,
        'reference'  => trim($l['reference']  ?? '') ?: null,
        'description'=> $desc,
        'qty'        => (float)($l['qty']        ?? 1),
        'unit_price' => (float)($l['unit_price'] ?? 0),
    ];
}

$db->beginTransaction();
try {
    if ($edit_id > 0) {
        // Update document
        $set  = implode(', ', array_map(fn($k) => "$k = ?", array_keys($doc_data)));
        $vals = array_values($doc_data);
        $vals[] = $edit_id;
        $db->prepare("UPDATE documents SET $set WHERE id = ?")->execute($vals);
        // Delete old lines
        $db->prepare("DELETE FROM document_lines WHERE document_id = ?")->execute([$edit_id]);
        $doc_id = $edit_id;
    } else {
        // Generate doc number
        $doc_data['number'] = next_doc_number($type);
        $cols = implode(', ', array_keys($doc_data));
        $ph   = implode(', ', array_fill(0, count($doc_data), '?'));
        $db->prepare("INSERT INTO documents ($cols) VALUES ($ph)")->execute(array_values($doc_data));
        $doc_id = (int)$db->lastInsertId();
    }

    // Insert lines
    $line_stmt = $db->prepare(
        "INSERT INTO document_lines (document_id, sort_order, product_id, reference, description, qty, unit_price)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    foreach ($lines as $i => $l) {
        $line_stmt->execute([
            $doc_id, $i,
            $l['product_id'], $l['reference'], $l['description'],
            $l['qty'], $l['unit_price'],
        ]);
    }

    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    die('Error saving document: ' . htmlspecialchars($e->getMessage()));
}

// Redirect
if (!empty($_POST['save_and_view'])) {
    header('Location: ' . BASE_URL . '/document_view.php?id=' . $doc_id);
} else {
    header('Location: ' . BASE_URL . '/document_form.php?id=' . $doc_id . '&saved=1');
}
exit;
