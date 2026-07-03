<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_login();

$id     = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';
$valid  = ['draft','sent','accepted','rejected','paid','cancelled'];

if ($id && in_array($status, $valid)) {
    db()->prepare("UPDATE documents SET status = ? WHERE id = ?")->execute([$status, $id]);
}

header('Location: ' . BASE_URL . '/document_view.php?id=' . $id);
exit;
