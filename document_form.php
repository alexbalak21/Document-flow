<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_login();

$db = db();
$edit_id = (int)($_GET['id'] ?? 0);
$doc     = null;
$lines   = [];

if ($edit_id > 0) {
    $doc = $db->prepare("SELECT * FROM documents WHERE id = ?")->execute([$edit_id]) ? null : null;
    $stmt = $db->prepare("SELECT * FROM documents WHERE id = ?");
    $stmt->execute([$edit_id]);
    $doc = $stmt->fetch();
    if (!$doc) { header('Location: ' . BASE_URL . '/documents.php'); exit; }

    $lstmt = $db->prepare("SELECT * FROM document_lines WHERE document_id = ? ORDER BY sort_order");
    $lstmt->execute([$edit_id]);
    $lines = $lstmt->fetchAll();
}

$clients  = $db->query("SELECT id, name, company FROM clients ORDER BY company, name")->fetchAll();
$products = $db->query("SELECT ID as id, reference, title, size, price FROM products ORDER BY title")->fetchAll();

$today     = date('Y-m-d');
$page_title = $doc ? 'Edit ' . htmlspecialchars($doc['number']) : 'New document';
$active_nav = 'documents';

require __DIR__ . '/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0 fw-semibold"><?= $doc ? 'Edit ' . htmlspecialchars($doc['number']) : 'New document' ?></h4>
  <a href="<?= BASE_URL ?>/documents.php" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Back
  </a>
</div>

<form method="post" action="<?= BASE_URL ?>/document_save.php" id="docForm">
  <?php if ($doc): ?><input type="hidden" name="id" value="<?= $doc['id'] ?>"><?php endif; ?>

  <div class="row g-4">

    <!-- Left column: header info -->
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <h6 class="fw-semibold mb-3">Document</h6>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-medium">Type <span class="text-danger">*</span></label>
              <select name="type" id="doc_type" class="form-select" required>
                <option value="quote"              <?= ($doc['type'] ?? '') === 'quote'              ? 'selected' : '' ?>>Quote</option>
                <option value="invoice"            <?= ($doc['type'] ?? '') === 'invoice'            ? 'selected' : '' ?>>Invoice</option>
                <option value="order_confirmation" <?= ($doc['type'] ?? '') === 'order_confirmation' ? 'selected' : '' ?>>Order confirmation</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Status</label>
              <select name="status" class="form-select">
                <?php foreach (['draft','sent','accepted','rejected','paid','cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= ($doc['status'] ?? 'draft') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Issue date <span class="text-danger">*</span></label>
              <input type="date" name="issue_date" class="form-control" value="<?= htmlspecialchars($doc['issue_date'] ?? $today) ?>" required>
            </div>
            <div class="col-md-6" id="field_valid_until" style="<?= ($doc['type'] ?? 'quote') !== 'quote' ? 'display:none' : '' ?>">
              <label class="form-label fw-medium">Valid until</label>
              <input type="date" name="valid_until" class="form-control" value="<?= htmlspecialchars($doc['valid_until'] ?? '') ?>">
            </div>
            <div class="col-md-6" id="field_due_date" style="<?= ($doc['type'] ?? 'quote') === 'quote' ? 'display:none' : '' ?>">
              <label class="form-label fw-medium">Due date</label>
              <input type="date" name="due_date" class="form-control" value="<?= htmlspecialchars($doc['due_date'] ?? '') ?>">
            </div>
            <div class="col-md-6" id="field_service_date" style="<?= ($doc['type'] ?? 'quote') === 'quote' ? 'display:none' : '' ?>">
              <label class="form-label fw-medium">Service / delivery date</label>
              <input type="date" name="service_date" class="form-control" value="<?= htmlspecialchars($doc['service_date'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">PO / quote reference</label>
              <input type="text" name="po_reference" class="form-control" value="<?= htmlspecialchars($doc['po_reference'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Payment method</label>
              <input type="text" name="payment_method" class="form-control" value="<?= htmlspecialchars($doc['payment_method'] ?? 'Bank Transfer (Wire)') ?>">
            </div>
          </div>
        </div>
      </div>

      <!-- VAT -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <h6 class="fw-semibold mb-3">VAT &amp; Totals</h6>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label fw-medium">VAT rate (%)</label>
              <input type="number" name="tax_rate" id="tax_rate" class="form-control" min="0" max="100" step="0.01"
                     value="<?= htmlspecialchars($doc['tax_rate'] ?? '0') ?>" oninput="recalc()">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-medium">Currency symbol</label>
              <input type="text" name="currency_symbol" class="form-control" value="<?= htmlspecialchars($doc['currency_symbol'] ?? '€') ?>" maxlength="5">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-medium">Currency</label>
              <input type="text" name="currency" class="form-control" value="<?= htmlspecialchars($doc['currency'] ?? 'EUR') ?>" maxlength="10">
            </div>
            <div class="col-12">
              <label class="form-label fw-medium">VAT legal mention</label>
              <select name="vat_mention" class="form-select mb-2" onchange="this.nextElementSibling.value=this.value==='custom'?'':this.value">
                <option value="">— Select —</option>
                <?php foreach (VAT_MENTIONS as $m): ?>
                <option value="<?= htmlspecialchars($m) ?>" <?= ($doc['vat_mention'] ?? '') === $m ? 'selected' : '' ?>><?= htmlspecialchars($m) ?></option>
                <?php endforeach; ?>
                <option value="custom">Custom…</option>
              </select>
              <input type="text" name="vat_mention_custom" class="form-control form-control-sm"
                     placeholder="Or type a custom VAT mention"
                     value="<?= htmlspecialchars($doc['vat_mention'] ?? '') ?>">
            </div>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h6 class="fw-semibold mb-3">Notes</h6>
          <textarea name="notes" class="form-control" rows="3" placeholder="Shipping info, conditions, additional instructions…"><?= htmlspecialchars($doc['notes'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- Right column: client + line items -->
    <div class="col-lg-5">

      <!-- Client selector -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <h6 class="fw-semibold mb-3">Client <span class="text-danger">*</span></h6>
          <select name="client_id" id="client_id" class="form-select" required>
            <option value="">— Select a client —</option>
            <?php foreach ($clients as $c): ?>
            <option value="<?= $c['id'] ?>" <?= ($doc['client_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['company'] ? $c['company'] . ' – ' . $c['name'] : $c['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <a href="<?= BASE_URL ?>/clients.php" class="small text-muted mt-2 d-inline-block">
            <i class="bi bi-plus-circle me-1"></i>Add a new client
          </a>
        </div>
      </div>

      <!-- Line items -->
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h6 class="fw-semibold mb-3">Line items</h6>

          <!-- Product search to add lines -->
          <div class="input-group mb-3">
            <input type="text" id="product_search" class="form-control form-control-sm" placeholder="Search products to add…">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addFreeLine()">
              <i class="bi bi-plus"></i> Free line
            </button>
          </div>
          <div id="product_suggestions" class="list-group mb-3" style="display:none;max-height:200px;overflow-y:auto"></div>

          <!-- Lines table -->
          <div id="lines_container">
            <table class="table table-sm align-middle" id="lines_table">
              <thead class="table-light">
                <tr>
                  <th style="width:40%">Description</th>
                  <th style="width:10%">Qty</th>
                  <th style="width:18%">Unit price</th>
                  <th style="width:18%">Total</th>
                  <th style="width:14%"></th>
                </tr>
              </thead>
              <tbody id="lines_body">
                <?php foreach ($lines as $i => $line): ?>
                <tr class="line-row" data-idx="<?= $i ?>">
                  <td>
                    <input type="hidden" name="lines[<?= $i ?>][product_id]" value="<?= $line['product_id'] ?? '' ?>">
                    <input type="hidden" name="lines[<?= $i ?>][reference]"  value="<?= htmlspecialchars($line['reference'] ?? '') ?>">
                    <input type="text"   name="lines[<?= $i ?>][description]" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($line['description']) ?>" required>
                  </td>
                  <td><input type="number" name="lines[<?= $i ?>][qty]" class="form-control form-control-sm line-qty" value="<?= $line['qty'] ?>" min="0" step="any" oninput="recalc()"></td>
                  <td><input type="number" name="lines[<?= $i ?>][unit_price]" class="form-control form-control-sm line-price" value="<?= $line['unit_price'] ?>" min="0" step="any" oninput="recalc()"></td>
                  <td class="line-total text-end fw-medium">—</td>
                  <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(this)"><i class="bi bi-x"></i></button></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Totals -->
          <div class="border-top pt-2 mt-2 text-end">
            <div class="d-flex justify-content-between text-muted small"><span>Subtotal (excl. VAT)</span><span id="sum_subtotal">0.00</span></div>
            <div class="d-flex justify-content-between text-muted small"><span>VAT (<span id="vat_pct">0</span>%)</span><span id="sum_tax">0.00</span></div>
            <div class="d-flex justify-content-between fw-semibold mt-1"><span>Total</span><span id="sum_total">0.00</span></div>
          </div>
        </div>
      </div>
    </div>
  </div><!-- /row -->

  <div class="mt-4 d-flex gap-2">
    <button type="submit" name="save_and_view" value="1" class="btn btn-primary">
      <i class="bi bi-check-lg me-1"></i>Save &amp; view
    </button>
    <button type="submit" class="btn btn-outline-secondary">
      <i class="bi bi-floppy me-1"></i>Save draft
    </button>
    <a href="<?= BASE_URL ?>/documents.php" class="btn btn-link text-muted">Cancel</a>
  </div>
</form>

<?php
// Pass products to JS
$products_json = json_encode($products, JSON_HEX_TAG);
$line_idx = count($lines);
$extra_js = <<<JS
<script>
const products = $products_json;
let lineIdx = $line_idx;

// Product search
const searchInput = document.getElementById('product_search');
const suggestions = document.getElementById('product_suggestions');

searchInput.addEventListener('input', function() {
  const q = this.value.toLowerCase().trim();
  suggestions.innerHTML = '';
  if (q.length < 2) { suggestions.style.display = 'none'; return; }
  const matches = products.filter(p =>
    p.title.toLowerCase().includes(q) || p.reference.toLowerCase().includes(q)
  ).slice(0, 8);
  if (!matches.length) { suggestions.style.display = 'none'; return; }
  matches.forEach(p => {
    const a = document.createElement('a');
    a.className = 'list-group-item list-group-item-action py-1 small';
    a.href = '#';
    a.innerHTML = '<span class="fw-medium">' + escHtml(p.reference) + '</span> — ' + escHtml(p.title)
      + (p.size ? ' <span class="text-muted">(' + escHtml(p.size) + ')</span>' : '')
      + ' <span class="float-end text-success">' + p.price + ' €</span>';
    a.addEventListener('click', e => {
      e.preventDefault();
      addProductLine(p);
      searchInput.value = '';
      suggestions.style.display = 'none';
    });
    suggestions.appendChild(a);
  });
  suggestions.style.display = 'block';
});

document.addEventListener('click', e => {
  if (!searchInput.contains(e.target) && !suggestions.contains(e.target))
    suggestions.style.display = 'none';
});

function addProductLine(p) {
  const desc = p.title + (p.size ? ' (' + p.size + ')' : '') + ' (Ref. ' + p.reference + ')';
  addLine(p.id, p.reference, desc, 1, p.price);
}

function addFreeLine() {
  addLine('', '', '', 1, 0);
}

function addLine(product_id, reference, description, qty, price) {
  const i = lineIdx++;
  const sym = document.querySelector('[name=currency_symbol]').value || '€';
  const tr = document.createElement('tr');
  tr.className = 'line-row';
  tr.dataset.idx = i;
  tr.innerHTML = \`
    <td>
      <input type="hidden" name="lines[\${i}][product_id]" value="\${escAttr(product_id)}">
      <input type="hidden" name="lines[\${i}][reference]"  value="\${escAttr(reference)}">
      <input type="text"   name="lines[\${i}][description]" class="form-control form-control-sm" value="\${escAttr(description)}" required>
    </td>
    <td><input type="number" name="lines[\${i}][qty]"        class="form-control form-control-sm line-qty"   value="\${qty}"   min="0" step="any" oninput="recalc()"></td>
    <td><input type="number" name="lines[\${i}][unit_price]" class="form-control form-control-sm line-price" value="\${price}" min="0" step="any" oninput="recalc()"></td>
    <td class="line-total text-end fw-medium">—</td>
    <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(this)"><i class="bi bi-x"></i></button></td>
  \`;
  document.getElementById('lines_body').appendChild(tr);
  recalc();
  tr.querySelector('[name$="[description]"]').focus();
}

function removeLine(btn) {
  btn.closest('tr').remove();
  recalc();
}

function recalc() {
  const sym = document.querySelector('[name=currency_symbol]').value || '€';
  const rate = parseFloat(document.getElementById('tax_rate').value) || 0;
  document.getElementById('vat_pct').textContent = rate;
  let subtotal = 0;
  document.querySelectorAll('.line-row').forEach(tr => {
    const qty   = parseFloat(tr.querySelector('.line-qty').value)   || 0;
    const price = parseFloat(tr.querySelector('.line-price').value) || 0;
    const total = qty * price;
    subtotal += total;
    tr.querySelector('.line-total').textContent = sym + ' ' + total.toFixed(2);
  });
  const tax   = subtotal * rate / 100;
  document.getElementById('sum_subtotal').textContent = sym + ' ' + subtotal.toFixed(2);
  document.getElementById('sum_tax').textContent      = sym + ' ' + tax.toFixed(2);
  document.getElementById('sum_total').textContent    = sym + ' ' + (subtotal + tax).toFixed(2);
}

// Show/hide date fields based on type
document.getElementById('doc_type').addEventListener('change', function() {
  const isQuote = this.value === 'quote';
  document.getElementById('field_valid_until').style.display  = isQuote ? '' : 'none';
  document.getElementById('field_due_date').style.display     = isQuote ? 'none' : '';
  document.getElementById('field_service_date').style.display = isQuote ? 'none' : '';
});

function escHtml(s)  { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function escAttr(s)  { return String(s).replace(/"/g,'&quot;'); }

// Init
recalc();
</script>
JS;
require __DIR__ . '/layout/footer.php';
?>
