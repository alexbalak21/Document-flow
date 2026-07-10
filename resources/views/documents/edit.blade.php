@extends('layouts.auth')

@section('title', 'Edit ' . $type->name)

@section('content')
<div class="container py-4" style="max-width:800px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('documents.show', $document) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-semibold mb-0">Edit {{ $type->name }}</h4>
            <div class="text-muted small">
                {{ $document->title }} · v{{ $document->version }}
                <span class="badge text-bg-secondary ms-1">Draft</span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('documents.update', $document) }}" id="edit-form">
        @csrf
        @method('PUT')

        {{-- Customer --}}
        @if(isset($entities['customer']))
        @php $customers = $entityData['customer']; @endphp
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-person me-2 text-primary"></i>Customer</span>
                <button type="button" class="btn btn-sm btn-outline-primary"
                    data-bs-toggle="modal" data-bs-target="#newCustomerModal">
                    <i class="bi bi-person-plus me-1"></i>New Customer
                </button>
            </div>
            <div class="card-body">
                <label class="form-label fw-medium">Select existing customer</label>
                <select class="form-select mb-3" id="customer-picker" name="customer_id">
                    <option value="">— Type a new customer below or select one —</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}"
                        data-name="{{ $c->name }}"
                        data-company="{{ $c->company }}"
                        data-department="{{ $c->department }}"
                        data-street="{{ $c->street }}"
                        data-city="{{ $c->city }}"
                        data-zip="{{ $c->zip }}"
                        data-country="{{ $c->country }}"
                        data-phone="{{ $c->phone }}"
                        data-email="{{ $c->email }}"
                        data-vat="{{ $c->vat_number }}"
                        {{ $customerId == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}{{ $c->company ? ' — ' . $c->company : '' }}
                    </option>
                    @endforeach
                </select>

                <div class="row g-2">
                    @php
                    $customerFields = [
                    ['customer_name', 'Full Name', 'text', true],
                    ['customer_company', 'Company', 'text', false],
                    ['customer_department', 'Department', 'text', false],
                    ['customer_vat_number', 'VAT Number', 'text', false],
                    ['customer_street', 'Street / Address', 'text', false],
                    ['customer_city', 'City', 'text', false],
                    ['customer_zip', 'ZIP', 'text', false],
                    ['customer_country', 'Country', 'text', false],
                    ['customer_phone', 'Phone', 'tel', false],
                    ['customer_email', 'Email', 'email', false],
                    ];
                    @endphp
                    @foreach($customerFields as [$fieldName, $label, $inputType, $required])
                    <div class="col-md-6">
                        <label class="form-label fw-medium small">
                            {{ $label }}
                            @if($required)<span class="text-danger">*</span>@endif
                        </label>
                        <input type="{{ $inputType }}"
                            name="{{ $fieldName }}"
                            class="form-control form-control-sm"
                            value="{{ $prefill[$fieldName] ?? '' }}"
                            {{ $required ? 'required' : '' }}>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Product --}}
        @if(isset($entities['product']))
        @php $products = $entityData['product']; @endphp
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
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
        @endif

        {{-- Template-specific fields from form.json --}}
        @foreach($form as $section)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">{{ $section['section'] }}</div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($section['fields'] as $field)
                    <div class="col-md-6">
                        <label class="form-label fw-medium">
                            {{ $field['label'] ?? ucfirst(str_replace('_', ' ', $field['name'])) }}
                            @if(!empty($field['required']))<span class="text-danger">*</span>@endif
                        </label>

                        @php $val = $prefill[$field['name']] ?? old($field['name'], ''); @endphp

                        @if($field['type'] === 'textarea')
                        <textarea name="{{ $field['name'] }}" class="form-control" rows="3"
                            {{ !empty($field['required']) ? 'required' : '' }}>{{ $val }}</textarea>

                        @elseif($field['type'] === 'date')
                        <input type="date" name="{{ $field['name'] }}" class="form-control"
                            value="{{ $val ?: date('Y-m-d') }}"
                            {{ !empty($field['required']) ? 'required' : '' }}>

                        @elseif(in_array($field['type'], ['number', 'currency']))
                        <input type="number" name="{{ $field['name'] }}" class="form-control"
                            step="{{ $field['type'] === 'currency' ? '0.01' : '1' }}"
                            min="0" value="{{ $val }}"
                            {{ !empty($field['required']) ? 'required' : '' }}>

                        @else
                        <input type="{{ $field['type'] }}" name="{{ $field['name'] }}"
                            class="form-control" value="{{ $val }}"
                            {{ !empty($field['required']) ? 'required' : '' }}>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-warning">
                <i class="bi bi-floppy me-1"></i>Save Changes
            </button>
            <button type="button" class="btn btn-outline-primary" onclick="submitPreview()">
                <i class="bi bi-eye me-1"></i>Preview
            </button>
            <a href="{{ route('documents.show', $document) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>

    </form>

    {{-- Dedicated POST-only preview form --}}
    <form method="POST" action="{{ route('documents.preview', $type->slug) }}"
          target="_blank" id="preview-form" style="display:none;">
        @csrf
    </form>
</div>

{{-- New Customer Modal --}}
@if(isset($entities['customer']))
<div class="modal fade" id="newCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modal-errors" class="alert alert-danger d-none"></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label><input type="text" id="m_name" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label fw-medium">Company</label><input type="text" id="m_company" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label fw-medium">Department</label><input type="text" id="m_department" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label fw-medium">VAT Number</label><input type="text" id="m_vat_number" class="form-control"></div>
                    <div class="col-12"><label class="form-label fw-medium">Street</label><input type="text" id="m_street" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label fw-medium">City</label><input type="text" id="m_city" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label fw-medium">ZIP</label><input type="text" id="m_zip" class="form-control"></div>
                    <div class="col-md-5"><label class="form-label fw-medium">Country</label><input type="text" id="m_country" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label fw-medium">Phone</label><input type="tel" id="m_phone" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label fw-medium">Email</label><input type="email" id="m_email" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveNewCustomer">
                    <i class="bi bi-floppy me-1"></i>Save & Select
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<script>
    document.getElementById('customer-picker')?.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (!opt.value) return;
        const map = {
            'customer_name': opt.dataset.name,
            'customer_company': opt.dataset.company,
            'customer_department': opt.dataset.department,
            'customer_street': opt.dataset.street,
            'customer_city': opt.dataset.city,
            'customer_zip': opt.dataset.zip,
            'customer_country': opt.dataset.country,
            'customer_phone': opt.dataset.phone,
            'customer_email': opt.dataset.email,
            'customer_vat_number': opt.dataset.vat,
        };
        for (const [name, value] of Object.entries(map)) {
            const el = document.querySelector(`[name="${name}"]`);
            if (el) el.value = value ?? '';
        }
    });

    document.getElementById('product-picker')?.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (!opt.value) return;
        const fields = {
            'product_reference': opt.dataset.reference,
            'product_name': opt.dataset.name,
            'product_unit': opt.dataset.unit,
            'product_unit_price': opt.dataset.price,
        };
        for (const [name, value] of Object.entries(fields)) {
            const el = document.querySelector(`[name="${name}"]`);
            if (el) el.value = value ?? '';
        }
    });

    document.getElementById('saveNewCustomer')?.addEventListener('click', async function() {
        const errBox = document.getElementById('modal-errors');
        errBox.classList.add('d-none');
        const payload = {
            name: document.getElementById('m_name').value,
            company: document.getElementById('m_company').value,
            department: document.getElementById('m_department').value,
            street: document.getElementById('m_street').value,
            city: document.getElementById('m_city').value,
            zip: document.getElementById('m_zip').value,
            country: document.getElementById('m_country').value,
            phone: document.getElementById('m_phone').value,
            email: document.getElementById('m_email').value,
            vat_number: document.getElementById('m_vat_number').value,
        };
        if (!payload.name) {
            errBox.textContent = 'Name is required.';
            errBox.classList.remove('d-none');
            return;
        }
        const res = await fetch('{{ route("customers.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload),
        });
        if (!res.ok) {
            const err = await res.json();
            errBox.textContent = Object.values(err.errors ?? {}).flat().join(' ');
            errBox.classList.remove('d-none');
            return;
        }
        const customer = await res.json();
        const picker = document.getElementById('customer-picker');
        const option = new Option(customer.name + (customer.company ? ' — ' + customer.company : ''), customer.id, true, true);
        Object.assign(option.dataset, {
            name: customer.name,
            company: customer.company ?? '',
            department: customer.department ?? '',
            street: customer.street ?? '',
            city: customer.city ?? '',
            zip: customer.zip ?? '',
            country: customer.country ?? '',
            phone: customer.phone ?? '',
            email: customer.email ?? '',
            vat: customer.vat_number ?? ''
        });
        picker.add(option);
        picker.dispatchEvent(new Event('change'));
        bootstrap.Modal.getInstance(document.getElementById('newCustomerModal')).hide();
    });

    function submitPreview() {
        const editForm    = document.getElementById('edit-form');
        const previewForm = document.getElementById('preview-form');

        // Clear previous hidden inputs
        previewForm.querySelectorAll('input[type="hidden"]:not([name="_token"])').forEach(el => el.remove());

        // Copy all field values except _method (no PUT spoofing)
        new FormData(editForm).forEach((value, key) => {
            if (key === '_method' || key === '_token') return;
            const input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = key;
            input.value = value;
            previewForm.appendChild(input);
        });

        previewForm.submit();
    }
</script>
@endsection