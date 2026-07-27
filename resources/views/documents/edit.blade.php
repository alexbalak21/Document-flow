@extends('layouts.auth')

@section('title', 'Edit ' . $type->name)

@section('content')
<div class="container py-4" style="max-width:800px;">

    <x-ui.page-header
        :title="'Edit ' . $type->name"
        :backRoute="route('documents.show', $document)"
        :subtitle="$document->title . ' · v' . $document->version"
    >
        <span class="badge text-bg-secondary ms-1">Draft</span>
    </x-ui.page-header>

    <form method="POST" action="{{ route('documents.update', $document) }}" id="edit-form">
        @csrf
        @method('PUT')

        {{-- Customer picker --}}
        @if(isset($entities['customer']))
            <x-document.customer-picker
                :customers="$entityData['customer']"
                :prefill="$prefill"
                :selectedId="$customerId"
            />
        @endif

        {{-- Product picker --}}
        @if(isset($entities['product']))
            <x-document.product-picker
                :products="$entityData['product']"
                :prefill="$prefill"
            />
        @endif

        {{-- Template-specific sections --}}
        @foreach($form as $section)
            <x-document.form-section :section="$section" :prefill="$prefill" :type-slug="$type->slug" :document-id="$document->id" />
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

@if(isset($entities['customer']))
    <x-document.new-customer-modal />
@endif

@include('partials.customer-picker-js')

<script>
function submitPreview() {
    const editForm    = document.getElementById('edit-form');
    const previewForm = document.getElementById('preview-form');

    previewForm.querySelectorAll('input[type="hidden"]:not([name="_token"])').forEach(el => el.remove());

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