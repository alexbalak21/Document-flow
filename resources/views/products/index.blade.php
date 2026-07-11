@extends('layouts.auth')

@section('title', 'Products')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h4 class="fw-semibold mb-0">Products</h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('export.product.model') }}" class="btn btn-outline-info btn-sm">
                <i class="bi bi-download me-1"></i>JSON Model
            </a>
            <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#importProductModal">
                <i class="bi bi-upload me-1"></i>Import JSON
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newProductModal">
                <i class="bi bi-plus-lg me-1"></i>New Product
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2 alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($products->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center text-muted py-5">
                No products yet. Create one or import from JSON.
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Reference</th>
                            <th>Name</th>
                            <th>Unit</th>
                            <th class="text-end">Unit Price</th>
                            <th class="pe-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $p)
                        <tr>
                            <td class="ps-3"><code>{{ $p->reference }}</code></td>
                            <td class="fw-medium">{{ $p->name }}</td>
                            <td class="text-muted small">{{ $p->product_unit ?? '—' }}</td>
                            <td class="text-end fw-medium">€{{ $p->formatted_price }}</td>
                            <td class="pe-3 text-end">
                                <a href="{{ route('export.product', $p) }}"
                                   class="btn btn-sm btn-outline-info" title="Export JSON">
                                    <i class="bi bi-download"></i>
                                </a>
                                <a href="{{ route('products.edit', $p) }}"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($products->hasPages())
            <div class="mt-3">{{ $products->links() }}</div>
        @endif
    @endif
</div>

{{-- New Product Modal --}}
<div class="modal fade" id="newProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('products.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold">New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('products._form')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Import Product JSON Modal --}}
<div class="modal fade" id="importProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">Import Product from JSON</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="prod-import-error" class="alert alert-danger d-none py-2 small"></div>
                <div id="prod-import-success" class="alert alert-success d-none py-2 small"></div>
                <div class="mb-3">
                    <label class="form-label fw-medium small">Upload JSON file</label>
                    <input type="file" id="prod-json-file" accept=".json" class="form-control form-control-sm">
                </div>
                <div class="text-center text-muted small mb-3">— or —</div>
                <div class="mb-3">
                    <label class="form-label fw-medium small">Paste JSON</label>
                    <textarea id="prod-json-text" class="form-control form-control-sm" rows="6"
                        placeholder='{"reference":"K0307-01","name":"Kit","unit_price":530.0,...}'></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="importProduct()">
                    <i class="bi bi-upload me-1"></i>Import & Create
                </button>
            </div>
        </div>
    </div>
</div>

<script>
async function importProduct() {
    const file   = document.getElementById('prod-json-file').files[0];
    const text   = document.getElementById('prod-json-text').value.trim();
    const errBox = document.getElementById('prod-import-error');
    const okBox  = document.getElementById('prod-import-success');
    errBox.classList.add('d-none');
    okBox.classList.add('d-none');

    const formData = new FormData();
    if (file)      formData.append('json_file', file);
    else if (text) formData.append('json_text', text);
    else { errBox.textContent = 'Please upload a file or paste JSON.'; errBox.classList.remove('d-none'); return; }

    const importRes = await fetch('{{ route("import.product") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: formData,
    });

    const importJson = await importRes.json();
    if (importJson.error) { errBox.textContent = importJson.error; errBox.classList.remove('d-none'); return; }

    // Map back to product field names (strip product_ prefix for store)
    const fields = {};
    for (const [k, v] of Object.entries(importJson.fields)) {
        fields[k.replace('product_', '')] = v;
    }

    const saveRes = await fetch('{{ route("products.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(fields),
    });

    if (!saveRes.ok) {
        const err = await saveRes.json();
        errBox.textContent = Object.values(err.errors ?? {err: 'Error'}).flat().join(' ');
        errBox.classList.remove('d-none');
        return;
    }

    okBox.textContent = 'Product imported successfully. Reloading...';
    okBox.classList.remove('d-none');
    setTimeout(() => location.reload(), 1200);
}
</script>
@endsection
