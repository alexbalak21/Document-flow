<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_login();

$search = trim($_GET['q'] ?? '');
$sql    = "SELECT * FROM products";
$params = [];
if ($search !== '') {
    $sql .= " WHERE title LIKE ? OR reference LIKE ?";
    $params = ["%$search%", "%$search%"];
}
$sql .= " ORDER BY title";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$page_title = 'Products';
$active_nav = 'products';
require __DIR__ . '/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0 fw-semibold">Product catalog</h4>
</div>

<form class="mb-3" method="get">
  <div class="input-group" style="max-width:380px">
    <input type="search" name="q" class="form-control" placeholder="Search reference or title…" value="<?= htmlspecialchars($search) ?>">
    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
  </div>
</form>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr><th>Reference</th><th>Title</th><th>Size / format</th><th class="text-end">Price (€)</th><th>Updated</th></tr>
      </thead>
      <tbody>
        <?php if (empty($products)): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">No products found.</td></tr>
        <?php else: foreach ($products as $p): ?>
        <tr>
          <td class="fw-medium small"><?= htmlspecialchars($p['reference']) ?></td>
          <td><?= htmlspecialchars($p['title']) ?></td>
          <td class="text-muted small"><?= htmlspecialchars($p['size']) ?></td>
          <td class="text-end fw-medium"><?= number_format((float)$p['price'], 2) ?></td>
          <td class="text-muted small"><?= htmlspecialchars($p['updated_on']) ?></td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
