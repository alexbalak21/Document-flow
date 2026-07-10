@extends('layouts.auth')

@section('title', 'Templates')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-semibold mb-0">Templates</h4>
        <div class="d-flex gap-2">
            {{-- Scan & Install: only adds NEW templates --}}
            <form method="POST" action="{{ route('templates.install') }}">
                @csrf
                <button class="btn btn-outline-primary">
                    <i class="bi bi-download me-1"></i>Scan &amp; Install
                </button>
            </form>

            {{-- Rescan & Update: updates ALL templates + regenerates all snapshots --}}
            <form method="POST" action="{{ route('templates.rescan') }}"
                  onsubmit="return confirm('This will update all template records and regenerate every saved document snapshot. Continue?')">
                @csrf
                <button class="btn btn-warning">
                    <i class="bi bi-arrow-clockwise me-1"></i>Rescan &amp; Update All
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('errors_list') && count(session('errors_list')))
        <div class="alert alert-warning">
            <strong>Some issues occurred:</strong>
            <ul class="mb-0 mt-1">
                @foreach(session('errors_list') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($templates->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center text-muted py-5">
                No templates installed yet.
                Click <strong>Scan &amp; Install</strong> to load templates from
                <code>storage/app/templates/</code>.
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach($templates as $template)
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="fw-semibold mb-0">{{ $template->name }}</h6>
                            @if($template->active)
                                <span class="badge text-bg-success">Active</span>
                            @else
                                <span class="badge text-bg-secondary">Inactive</span>
                            @endif
                        </div>
                        <div class="text-muted small mb-1">
                            v{{ $template->version }} · <code>{{ $template->slug }}</code>
                        </div>
                        <p class="small text-muted mb-3">{{ $template->description ?? '—' }}</p>

                        <div class="d-flex gap-2 flex-wrap">
                            {{-- Link to document page --}}
                            <a href="{{ route('documents.page', $template->slug) }}"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-file-earmark me-1"></i>
                                {{ $template->documents_count }} doc(s)
                            </a>

                            {{-- Regenerate snapshots for this template only --}}
                            @if($template->documents_count > 0)
                            <form method="POST"
                                  action="{{ route('templates.regenerate', $template) }}"
                                  onsubmit="return confirm('Regenerate all {{ $template->documents_count }} {{ $template->name }} snapshot(s) with the current template?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-arrow-repeat me-1"></i>Regenerate
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                    <div class="card-footer bg-white border-0 pt-0">
                        <small class="text-muted">
                            <i class="bi bi-folder me-1"></i>
                            <code>storage/app/templates/{{ $template->slug }}/</code>
                        </small>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
