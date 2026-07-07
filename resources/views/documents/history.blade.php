@extends('layouts.auth')

@section('title', 'Document History')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h4 class="fw-semibold mb-0">Document History</h4>
        </div>
    </div>

    {{-- Filter by type --}}
    <div class="d-flex gap-2 flex-wrap mb-4">
        <a href="{{ route('documents.history') }}"
           class="btn btn-sm {{ is_null($selectedSlug) ? 'btn-primary' : 'btn-outline-secondary' }}">
            All
        </a>
        @foreach($types as $t)
            <a href="{{ route('documents.history', ['type' => $t->slug]) }}"
               class="btn btn-sm {{ $selectedSlug === $t->slug ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $t->name }}
            </a>
        @endforeach
    </div>

    @if($documents->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center text-muted py-5">
                No documents generated yet.
                <a href="{{ route('dashboard') }}">Go to the dashboard</a> to create one.
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Title</th>
                            <th>Type</th>
                            <th>Reference</th>
                            <th>Created</th>
                            <th class="pe-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $doc)
                        <tr>
                            <td class="ps-3 fw-medium">{{ $doc->title }}</td>
                            <td>
                                <span class="badge text-bg-primary">{{ $doc->documentType->name }}</span>
                            </td>
                            <td class="text-muted">{{ $doc->reference ?? '—' }}</td>
                            <td class="text-muted small">{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                            <td class="pe-3 text-end">
                                <a href="{{ route('documents.show', $doc) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($documents->hasPages())
            <div class="mt-3">
                {{ $documents->links() }}
            </div>
        @endif
    @endif

</div>
@endsection
