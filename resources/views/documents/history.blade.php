@extends('layouts.auth')

@section('title', 'Document History')

@section('content')
<div class="container py-4">

    <x-ui.page-header title="Document History" :backRoute="route('dashboard')" />

    <x-ui.alert-flash />

    {{-- Filter tabs --}}
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
        <x-ui.empty-state message="No documents yet.">
            <a href="{{ route('dashboard') }}">Create one</a>.
        </x-ui.empty-state>
    @else
        <div class="card border-0 shadow-sm" style="overflow:visible;">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Title</th>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Version</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="pe-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $doc)
                        <x-document.table-row :doc="$doc" />
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($documents->hasPages())
            <div class="mt-3">{{ $documents->links() }}</div>
        @endif
    @endif

</div>
@endsection
