{{--
    x-document.product-picker
    Product selection card used in both create and edit forms.
    Props:
      $products – collection of Product models
      $prefill  – array of prefilled field values
--}}
@props(['products', 'prefill' => []])

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
        <span><i class="bi bi-box-seam me-2 text-success"></i>Product</span>
    </div>
    <div class="card-body">
        <label class="form-label fw-medium">Select existing product</label>
        <select class="form-select mb-3" id="product-picker">
            <option value="">— Pick a product to auto-fill —</option>
            @foreach($products as $p)
            <option value="{{ $p->id }}"
                data-reference="{{ $p->reference }}"
                data-name="{{ $p->name }}"
                data-description="{{ $p->description }}"
                data-unit="{{ $p->product_unit }}"
                data-price="{{ $p->unit_price }}">
                {{ $p->reference }} — {{ $p->name }} (€{{ $p->formatted_price }})
            </option>
            @endforeach
        </select>

        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label fw-medium small">Reference <span class="text-danger">*</span></label>
                <input type="text" name="product_reference" class="form-control form-control-sm"
                       value="{{ $prefill['product_reference'] ?? '' }}" required>
            </div>
            <div class="col-md-8">
                <label class="form-label fw-medium small">Product Name <span class="text-danger">*</span></label>
                <input type="text" name="product_name" class="form-control form-control-sm"
                       value="{{ $prefill['product_name'] ?? '' }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-medium small">Unit</label>
                <input type="text" name="product_unit" class="form-control form-control-sm"
                       value="{{ $prefill['product_unit'] ?? '' }}"
                       placeholder="e.g. (1 plate, 96 assays)">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-medium small">Quantity <span class="text-danger">*</span></label>
                <input type="number" name="product_quantity" class="form-control form-control-sm"
                       value="{{ $prefill['product_quantity'] ?? 1 }}" min="1" step="1" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-medium small">Unit Price (€) <span class="text-danger">*</span></label>
                <input type="number" name="product_unit_price" class="form-control form-control-sm"
                       value="{{ $prefill['product_unit_price'] ?? '' }}" min="0" step="0.01" required>
            </div>
        </div>
    </div>
</div>
