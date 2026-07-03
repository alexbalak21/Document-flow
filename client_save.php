<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_login();

$id     = (int)($_POST['id'] ?? 0);
$fields = ['name','company','department','street','zip','city','country','email','phone','vat_number','notes'];
$data   = [];
foreach ($fields as $f) {
    $data[$f] = trim($_POST[$f] ?? '') ?: null;
}

if (empty($data['name'])) {
    header('Location: ' . BASE_URL . '/clients.php?error=Name+is+required');
    exit;
}

$db = db();
if ($id > 0) {
    $set = implode(', ', array_map(fn($f) => "$f = ?", $fields));
    $stmt = $db->prepare("UPDATE clients SET $set WHERE id = ?");
    $stmt->execute([...array_values($data), $id]);
} else {
    $cols = implode(', ', $fields);
    $ph   = implode(', ', array_fill(0, count($fields), '?'));
    $stmt = $db->prepare("INSERT INTO clients ($cols) VALUES ($ph)");
    $stmt->execute(array_values($data));
}

header('Location: ' . BASE_URL . '/clients.php');
exit;
