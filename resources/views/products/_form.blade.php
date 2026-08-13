<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label fw-medium">Reference</label>
        <input type="text" name="reference" class="form-control"
               value="{{ old('reference', $product->reference ?? '') }}"
               placeholder="Leave blank if this line has no catalog reference">
    </div>
    <div class="col-md-8">
        <label class="form-label fw-medium">Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control"
               value="{{ old('name', $product->name ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-medium">Unit</label>
        <input type="text" name="product_unit" class="form-control"
               value="{{ old('product_unit', $product->product_unit ?? '') }}"
               placeholder="e.g. (1 plate, 96 assays)">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-medium">Unit Price (€) <span class="text-danger">*</span></label>
        <input type="number" name="unit_price" class="form-control"
               step="0.01" min="0"
               value="{{ old('unit_price', $product->unit_price ?? '') }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label fw-medium">Page URL</label>
        <input type="url" name="page_url" class="form-control"
               value="{{ old('page_url', $product->page_url ?? '') }}">
    </div>
    <div class="col-12">
        <label class="form-label fw-medium">Description</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
    </div>
</div>
