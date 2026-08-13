{{--
    x-document.customer-picker
    Customer selection card used in both create and edit forms.
    Uses a searchable modal (instead of a <select>) so it scales to
    large customer lists.
    Props:
      $customers        – collection of Customer models (used to resolve
                           the initial label when $selectedId is set)
      $prefill          – array of prefilled field values
      $selectedId       – currently selected customer id (for edit)
      $showNewButton    – whether to show the "New Customer" modal trigger
--}}
@props([
    'customers',
    'prefill'       => [],
    'selectedId'    => null,
    'showNewButton' => true,
])

@php
$customerFields = [
    ['customer_name',       'Full Name',        'text',  true],
    ['customer_company',    'Company',          'text',  false],
    ['customer_department', 'Department',       'text',  false],
    ['customer_vat_number', 'VAT Number',       'text',  false],
    ['customer_street',     'Street / Address', 'text',  false],
    ['customer_city',       'City',             'text',  false],
    ['customer_zip',        'ZIP',              'text',  false],
    ['customer_country',    'Country',          'text',  false],
    ['customer_phone',      'Phone',            'tel',   false],
    ['customer_email',      'Email',            'email', false],
];

$selectedCustomer = $selectedId ? collect($customers)->firstWhere('id', $selectedId) : null;
$initialLabel = $selectedCustomer
    ? $selectedCustomer->name . ($selectedCustomer->company ? ' — ' . $selectedCustomer->company : '')
    : null;
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
        <span><i class="bi bi-person me-2 text-primary"></i>Customer</span>
        @if($showNewButton)
        <button type="button" class="btn btn-sm btn-outline-primary"
            data-bs-toggle="modal" data-bs-target="#newCustomerModal">
            <i class="bi bi-person-plus me-1"></i>New Customer
        </button>
        @endif
    </div>
    <div class="card-body">
        <label class="form-label fw-medium">Select existing customer</label>
        <div class="d-flex gap-2 mb-3">
            <button type="button" class="btn btn-outline-secondary flex-grow-1 text-start"
                data-bs-toggle="modal" data-bs-target="#customerSearchModal" id="customer-picker-btn">
                <i class="bi bi-search me-2 text-muted"></i>
                <span id="customer-picker-label" class="{{ $initialLabel ? '' : 'text-muted' }}">
                    {{ $initialLabel ?? 'Search & select a customer…' }}
                </span>
            </button>
            <button type="button" class="btn btn-outline-danger" id="customer-picker-clear"
                title="Clear selection" style="{{ $initialLabel ? '' : 'display:none' }}">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <input type="hidden" id="customer-picker" name="customer_id" value="{{ $selectedId }}">

        <div class="row g-2">
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

{{-- Customer search modal --}}
<div class="modal fade" id="customerSearchModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold"><i class="bi bi-person me-2"></i>Select Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="customer-search-input"
                        placeholder="Search by name, company, email, VAT number…" autocomplete="off">
                </div>
                <div id="customer-search-results" class="list-group">
                    <div class="text-muted small text-center py-4" id="customer-search-hint">
                        Start typing to search, or browse recent customers.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
