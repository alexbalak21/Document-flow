<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_login();

$db = db();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    // Prevent delete if client has documents
    $used = $db->prepare("SELECT COUNT(*) FROM documents WHERE client_id = ?");
    $used->execute([$id]);
    if ($used->fetchColumn() > 0) {
        $flash_error = 'Cannot delete: this client has existing documents.';
    } else {
        $db->prepare("DELETE FROM clients WHERE id = ?")->execute([$id]);
        $flash_ok = 'Client deleted.';
    }
}

$search  = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM clients";
$params = [];
if ($search !== '') {
    $sql .= " WHERE name LIKE ? OR company LIKE ? OR email LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}
$sql .= " ORDER BY company, name";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$clients = $stmt->fetchAll();

$page_title = 'Clients';
$active_nav = 'clients';
require __DIR__ . '/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0 fw-semibold">Clients</h4>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clientModal" onclick="openNew()">
    <i class="bi bi-plus-lg me-1"></i>New client
  </button>
</div>

<?php if (!empty($flash_ok)): ?>
  <div class="alert alert-success alert-dismissible fade show py-2"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($flash_ok) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($flash_error)): ?>
  <div class="alert alert-danger alert-dismissible fade show py-2"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($flash_error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<!-- Search -->
<form class="mb-3" method="get">
  <div class="input-group" style="max-width:380px">
    <input type="search" name="q" class="form-control" placeholder="Search name, company, email…" value="<?= htmlspecialchars($search) ?>">
    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
  </div>
</form>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Name / Contact</th><th>Company</th><th>Country</th><th>Email</th><th>VAT number</th><th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($clients)): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No clients found.</td></tr>
        <?php else: foreach ($clients as $c): ?>
        <tr>
          <td class="fw-medium"><?= htmlspecialchars($c['name']) ?></td>
          <td><?= htmlspecialchars($c['company'] ?? '') ?><br><span class="text-muted small"><?= htmlspecialchars($c['department'] ?? '') ?></span></td>
          <td class="text-muted small"><?= htmlspecialchars($c['country'] ?? '') ?></td>
          <td class="small"><?= htmlspecialchars($c['email'] ?? '') ?></td>
          <td class="small text-muted"><?= htmlspecialchars($c['vat_number'] ?? '') ?></td>
          <td class="text-end">
            <button class="btn btn-sm btn-outline-secondary me-1"
                    onclick='editClient(<?= json_encode($c) ?>)'
                    data-bs-toggle="modal" data-bs-target="#clientModal">
              <i class="bi bi-pencil"></i>
            </button>
            <form method="post" class="d-inline" onsubmit="return confirm('Delete this client?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $c['id'] ?>">
              <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Create / Edit Modal -->
<div class="modal fade" id="clientModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" action="<?= BASE_URL ?>/client_save.php" novalidate>
        <input type="hidden" name="id" id="f_id">
        <div class="modal-header">
          <h5 class="modal-title" id="clientModalLabel">New client</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-medium">Contact name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="f_name" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Company / Institution</label>
              <input type="text" name="company" id="f_company" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Department</label>
              <input type="text" name="department" id="f_department" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Email</label>
              <input type="email" name="email" id="f_email" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Phone</label>
              <input type="text" name="phone" id="f_phone" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">VAT / GST number</label>
              <input type="text" name="vat_number" id="f_vat_number" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label fw-medium">Street address</label>
              <input type="text" name="street" id="f_street" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-medium">ZIP / Postal code</label>
              <input type="text" name="zip" id="f_zip" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-medium">City</label>
              <input type="text" name="city" id="f_city" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-medium">Country</label>
              <input type="text" name="country" id="f_country" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label fw-medium">Notes</label>
              <textarea name="notes" id="f_notes" class="form-control" rows="2"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save client</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
$extra_js = <<<'JS'
<script>
function openNew() {
  document.getElementById('clientModalLabel').textContent = 'New client';
  ['id','name','company','department','email','phone','vat_number','street','zip','city','country','notes']
    .forEach(f => { const el = document.getElementById('f_'+f); if(el) el.value=''; });
}
function editClient(c) {
  document.getElementById('clientModalLabel').textContent = 'Edit client';
  const map = {id:'id',name:'name',company:'company',department:'department',
    email:'email',phone:'phone',vat_number:'vat_number',
    street:'street',zip:'zip',city:'city',country:'country',notes:'notes'};
  for (const [field, key] of Object.entries(map)) {
    const el = document.getElementById('f_'+field);
    if (el) el.value = c[key] ?? '';
  }
}
</script>
JS;
require __DIR__ . '/layout/footer.php';
?>
