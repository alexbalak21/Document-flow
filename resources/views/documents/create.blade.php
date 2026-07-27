@extends('layouts.auth')

@section('title', 'New ' . $type->name)

@section('content')
<div class="container py-4" style="max-width:800px;">

    {{-- Page header with optional language switcher --}}
    <x-ui.page-header :title="'New ' . $type->name"
                      :backRoute="route('documents.page', $type->slug)">
        <x-document.lang-switcher :languages="$languages ?? []" :i18n="$i18n ?? []" />
    </x-ui.page-header>

    <x-ui.alert-flash />

    @php $prefill = session('convert_data', []); @endphp

    {{-- Import / Export panel --}}
    <x-document.import-export-panel :type="$type" />

    <form method="POST" action="{{ route('documents.store', $type->slug) }}">
        @csrf
        <input type="hidden" name="lang" id="form-lang" value="{{ ($languages ?? [])[0] ?? 'en' }}">

        {{-- Customer picker --}}
        @if(isset($entities['customer']))
            <x-document.customer-picker
                :customers="$entityData['customer']"
                :prefill="$prefill"
                :selectedId="session('convert_customer_id')"
            />
        @endif

        {{-- Product picker --}}
        @if(isset($entities['product']))
            <x-document.product-picker
                :products="$entityData['product']"
                :prefill="$prefill"
            />
        @endif

        {{-- Template-specific sections from form.json --}}
        @foreach($form as $section)
            <x-document.form-section :section="$section" :prefill="$prefill" :type-slug="$type->slug" />
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

{{-- New Customer modal (only when customer entity is active) --}}
@if(isset($entities['customer']))
    <x-document.new-customer-modal />
@endif

@include('partials.customer-picker-js')
@include('partials.import-export-js')
@if(!empty($languages))
    @include('partials.lang-switcher-js')
@endif
@endsection