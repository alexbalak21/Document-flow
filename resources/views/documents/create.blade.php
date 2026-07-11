@extends('layouts.auth')

@section('title', 'New ' . $type->name)

@section('content')
<div class="container py-4" style="max-width:800px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('documents.page', $type->slug) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="fw-semibold mb-0">New {{ $type->name }}</h4>

        @if(!empty($languages))
        <div class="ms-auto d-flex align-items-center gap-2">
            <i class="bi bi-translate text-muted"></i>
            <select id="lang-switcher" class="form-select form-select-sm" style="width:auto;">
                @foreach($languages as $lang)
                <option value="{{ $lang }}">
                    {{ $lang === 'en' ? '🇬🇧 English' : ($lang === 'fr' ? '🇫🇷 Français' : strtoupper($lang)) }}
                </option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    @if(!empty($languages))
    {{-- i18n strings for JS label switching --}}
    <script>
    const _i18n = @json($i18n);
    </script>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php $prefill = session('convert_data', []); @endphp

    {{-- ================================================================ --}}
    {{-- JSON IMPORT / EXPORT PANEL                                        --}}
    {{-- ================================================================ --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center"
             style="cursor:pointer;" onclick="toggleImportPanel()">
            <span class="fw-semibold">
                <i class="bi bi-arrow-left-right me-2 text-info"></i>Import / Export JSON
            </span>
            <i class="bi bi-chevron-down" id="import-chevron"></i>
        </div>
        <div id="import-panel" style="display:none;">
            <div class="card-body">
                <div class="row g-3">
                    {{-- Download blank model --}}
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="fw-medium small mb-1">
                                <i class="bi bi-download me-1 text-primary"></i>Download blank model
                            </div>
                            <p class="text-muted small mb-2">
                                Get an empty JSON template showing all fields for this document type.
                            </p>
                            <a href="{{ route('export.document.model', $type->slug) }}"
                               class="btn btn-sm btn-outline-primary">
                                Download {{ $type->name }} model
                            </a>
                        </div>
                    </div>

                    {{-- Upload JSON file --}}
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="fw-medium small mb-1">
                                <i class="bi bi-upload me-1 text-success"></i>Import from file
                            </div>
                            <p class="text-muted small mb-2">Upload a filled JSON file to auto-fill the form.</p>
                            <input type="file" id="json-file-input" accept=".json" class="form-control form-control-sm mb-2">
                            <button type="button" class="btn btn-sm btn-success" onclick="importFromFile()">
                                Fill form from file
                            </button>
                        </div>
                    </div>

                    {{-- Paste JSON text --}}
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="fw-medium small mb-1">
                                <i class="bi bi-clipboard me-1 text-warning"></i>Paste JSON
                            </div>
                            <textarea id="json-text-input" class="form-control form-control-sm mb-2"
                                rows="3" placeholder='{"customer_name":"John","quote_number":"Q-001",...}'></textarea>
                            <button type="button" class="btn btn-sm btn-warning" onclick="importFromText()">
                                Fill form from text
                            </button>
                        </div>
                    </div>
                </div>
                <div id="import-error" class="alert alert-danger mt-2 py-2 d-none small"></div>
                <div id="import-success" class="alert alert-success mt-2 py-2 d-none small"></div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('documents.store', $type->slug) }}">
        @csrf
        <input type="hidden" name="lang" id="form-lang" value="{{ $languages[0] ?? 'en' }}">

        {{-- ================================================================ --}}
        {{-- SHARED ENTITY PICKERS (auto-generated from manifest entities)    --}}
        {{-- ================================================================ --}}

        @if(isset($entities['customer']))
        @php $customers = $entityData['customer']; $prefillCustomerId = session('convert_customer_id'); @endphp
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
                        {{ $prefillCustomerId == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}{{ $c->company ? ' — ' . $c->company : '' }}
                    </option>
                    @endforeach
                </select>

                <div class="row g-2">
                    @php
                        $customerFields = [
                            ['customer_name',       'Full Name',            'text',  true],
                            ['customer_company',    'Company',              'text',  false],
                            ['customer_department', 'Department',           'text',  false],
                            ['customer_vat_number', 'VAT Number',           'text',  false],
                            ['customer_street',     'Street / Address',     'text',  false],
                            ['customer_city',       'City',                 'text',  false],
                            ['customer_zip',        'ZIP',                  'text',  false],
                            ['customer_country',    'Country',              'text',  false],
                            ['customer_phone',      'Phone',                'tel',   false],
                            ['customer_email',      'Email',                'email', false],
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

        @if(isset($entities['product']))
        @php $products = $entityData['product']; @endphp
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
        @endif

        {{-- ================================================================ --}}
        {{-- TEMPLATE-SPECIFIC FORM SECTIONS (from form.json)                 --}}
        {{-- ================================================================ --}}
        @foreach($form as $section)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"
                 @if(!empty($section['i18n_section'])) data-i18n="{{ $section['i18n_section'] }}" @endif>
                {{ $section['section'] }}
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($section['fields'] as $field)
                    <div class="col-md-6">
                        <label class="form-label fw-medium"
                               @if(!empty($field['i18n_label'])) data-i18n="{{ $field['i18n_label'] }}" @endif>
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
            <button type="submit" class="btn btn-success">
                <i class="bi bi-floppy me-1"></i>Save {{ $type->name }}
            </button>
            <button type="submit"
                formaction="{{ route('documents.preview', $type->slug) }}"
                formtarget="_blank"
                class="btn btn-outline-primary">
                <i class="bi bi-eye me-1"></i>Preview
            </button>
            <a href="{{ route('documents.page', $type->slug) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>

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
// ── Customer picker: auto-fill fields ──────────────────────────────────────
document.getElementById('customer-picker')?.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    if (!opt.value) return;
    const map = {
        'customer_name':       opt.dataset.name,
        'customer_company':    opt.dataset.company,
        'customer_department': opt.dataset.department,
        'customer_street':     opt.dataset.street,
        'customer_city':       opt.dataset.city,
        'customer_zip':        opt.dataset.zip,
        'customer_country':    opt.dataset.country,
        'customer_phone':      opt.dataset.phone,
        'customer_email':      opt.dataset.email,
        'customer_vat_number': opt.dataset.vat,
    };
    for (const [name, value] of Object.entries(map)) {
        const el = document.querySelector(`[name="${name}"]`);
        if (el) el.value = value ?? '';
    }
});

// ── Product picker: auto-fill fields ───────────────────────────────────────
document.getElementById('product-picker')?.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    if (!opt.value) return;
    const fields = {
        'product_reference':  opt.dataset.reference,
        'product_name':       opt.dataset.name,
        'product_unit':       opt.dataset.unit,
        'product_unit_price': opt.dataset.price,
    };
    for (const [name, value] of Object.entries(fields)) {
        const el = document.querySelector(`[name="${name}"]`);
        if (el) el.value = value ?? '';
    }
});

// ── New customer AJAX save ─────────────────────────────────────────────────
document.getElementById('saveNewCustomer')?.addEventListener('click', async function () {
    const errBox = document.getElementById('modal-errors');
    errBox.classList.add('d-none');

    const payload = {
        name:       document.getElementById('m_name').value,
        company:    document.getElementById('m_company').value,
        department: document.getElementById('m_department').value,
        street:     document.getElementById('m_street').value,
        city:       document.getElementById('m_city').value,
        zip:        document.getElementById('m_zip').value,
        country:    document.getElementById('m_country').value,
        phone:      document.getElementById('m_phone').value,
        email:      document.getElementById('m_email').value,
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
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
    const picker   = document.getElementById('customer-picker');
    const option   = new Option(
        customer.name + (customer.company ? ' — ' + customer.company : ''),
        customer.id, true, true
    );
    Object.assign(option.dataset, {
        name: customer.name, company: customer.company ?? '',
        department: customer.department ?? '', street: customer.street ?? '',
        city: customer.city ?? '', zip: customer.zip ?? '',
        country: customer.country ?? '', phone: customer.phone ?? '',
        email: customer.email ?? '', vat: customer.vat_number ?? '',
    });
    picker.add(option);
    picker.dispatchEvent(new Event('change'));
    bootstrap.Modal.getInstance(document.getElementById('newCustomerModal')).hide();
});

// ── Import/Export Panel ────────────────────────────────────────────────────
function toggleImportPanel() {
    const panel   = document.getElementById('import-panel');
    const chevron = document.getElementById('import-chevron');
    const open    = panel.style.display === 'none';
    panel.style.display   = open ? 'block' : 'none';
    chevron.className     = open ? 'bi bi-chevron-up' : 'bi bi-chevron-down';
}

async function importFromFile() {
    const file = document.getElementById('json-file-input').files[0];
    if (!file) { showImportError('Please select a JSON file first.'); return; }

    const text = await file.text();
    await sendImport(null, text);
}

async function importFromText() {
    const text = document.getElementById('json-text-input').value.trim();
    if (!text) { showImportError('Please paste JSON text first.'); return; }
    await sendImport(null, text);
}

async function sendImport(file, text) {
    const formData = new FormData();
    if (text) formData.append('json_text', text);

    const res = await fetch('{{ route("import.document", $type->slug) }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: formData,
    });

    const json = await res.json();

    if (json.error) { showImportError(json.error); return; }

    let filled = 0;
    for (const [name, value] of Object.entries(json.fields)) {
        const el = document.querySelector(`[name="${name}"]`);
        if (el) { el.value = value ?? ''; filled++; }
    }

    showImportSuccess(`${filled} field(s) filled from JSON.`);
}

function showImportError(msg) {
    const el = document.getElementById('import-error');
    el.textContent = msg;
    el.classList.remove('d-none');
    document.getElementById('import-success').classList.add('d-none');
}

function showImportSuccess(msg) {
    const el = document.getElementById('import-success');
    el.textContent = msg;
    el.classList.remove('d-none');
    document.getElementById('import-error').classList.add('d-none');
}

// ── Language switcher ──────────────────────────────────────────────────────
(function () {
    const switcher = document.getElementById('lang-switcher');
    if (!switcher || typeof _i18n === 'undefined') return;

    function applyLang(lang) {
        const strings = _i18n[lang] || _i18n['en'] || {};

        // Update hidden form field so the selected language is submitted
        const hidden = document.getElementById('form-lang');
        if (hidden) hidden.value = lang;

        // Update section headers and field labels that have data-i18n attributes
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (strings[key] !== undefined) {
                // Preserve the red asterisk for required fields (it's a child span)
                const asterisk = el.querySelector('span.text-danger');
                el.textContent = strings[key];
                if (asterisk) el.appendChild(asterisk);
            }
        });
    }

    switcher.addEventListener('change', function () {
        applyLang(this.value);
    });

    // Apply on load to set initial state consistently
    applyLang(switcher.value);
})();
</script>
@endsection
