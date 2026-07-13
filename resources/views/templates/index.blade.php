@extends('layouts.auth')

@section('title', 'Templates')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-semibold mb-0">Templates</h4>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('templates.install') }}">
                @csrf
                <button class="btn btn-outline-primary">
                    <i class="bi bi-download me-1"></i>Scan &amp; Install
                </button>
            </form>
            <form method="POST" action="{{ route('templates.rescan') }}"
                  onsubmit="return confirm('This will update all templates and regenerate every saved document snapshot. Continue?')">
                @csrf
                <button class="btn btn-warning">
                    <i class="bi bi-arrow-clockwise me-1"></i>Rescan &amp; Update All
                </button>
            </form>
        </div>
    </div>

    {{-- Upload ZIP package --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-upload me-2 text-primary"></i>Upload Template Package (.zip)
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('templates.upload') }}" enctype="multipart/form-data">
                @csrf
                <div class="d-flex gap-3 align-items-end">
                    <div class="flex-grow-1">
                        <label class="form-label fw-medium small">
                            Select a template package ZIP
                        </label>
                        <input type="file" name="package" class="form-control" accept=".zip" required>
                        <div class="form-text">
                            ZIP must contain: <code>manifest.json</code>, <code>template.html</code>,
                            <code>form.json</code>, <code>style.css</code>.
                            Existing templates with the same slug will be updated automatically.
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cloud-upload me-1"></i>Upload &amp; Install
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
    @endif

    @if(session('error'))
        <x-ui.alert type="danger">{{ session('error') }}</x-ui.alert>
    @endif

    @if(session('errors_list') && count(session('errors_list')))
        <x-ui.alert type="warning">
            <strong>Issues:</strong>
            <ul class="mb-0 mt-1">
                @foreach(session('errors_list') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    @if($templates->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center text-muted py-5">
                No templates installed yet. Upload a package above or click
                <strong>Scan &amp; Install</strong>.
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach($templates as $template)
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi {{ $template->icon_display }} text-primary fs-5"></i>
                                <h6 class="fw-semibold mb-0">{{ $template->name }}</h6>
                            </div>
                            @if($template->active)
                                <span class="badge text-bg-success">Active</span>
                            @else
                                <span class="badge text-bg-secondary">Inactive</span>
                            @endif
                        </div>
                        <div class="text-muted small mb-1">
                            v{{ $template->version }} · <code>{{ $template->slug }}</code>
                            @if($template->sidebar_group)
                                · <span class="badge text-bg-light text-dark border">{{ $template->sidebar_group }}</span>
                            @endif
                        </div>
                        <p class="small text-muted mb-3">{{ $template->description ?? '—' }}</p>

                        {{-- Accent color picker --}}
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <label class="form-label fw-medium small mb-0" style="white-space:nowrap;">
                                Accent color
                            </label>
                            <input type="color"
                                   class="form-control form-control-color"
                                   style="width:40px; height:32px; padding:2px; cursor:pointer;"
                                   value="{{ $template->accent_color ?? '#1a56db' }}"
                                   data-template-id="{{ $template->id }}"
                                   onchange="updateAccentColor(this)"
                                   title="Change accent color">
                            <span class="font-monospace small text-muted"
                                  id="color-label-{{ $template->id }}">
                                {{ $template->accent_color ?? '#1a56db' }}
                            </span>
                            <span class="small d-none" id="color-status-{{ $template->id }}"></span>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">

                            {{-- Document count --}}
                            {{-- Preview template with placeholders --}}
                            <a href="{{ route('templates.preview', $template) }}"
                               target="_blank"
                               class="btn btn-sm btn-outline-info"
                               title="Preview template with placeholders">
                                <i class="bi bi-eye me-1"></i>Preview
                            </a>

                            <a href="{{ route('documents.page', $template->slug) }}"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-file-earmark me-1"></i>
                                {{ $template->documents_count }} doc(s)
                            </a>

                            {{-- Regenerate snapshots --}}
                            @if($template->documents_count > 0)
                            <form method="POST"
                                  action="{{ route('templates.regenerate', $template) }}"
                                  onsubmit="return confirm('Regenerate all {{ $template->documents_count }} snapshot(s)?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-arrow-repeat me-1"></i>Regenerate
                                </button>
                            </form>
                            @endif

                            {{-- Enable / Disable --}}
                            <form method="POST" action="{{ route('templates.toggle', $template) }}">
                                @csrf
                                <button type="submit"
                                    class="btn btn-sm {{ $template->active ? 'btn-outline-secondary' : 'btn-outline-success' }}"
                                    title="{{ $template->active ? 'Disable template' : 'Enable template' }}">
                                    <i class="bi {{ $template->active ? 'bi-pause-circle' : 'bi-play-circle' }} me-1"></i>
                                    {{ $template->active ? 'Disable' : 'Enable' }}
                                </button>
                            </form>

                            {{-- Delete (only if no documents) --}}
                            @if($template->documents_count === 0)
                            <form method="POST" action="{{ route('templates.destroy', $template) }}"
                                  onsubmit="return confirm('Delete \\&quot;{{ $template->name }}\\&quot; and remove all files from disk? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete template">
                                    <i class="bi bi-trash me-1"></i>Delete
                                </button>
                            </form>
                            @else
                            <button class="btn btn-sm btn-outline-danger disabled"
                                title="Cannot delete — {{ $template->documents_count }} document(s) exist. Disable instead.">
                                <i class="bi bi-trash me-1"></i>Delete
                            </button>
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

    {{-- Multi-language guide --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-translate me-2 text-info"></i>Multi-language Templates
        </div>
        <div class="card-body">
            <p class="small text-muted mb-2">
                To add a language variant, create a separate template package with a different slug.
                Use <code>sidebar_group</code> in <code>manifest.json</code> to group them together in the sidebar.
            </p>
            <pre class="bg-light rounded p-3 small mb-0">{
    "name": "Devis (FR)",
    "slug": "quote-fr",
    "icon": "bi-file-earmark-text",
    "sidebar_label": "Devis (FR)",
    "sidebar_group": "Ventes",
    "entities": ["customer", "product"]
}</pre>
        </div>
    </div>

</div>

<script>
// Debounce: wait 600ms after the user stops dragging before saving
const _colorTimers = {};

function updateAccentColor(input) {
    const id    = input.dataset.templateId;
    const color = input.value;
    const label  = document.getElementById('color-label-' + id);
    const status = document.getElementById('color-status-' + id);

    // Update the hex label instantly while dragging
    if (label) label.textContent = color;

    // Debounce the actual save
    clearTimeout(_colorTimers[id]);
    _colorTimers[id] = setTimeout(() => _saveColor(id, color, status), 600);
}

async function _saveColor(id, color, status) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    if (status) {
        status.textContent = 'Saving…';
        status.className   = 'small text-muted';
        status.classList.remove('d-none');
    }

    try {
        const res = await fetch(`/templates/${id}/color`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ accent_color: color }),
        });

        if (!res.ok) {
            // Surface the HTTP error (422 validation, 419 CSRF, etc.)
            const text = await res.text();
            throw new Error(`HTTP ${res.status} — ${text.substring(0, 120)}`);
        }

        const json = await res.json();

        if (status) {
            status.textContent = json.success ? '✓ ' + json.message : 'Error saving color.';
            status.className   = json.success ? 'small text-success' : 'small text-danger';
            setTimeout(() => status.classList.add('d-none'), 3000);
        }
    } catch (e) {
        if (status) {
            status.textContent = 'Error: ' + e.message;
            status.className   = 'small text-danger d-block';
        }
        console.error('updateAccentColor failed:', e);
    }
}
</script>
@endsection