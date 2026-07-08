@extends('layouts.auth')

@section('title', 'New ' . $type->name)

@section('content')
<div class="container py-4" style="max-width:760px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="fw-semibold mb-0">New {{ $type->name }}</h4>
    </div>

    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    @php $prefill = session('convert_data', []); @endphp

    <form method="POST" action="{{ route('documents.store', $type->slug) }}">
        @csrf

        @foreach($form as $section)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                {{ $section['section'] }}
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($section['fields'] as $field)
                    <div class="col-md-6">
                        <label class="form-label fw-medium">
                            {{ $field['label'] ?? ucfirst(str_replace('_', ' ', $field['name'])) }}
                            @if(!empty($field['required']))<span class="text-danger">*</span>@endif
                        </label>

                        @php
                            $prefillValue = $prefill[$field['name']] ?? old($field['name'], '');
                        @endphp

                        @if($field['type'] === 'textarea')
                            <textarea name="{{ $field['name'] }}"
                                class="form-control" rows="3"
                                {{ !empty($field['required']) ? 'required' : '' }}>{{ $prefillValue }}</textarea>

                        @elseif($field['type'] === 'currency' || $field['type'] === 'number')
                            <input type="number" name="{{ $field['name'] }}"
                                class="form-control"
                                step="{{ $field['type'] === 'currency' ? '0.01' : '1' }}"
                                min="0"
                                value="{{ $prefillValue }}"
                                {{ !empty($field['required']) ? 'required' : '' }}>

                        @elseif($field['type'] === 'date')
                            <input type="date" name="{{ $field['name'] }}"
                                class="form-control"
                                value="{{ $prefillValue ?: date('Y-m-d') }}"
                                {{ !empty($field['required']) ? 'required' : '' }}>

                        @else
                            <input type="{{ $field['type'] }}" name="{{ $field['name'] }}"
                                class="form-control"
                                value="{{ $prefillValue }}"
                                {{ !empty($field['required']) ? 'required' : '' }}>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

        {{-- Product picker --}}
        @if($products->isNotEmpty())
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Quick-fill from Products</div>
            <div class="card-body">
                <select class="form-select" id="product-picker">
                    <option value="">— Pick a product to auto-fill —</option>
                    @foreach($products as $p)
                    <option value="{{ $p->reference }}"
                        data-name="{{ $p->name }}"
                        data-price="{{ number_format($p->price / 100, 2, '.', '') }}">
                        {{ $p->reference }} — {{ $p->name }} (€{{ number_format($p->price / 100, 2) }})
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif

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
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>

    </form>
</div>

<script>
document.getElementById('product-picker')?.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    if (!opt.value) return;
    const ref   = document.querySelector('[name="product_reference"]');
    const name  = document.querySelector('[name="product_name"]');
    const price = document.querySelector('[name="product_unit_price"]');
    if (ref)   ref.value   = opt.value;
    if (name)  name.value  = opt.dataset.name;
    if (price) price.value = opt.dataset.price;
});
</script>
@endsection
