@extends('layouts.auth')

@section('title', $type->name)

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-semibold mb-0">{{ $type->name }}</h4>
        <p class="text-muted small mb-0">{{ $type->description ?? '' }}</p>
    </div>
    <span class="badge text-bg-secondary">v{{ $type->version }}</span>
</div>

{{-- ── Action cards ──────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

    {{-- Create new --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="rounded-3 bg-primary-subtle p-3 mb-3 align-self-start">
                    <i class="bi bi-plus-circle text-primary fs-4"></i>
                </div>
                <h6 class="fw-semibold">New {{ $type->name }}</h6>
                <p class="text-muted small flex-grow-1">
                    Start a blank {{ strtolower($type->name) }} from scratch.
                </p>
                <a href="{{ route('documents.create', $type->slug) }}"
                   class="btn btn-primary mt-2">
                    <i class="bi bi-plus-lg me-1"></i>Create {{ $type->name }}
                </a>
            </div>
        </div>
    </div>

    {{-- Convert from (only shown when a source type is available and has accepted docs) --}}
    @foreach($convertSources as $source)
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 border-start border-success border-3">
            <div class="card-body d-flex flex-column">
                <div class="rounded-3 bg-success-subtle p-3 mb-3 align-self-start">
                    <i class="bi bi-arrow-right-circle text-success fs-4"></i>
                </div>
                <h6 class="fw-semibold">Create from {{ $source['type']->name }}</h6>
                <p class="text-muted small flex-grow-1">
                    Convert an accepted {{ strtolower($source['type']->name) }} directly into a
                    {{ strtolower($type->name) }}. All customer and product data will be pre-filled.
                </p>
                @if($source['documents']->isEmpty())
                    <p class="text-muted small fst-italic mt-2 mb-0">
                        No accepted {{ strtolower($source['type']->name) }}s available yet.
                    </p>
                @else
                    <div class="mt-2">
                        <label class="form-label small fw-medium">
                            Select {{ $source['type']->name }}
                        </label>
                        <div class="input-group">
                            <select class="form-select form-select-sm"
                                    id="convert-select-{{ $source['type']->slug }}">
                                <option value="">— Pick one —</option>
                                @foreach($source['documents'] as $doc)
                                    <option value="{{ $doc->id }}">
                                        {{ $doc->reference ?? $doc->title }}
                                        @if($doc->customer) — {{ $doc->customer->name }} @endif
                                    </option>
                                @endforeach
                            </select>
                            <form method="POST" id="convert-form-{{ $source['type']->slug }}"
                                  action="">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm"
                                    onclick="return prepareConvert('{{ $source['type']->slug }}')">
                                    <i class="bi bi-arrow-right-circle"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach

</div>

{{-- ── Recent documents of this type ───────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-semibold text-muted text-uppercase mb-0" style="font-size:11px;letter-spacing:1px;">
        Recent {{ $type->name }}s
    </h6>
    <a href="{{ route('documents.history', ['type' => $type->slug]) }}"
       class="btn btn-sm btn-outline-secondary">View all</a>
</div>

@if($recentDocs->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center text-muted py-5">
            No {{ strtolower($type->name) }}s yet.
            <a href="{{ route('documents.create', $type->slug) }}">Create the first one.</a>
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Reference</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="pe-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentDocs as $doc)
                    <tr>
                        <td class="ps-3 fw-medium">{{ $doc->reference ?? $doc->title }}</td>
                        <td class="text-muted">
                            {{ $doc->customer?->name ?? '—' }}
                            @if($doc->customer?->company)
                                <span class="text-muted small">· {{ $doc->customer->company }}</span>
                            @endif
                        </td>
                        <td>
                            @php $color = \App\Models\Document::$statusColors[$doc->status] ?? 'secondary'; @endphp
                            <span class="badge text-bg-{{ $color }}">{{ ucfirst($doc->status) }}</span>
                        </td>
                        <td class="text-muted small">{{ $doc->created_at->format('d/m/Y') }}</td>
                        <td class="pe-3 text-end">
                            <a href="{{ route('documents.show', $doc) }}" target="_blank"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<script>
function prepareConvert(slug) {
    const select = document.getElementById('convert-select-' + slug);
    const form   = document.getElementById('convert-form-' + slug);
    const id     = select.value;
    if (!id) { alert('Please select a document to convert.'); return false; }
    form.action = '/documents/' + id + '/convert';
    return true;
}
</script>

@endsection
