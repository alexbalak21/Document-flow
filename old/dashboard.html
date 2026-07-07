<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_login();

$page_title = 'Dashboard';
$active_nav = 'dashboard';

$db = db();

// Stats
$stats = [
    'clients'  => $db->query("SELECT COUNT(*) FROM clients")->fetchColumn(),
    'quotes'   => $db->query("SELECT COUNT(*) FROM documents WHERE type='quote'")->fetchColumn(),
    'invoices' => $db->query("SELECT COUNT(*) FROM documents WHERE type='invoice'")->fetchColumn(),
    'draft'    => $db->query("SELECT COUNT(*) FROM documents WHERE status='draft'")->fetchColumn(),
];

// Recent documents
$recent = $db->query("
    SELECT d.id, d.number, d.type, d.status, d.issue_date,
           c.name AS client_name, c.company
    FROM documents d
    JOIN clients c ON c.id = d.client_id
    ORDER BY d.created_at DESC
    LIMIT 8
")->fetchAll();

require __DIR__ . '/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0 fw-semibold">Dashboard</h4>
  <a href="<?= BASE_URL ?>/document_form.php" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i>New document
  </a>
</div>

<!-- Stats cards -->
<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['Clients',       $stats['clients'],  'bi-people',          'primary',   BASE_URL.'/clients.php'],
    ['Quotes',        $stats['quotes'],   'bi-file-earmark-text','success',  BASE_URL.'/documents.php?type=quote'],
    ['Invoices',      $stats['invoices'], 'bi-receipt',         'info',      BASE_URL.'/documents.php?type=invoice'],
    ['Drafts',        $stats['draft'],    'bi-pencil-square',   'warning',   BASE_URL.'/documents.php?status=draft'],
  ];
  foreach ($cards as [$label, $count, $icon, $color, $link]): ?>
  <div class="col-6 col-xl-3">
    <a href="<?= $link ?>" class="text-decoration-none">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-3 bg-<?= $color ?>-subtle p-3">
            <i class="bi <?= $icon ?> text-<?= $color ?> fs-4"></i>
          </div>
          <div>
            <div class="fs-3 fw-bold"><?= $count ?></div>
            <div class="text-muted small"><?= $label ?></div>
          </div>
        </div>
      </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<!-- Recent documents -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <span class="fw-medium">Recent documents</span>
    <a href="<?= BASE_URL ?>/documents.php" class="btn btn-sm btn-outline-secondary">View all</a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Number</th><th>Type</th><th>Client</th><th>Date</th><th>Status</th><th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($recent)): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No documents yet. <a href="<?= BASE_URL ?>/document_form.php">Create one</a>.</td></tr>
        <?php else: foreach ($recent as $doc): ?>
        <tr>
          <td><a href="<?= BASE_URL ?>/document_view.php?id=<?= $doc['id'] ?>" class="fw-medium text-decoration-none"><?= htmlspecialchars($doc['number']) ?></a></td>
          <td><span class="badge text-bg-<?= $doc['type'] === 'invoice' ? 'info' : ($doc['type'] === 'quote' ? 'success' : 'secondary') ?>"><?= ucfirst(str_replace('_', ' ', $doc['type'])) ?></span></td>
          <td><?= htmlspecialchars($doc['company'] ?: $doc['client_name']) ?></td>
          <td class="text-muted small"><?= date('d/m/Y', strtotime($doc['issue_date'])) ?></td>
          <td><?php
            $sc = ['draft'=>'secondary','sent'=>'primary','accepted'=>'success','rejected'=>'danger','paid'=>'dark','cancelled'=>'light text-dark'];
            echo '<span class="badge text-bg-'.($sc[$doc['status']] ?? 'secondary').'">'.ucfirst($doc['status']).'</span>';
          ?></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/document_view.php?id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
