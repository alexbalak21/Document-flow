<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_login();

$db  = db();
$id  = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . BASE_URL . '/documents.php'); exit; }

$stmt = $db->prepare("SELECT d.*, c.name AS c_name, c.company, c.department, c.street AS c_street,
    c.zip AS c_zip, c.city AS c_city, c.country AS c_country,
    c.email AS c_email, c.phone AS c_phone, c.vat_number AS c_vat
    FROM documents d
    JOIN clients c ON c.id = d.client_id
    WHERE d.id = ?");
$stmt->execute([$id]);
$doc = $stmt->fetch();
if (!$doc) { header('Location: ' . BASE_URL . '/documents.php'); exit; }

$lstmt = $db->prepare("SELECT * FROM document_lines WHERE document_id = ? ORDER BY sort_order");
$lstmt->execute([$id]);
$lines = $lstmt->fetchAll();

$totals = doc_totals($lines, (float)$doc['tax_rate']);
$co     = COMPANY;
$sym    = htmlspecialchars($doc['currency_symbol'] ?? '€');

$type_labels = ['quote' => 'QUOTE', 'invoice' => 'INVOICE', 'order_confirmation' => 'ORDER CONFIRMATION'];
$type_label  = $type_labels[$doc['type']] ?? strtoupper($doc['type']);

function fmt_date($d) { return $d ? date('d/m/Y', strtotime($d)) : '—'; }
function money($n, $sym) { return $sym . ' ' . number_format((float)$n, 2, '.', ' '); }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($doc['number']) ?></title>
  <link rel="icon" type="image/png" href="<?= BASE_URL ?>/img/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    body { background: #f4f6f9; }
    .doc-card { max-width: 900px; margin: 0 auto; background: #fff; padding: 3rem; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
    .doc-header { border-bottom: 2px solid #1a56db; padding-bottom: 1.5rem; margin-bottom: 2rem; }
    .doc-type { font-size: 1.6rem; font-weight: 700; color: #1a56db; letter-spacing: .04em; }
    .doc-number { font-size: .95rem; color: #6c757d; }
    .address-block { font-size: .875rem; line-height: 1.6; }
    .table thead th { background: #f8f9fa; font-size: .8rem; text-transform: uppercase; letter-spacing: .05em; }
    .totals-block { max-width: 320px; margin-left: auto; }
    .totals-block td { padding: .25rem .5rem; font-size: .9rem; }
    .legal-block { font-size: .75rem; color: #6c757d; border-top: 1px solid #dee2e6; padding-top: 1rem; margin-top: 2rem; }
    .acceptance-block { border: 1px solid #dee2e6; border-radius: 6px; padding: 1rem; font-size: .8rem; color: #6c757d; }
    .toolbar { max-width: 900px; margin: 0 auto 1rem; }
    @media print {
      body { background: #fff; }
      .toolbar { display: none !important; }
      .doc-card { box-shadow: none; padding: 0; }
    }
    @page { margin: 20mm; }
  </style>
</head>
<body>

<!-- Toolbar (hidden on print) -->
<div class="container py-3 toolbar no-print">
  <div class="d-flex gap-2 align-items-center">
    <a href="<?= BASE_URL ?>/documents.php" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Back
    </a>
    <a href="<?= BASE_URL ?>/document_form.php?id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-pencil me-1"></i>Edit
    </a>
    <button onclick="window.print()" class="btn btn-sm btn-primary">
      <i class="bi bi-printer me-1"></i>Print / Save PDF
    </button>
    <!-- Quick status update -->
    <form method="post" action="<?= BASE_URL ?>/document_status.php" class="ms-auto d-flex gap-2 align-items-center">
      <input type="hidden" name="id" value="<?= $doc['id'] ?>">
      <select name="status" class="form-select form-select-sm" style="width:auto">
        <?php foreach (['draft','sent','accepted','rejected','paid','cancelled'] as $s): ?>
        <option value="<?= $s ?>" <?= $doc['status']===$s ? 'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-sm btn-outline-success" type="submit">Update status</button>
    </form>
  </div>
</div>

<div class="container pb-5">
<div class="doc-card">

  <!-- Header -->
  <div class="doc-header d-flex justify-content-between align-items-start">
    <div>
      <img src="<?= BASE_URL ?>/img/logo.png" alt="<?= htmlspecialchars($co['name']) ?>" style="max-height:56px; margin-bottom:.75rem">
      <div class="address-block">
        <strong><?= htmlspecialchars($co['name']) ?></strong> <?= htmlspecialchars($co['legal_form']) ?><br>
        <?= htmlspecialchars($co['street']) ?><br>
        <?= htmlspecialchars($co['zip']) ?> <?= htmlspecialchars($co['city']) ?>, <?= htmlspecialchars($co['country']) ?><br>
        SIRET : <?= htmlspecialchars($co['siret']) ?> — VAT : <?= htmlspecialchars($co['vat_number']) ?><br>
        Share capital : <?= htmlspecialchars($co['share_capital']) ?> €<br>
        <?= htmlspecialchars($co['email']) ?> · <?= htmlspecialchars($co['phone']) ?>
      </div>
    </div>
    <div class="text-end">
      <div class="doc-type"><?= $type_label ?></div>
      <div class="doc-number mt-1"><?= htmlspecialchars($doc['number']) ?></div>
      <table class="mt-3" style="font-size:.85rem;margin-left:auto">
        <tr><td class="text-muted pe-3">Issue date</td><td class="fw-medium"><?= fmt_date($doc['issue_date']) ?></td></tr>
        <?php if ($doc['type'] === 'quote' && $doc['valid_until']): ?>
        <tr><td class="text-muted pe-3">Valid until</td><td class="fw-medium"><?= fmt_date($doc['valid_until']) ?></td></tr>
        <?php endif; ?>
        <?php if ($doc['service_date']): ?>
        <tr><td class="text-muted pe-3">Service date</td><td class="fw-medium"><?= fmt_date($doc['service_date']) ?></td></tr>
        <?php endif; ?>
        <?php if ($doc['due_date']): ?>
        <tr><td class="text-muted pe-3">Due date</td><td class="fw-medium text-danger"><?= fmt_date($doc['due_date']) ?></td></tr>
        <?php endif; ?>
        <?php if ($doc['po_reference']): ?>
        <tr><td class="text-muted pe-3">PO ref.</td><td class="fw-medium"><?= htmlspecialchars($doc['po_reference']) ?></td></tr>
        <?php endif; ?>
      </table>
    </div>
  </div>

  <!-- Bill to -->
  <div class="row mb-4">
    <div class="col-md-5">
      <div class="text-uppercase text-muted small fw-semibold mb-2" style="letter-spacing:.06em">Bill to</div>
      <div class="address-block">
        <?php if ($doc['company']): ?><strong><?= htmlspecialchars($doc['company']) ?></strong><br><?php endif; ?>
        <?php if ($doc['department']): ?><?= htmlspecialchars($doc['department']) ?><br><?php endif; ?>
        <?= htmlspecialchars($doc['c_name']) ?><br>
        <?php if ($doc['c_street']): ?><?= htmlspecialchars($doc['c_street']) ?><br><?php endif; ?>
        <?= htmlspecialchars(trim($doc['c_zip'] . ' ' . $doc['c_city'])) ?><?php if ($doc['c_country']): ?>, <?= htmlspecialchars($doc['c_country']) ?><?php endif; ?><br>
        <?php if ($doc['c_email']): ?><?= htmlspecialchars($doc['c_email']) ?><br><?php endif; ?>
        <?php if ($doc['c_vat']): ?><span class="text-muted">VAT: <?= htmlspecialchars($doc['c_vat']) ?></span><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Line items table -->
  <table class="table table-bordered mb-0">
    <thead>
      <tr>
        <th style="width:5%">#</th>
        <th style="width:50%">Description</th>
        <th style="width:10%" class="text-end">Qty</th>
        <th style="width:15%" class="text-end">Unit price</th>
        <th style="width:20%" class="text-end">Amount</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($lines as $i => $l): ?>
      <tr>
        <td class="text-muted small"><?= $i + 1 ?></td>
        <td>
          <?= nl2br(htmlspecialchars($l['description'])) ?>
          <?php if ($l['reference']): ?><br><span class="text-muted small">Ref. <?= htmlspecialchars($l['reference']) ?></span><?php endif; ?>
        </td>
        <td class="text-end"><?= number_format((float)$l['qty'], $l['qty'] == (int)$l['qty'] ? 0 : 2) ?></td>
        <td class="text-end"><?= money($l['unit_price'], $sym) ?></td>
        <td class="text-end"><?= money((float)$l['qty'] * (float)$l['unit_price'], $sym) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Totals -->
  <div class="totals-block mt-3">
    <table class="w-100">
      <tr>
        <td class="text-muted">Subtotal (excl. VAT)</td>
        <td class="text-end"><?= money($totals['subtotal'], $sym) ?></td>
      </tr>
      <?php if ((float)$doc['tax_rate'] > 0): ?>
      <tr>
        <td class="text-muted">VAT (<?= number_format((float)$doc['tax_rate'], 1) ?>%)</td>
        <td class="text-end"><?= money($totals['tax_amount'], $sym) ?></td>
      </tr>
      <?php endif; ?>
      <tr class="border-top">
        <td class="fw-bold pt-2">Total <?= htmlspecialchars($doc['currency'] ?? 'EUR') ?></td>
        <td class="fw-bold pt-2 text-end fs-5"><?= money($totals['total'], $sym) ?></td>
      </tr>
    </table>
  </div>

  <!-- Notes -->
  <?php if ($doc['notes']): ?>
  <div class="mt-4 p-3 bg-light rounded">
    <div class="small fw-semibold text-muted mb-1">Notes</div>
    <div class="small"><?= nl2br(htmlspecialchars($doc['notes'])) ?></div>
  </div>
  <?php endif; ?>

  <!-- Payment info (invoices) -->
  <?php if ($doc['type'] === 'invoice'): ?>
  <div class="mt-4">
    <div class="small fw-semibold text-muted mb-1">Payment</div>
    <div class="small"><?= htmlspecialchars($doc['payment_method'] ?? '') ?></div>
  </div>
  <?php endif; ?>

  <!-- Acceptance block (quotes only) -->
  <?php if ($doc['type'] === 'quote'): ?>
  <div class="acceptance-block mt-4">
    <div class="fw-semibold mb-1">Client acceptance</div>
    <p class="mb-2">Quote received before execution, read and approved, agreed.</p>
    <div class="row">
      <div class="col-6">Date: ___________________________</div>
      <div class="col-6">Signature &amp; stamp:</div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Legal footer -->
  <div class="legal-block">
    <?php if ($doc['vat_mention']): ?>
    <p class="mb-1"><strong>VAT:</strong> <?= htmlspecialchars($doc['vat_mention']) ?></p>
    <?php endif; ?>
    <p class="mb-1">In the event of late payment, a penalty equal to <?= number_format((float)$doc['late_payment_rate'], 2) ?>% per year will be applied, plus a fixed recovery fee of <?= $sym ?> <?= number_format((float)$doc['late_payment_fee'], 2) ?> (mandatory for B2B — Article L441-10 of the French Commercial Code).</p>
    <p class="mb-0"><?= htmlspecialchars($co['name']) ?> <?= htmlspecialchars($co['legal_form']) ?> — Capital <?= htmlspecialchars($co['share_capital']) ?> € — SIRET <?= htmlspecialchars($co['siret']) ?> — VAT <?= htmlspecialchars($co['vat_number']) ?></p>
  </div>

</div><!-- /doc-card -->
</div>

</body>
</html>
