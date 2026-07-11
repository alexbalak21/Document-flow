@extends('layouts.auth')

@section('title', 'Customers')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h4 class="fw-semibold mb-0">Customers</h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('export.customer.model') }}" class="btn btn-outline-info btn-sm">
                <i class="bi bi-download me-1"></i>JSON Model
            </a>
            <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#importCustomerModal">
                <i class="bi bi-upload me-1"></i>Import JSON
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
                <i class="bi bi-person-plus me-1"></i>New Customer
            </button>
        </div>
    </div>

    @if(session('success'))
        <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
    @endif

    @if($customers->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center text-muted py-5">
                No customers yet. Create your first one above.
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Name</th>
                            <th>Company</th>
                            <th>City</th>
                            <th>Email</th>
                            <th>VAT</th>
                            <th class="pe-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $c)
                        <tr>
                            <td class="ps-3 fw-medium">{{ $c->name }}</td>
                            <td class="text-muted">{{ $c->company ?? '—' }}</td>
                            <td class="text-muted">{{ $c->city ?? '—' }}</td>
                            <td class="text-muted">{{ $c->email ?? '—' }}</td>
                            <td class="text-muted small">{{ $c->vat_number ?? '—' }}</td>
                            <td class="pe-3 text-end">
                                <a href="{{ route('export.customer', $c) }}"
                               class="btn btn-sm btn-outline-info" title="Export JSON">
                                <i class="bi bi-download"></i>
                            </a>
                            <a href="{{ route('customers.edit', $c) }}"
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

        @if($customers->hasPages())
            <div class="mt-3">{{ $customers->links() }}</div>
        @endif
    @endif
</div>

{{-- New Customer Modal --}}
<div class="modal fade" id="newCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('customers.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold">New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('customers._form')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Import Customer JSON Modal --}}
<div class="modal fade" id="importCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">Import Customer from JSON</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <x-ui.alert type="danger"  id="cust-import-error"   :hidden="true" :small="true" />
                <x-ui.alert type="success" id="cust-import-success" :hidden="true" :small="true" />

                <div class="mb-3">
                    <label class="form-label fw-medium small">Upload JSON file</label>
                    <input type="file" id="cust-json-file" accept=".json" class="form-control form-control-sm">
                </div>
                <div class="text-center text-muted small mb-3">— or —</div>
                <div class="mb-3">
                    <label class="form-label fw-medium small">Paste JSON</label>
                    <textarea id="cust-json-text" class="form-control form-control-sm" rows="5"
                        placeholder='{"name":"John","company":"Acme","email":"john@acme.com",...}'></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="importCustomer()">
                    <i class="bi bi-upload me-1"></i>Import & Create
                </button>
            </div>
        </div>
    </div>
</div>

<script>
async function importCustomer() {
    const file    = document.getElementById('cust-json-file').files[0];
    const text    = document.getElementById('cust-json-text').value.trim();
    const errBox  = document.getElementById('cust-import-error');
    const okBox   = document.getElementById('cust-import-success');
    errBox.classList.add('d-none');
    okBox.classList.add('d-none');

    const formData = new FormData();
    if (file)      formData.append('json_file', file);
    else if (text) formData.append('json_text', text);
    else { errBox.textContent = 'Please upload a file or paste JSON.'; errBox.classList.remove('d-none'); return; }

    // First parse the JSON to get fields
    const importRes = await fetch('{{ route("import.customer") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: formData,
    });

    const importJson = await importRes.json();
    if (importJson.error) { errBox.textContent = importJson.error; errBox.classList.remove('d-none'); return; }

    // Then save as new customer
    const saveRes = await fetch('{{ route("customers.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(importJson.fields),
    });

    if (!saveRes.ok) {
        const err = await saveRes.json();
        errBox.textContent = Object.values(err.errors ?? {err: 'Error saving'}).flat().join(' ');
        errBox.classList.remove('d-none');
        return;
    }

    okBox.textContent = 'Customer imported successfully. Reloading...';
    okBox.classList.remove('d-none');
    setTimeout(() => location.reload(), 1200);
}
</script>
@endsection
