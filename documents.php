<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_login();

$db = db();

$type   = $_GET['type']   ?? '';
$status = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');

$where  = ['1=1'];
$params = [];
if ($type   !== '') { $where[] = 'd.type = ?';   $params[] = $type; }
if ($status !== '') { $where[] = 'd.status = ?';  $params[] = $status; }
if ($search !== '') { $where[] = '(d.number LIKE ? OR c.name LIKE ? OR c.company LIKE ?)';
                      $params  = array_merge($params, ["%$search%", "%$search%", "%$search%"]); }

$sql = "SELECT d.id, d.number, d.type, d.status, d.issue_date, d.due_date,
               c.name AS client_name, c.company
        FROM documents d
        JOIN clients c ON c.id = d.client_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY d.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$docs = $stmt->fetchAll();

$title_parts = array_filter([$type ? ucfirst(str_replace('_', ' ', $type)) . 's' : 'All documents']);
$page_title  = implode(' — ', $title_parts);
$active_nav  = $type ?: 'documents';
require __DIR__ . '/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0 fw-semibold"><?= htmlspecialchars($page_title) ?></h4>
  <a href="<?= BASE_URL ?>/document_form.php" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i>New document
  </a>
</div>

<!-- Filters -->
<form class="row g-2 mb-4" method="get">
  <div class="col-auto">
    <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
      <option value="" <?= $type==='' ? 'selected':'' ?>>All types</option>
      <option value="quote"              <?= $type==='quote' ? 'selected':'' ?>>Quotes</option>
      <option value="invoice"            <?= $type==='invoice' ? 'selected':'' ?>>Invoices</option>
      <option value="order_confirmation" <?= $type==='order_confirmation' ? 'selected':'' ?>>Order confirmations</option>
    </select>
  </div>
  <div class="col-auto">
    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
      <option value="" <?= $status==='' ? 'selected':'' ?>>All statuses</option>
      <?php foreach (['draft','sent','accepted','rejected','paid','cancelled'] as $s): ?>
      <option value="<?= $s ?>" <?= $status===$s ? 'selected':'' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col">
    <div class="input-group input-group-sm">
      <input type="search" name="q" class="form-control" placeholder="Search…" value="<?= htmlspecialchars($search) ?>">
      <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
    </div>
  </div>
</form>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Number</th><th>Type</th><th>Client</th><th>Date</th><th>Due date</th><th>Status</th><th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($docs)): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">No documents found. <a href="<?= BASE_URL ?>/document_form.php">Create one</a>.</td></tr>
        <?php else: foreach ($docs as $d):
          $type_colors = ['quote'=>'success','invoice'=>'info','order_confirmation'=>'secondary'];
          $stat_colors = ['draft'=>'secondary','sent'=>'primary','accepted'=>'success','rejected'=>'danger','paid'=>'dark','cancelled'=>'light text-dark'];
        ?>
        <tr>
          <td><a href="<?= BASE_URL ?>/document_view.php?id=<?= $d['id'] ?>" class="fw-medium text-decoration-none"><?= htmlspecialchars($d['number']) ?></a></td>
          <td><span class="badge text-bg-<?= $type_colors[$d['type']] ?? 'secondary' ?>"><?= ucfirst(str_replace('_', ' ', $d['type'])) ?></span></td>
          <td><?= htmlspecialchars($d['company'] ?: $d['client_name']) ?><br><small class="text-muted"><?= htmlspecialchars($d['company'] ? $d['client_name'] : '') ?></small></td>
          <td class="text-muted small"><?= date('d/m/Y', strtotime($d['issue_date'])) ?></td>
          <td class="text-muted small"><?= $d['due_date'] ? date('d/m/Y', strtotime($d['due_date'])) : '—' ?></td>
          <td><span class="badge text-bg-<?= $stat_colors[$d['status']] ?? 'secondary' ?>"><?= ucfirst($d['status']) ?></span></td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/document_view.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-outline-secondary me-1" title="View"><i class="bi bi-eye"></i></a>
            <a href="<?= BASE_URL ?>/document_form.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
