{{--
    x-document.customer-picker
    Customer selection card used in both create and edit forms.
    Props:
      $customers        – collection of Customer models
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
                {{ $selectedId == $c->id ? 'selected' : '' }}>
                {{ $c->name }}{{ $c->company ? ' — ' . $c->company : '' }}
            </option>
            @endforeach
        </select>

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
