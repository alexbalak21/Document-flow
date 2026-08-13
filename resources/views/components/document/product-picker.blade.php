{{--
    x-document.product-picker
    Line-items card used in both create and edit forms. Supports adding any
    number of products (via the searchable catalog modal or a manual blank
    row), plus a document-level discount (percentage or fixed amount).
    Props:
      $products – collection of Product models (unused for rendering, kept
                  for backward compatibility with callers)
      $prefill  – array of prefilled field values. Reads $prefill['items']
                  (array of {reference,name,unit,quantity,unit_price}) when
                  present, falling back to the legacy single-product fields.
--}}
@props(['products', 'prefill' => []])

@php
$prefillItems = $prefill['items'] ?? null;
if (empty($prefillItems) && ! empty($prefill['product_name'])) {
    $prefillItems = [[
        'reference'  => $prefill['product_reference']   ?? '',
        'name'       => $prefill['product_name']         ?? '',
        'unit'       => $prefill['product_unit']         ?? '',
        'quantity'   => $prefill['product_quantity']     ?? 1,
        'unit_price' => $prefill['product_unit_price']   ?? '',
    ]];
}
$prefillItems = $prefillItems ?? [];
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
        <span><i class="bi bi-box-seam me-2 text-success"></i>Products</span>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-success" id="add-manual-line">
                <i class="bi bi-plus-lg me-1"></i>Add Line
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary"
                data-bs-toggle="modal" data-bs-target="#productSearchModal" id="product-picker-btn">
                <i class="bi bi-search me-1"></i>Add Product
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-2" id="line-items-table">
                <thead>
                    <tr class="small text-muted">
                        <th style="min-width:110px;">Reference</th>
                        <th style="min-width:200px;">Product Name</th>
                        <th style="min-width:110px;">Unit</th>
                        <th style="width:80px;">Qty</th>
                        <th style="width:120px;">Unit Price (€)</th>
                        <th class="text-end" style="width:110px;">Line Total</th>
                        <th style="width:36px;"></th>
                    </tr>
                </thead>
                <tbody id="line-items-body">
                    @foreach($prefillItems as $i => $item)
                    <tr class="line-item-row">
                        <td><input type="text" name="items[{{ $i }}][reference]" class="form-control form-control-sm" value="{{ $item['reference'] ?? '' }}"></td>
                        <td><input type="text" name="items[{{ $i }}][name]" class="form-control form-control-sm" value="{{ $item['name'] ?? '' }}" required></td>
                        <td><input type="text" name="items[{{ $i }}][unit]" class="form-control form-control-sm" value="{{ $item['unit'] ?? '' }}" placeholder="e.g. 1 plate"></td>
                        <td><input type="number" name="items[{{ $i }}][quantity]" class="form-control form-control-sm qty-input" value="{{ $item['quantity'] ?? 1 }}" min="1" step="1" required></td>
                        <td><input type="number" name="items[{{ $i }}][unit_price]" class="form-control form-control-sm price-input" value="{{ $item['unit_price'] ?? '' }}" min="0" step="0.01" required></td>
                        <td class="text-end line-total fw-medium">€ 0.00</td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger remove-line" title="Remove"><i class="bi bi-trash"></i></button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="text-muted small text-center py-3 {{ count($prefillItems) ? 'd-none' : '' }}" id="line-items-empty">
            No products added yet — click <strong>Add Product</strong> to search your catalog, or <strong>Add Line</strong> to enter one manually.
        </div>
        <div class="text-end fw-semibold small" id="line-items-subtotal">Products subtotal: € 0.00</div>
    </div>
</div>

{{-- Discount --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-percent me-2 text-warning"></i>Discount
    </div>
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-medium small">Discount Type</label>
                <select name="discount_type" id="discount_type" class="form-select form-select-sm">
                    <option value="" {{ empty($prefill['discount_type']) ? 'selected' : '' }}>No discount</option>
                    <option value="percent" {{ ($prefill['discount_type'] ?? '') === 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                    <option value="amount" {{ ($prefill['discount_type'] ?? '') === 'amount' ? 'selected' : '' }}>Fixed Amount (€)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium small">Discount Value</label>
                <input type="number" name="discount_value" id="discount_value" class="form-control form-control-sm"
                    min="0" step="0.01" value="{{ $prefill['discount_value'] ?? '' }}">
            </div>
            <div class="col-md-4">
                <div class="small text-muted" id="discount-preview">No discount applied.</div>
            </div>
        </div>
    </div>
</div>

{{-- Product search modal --}}
<div class="modal fade" id="productSearchModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold"><i class="bi bi-box-seam me-2"></i>Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="product-search-input"
                        placeholder="Search by reference or name…" autocomplete="off">
                </div>
                <div id="product-search-results" class="list-group">
                    <div class="text-muted small text-center py-4" id="product-search-hint">
                        Start typing to search, or browse recent products.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
