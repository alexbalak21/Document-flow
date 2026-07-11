{{--
    x-document.import-export-panel
    The collapsible Import/Export JSON card on the create form.
    Props:
      $type – DocumentType model
--}}
@props(['type'])

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center"
         style="cursor:pointer;" onclick="toggleImportPanel()">
        <span class="fw-semibold">
            <i class="bi bi-arrow-left-right me-2 text-info"></i>Import / Export JSON
        </span>
        <i class="bi bi-chevron-down" id="import-chevron"></i>
    </div>
    <div id="import-panel" style="display:none;">
        <div class="card-body">
            <div class="row g-3">
                {{-- Download blank model --}}
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <div class="fw-medium small mb-1">
                            <i class="bi bi-download me-1 text-primary"></i>Download blank model
                        </div>
                        <p class="text-muted small mb-2">
                            Get an empty JSON template showing all fields for this document type.
                        </p>
                        <a href="{{ route('export.document.model', $type->slug) }}"
                           class="btn btn-sm btn-outline-primary">
                            Download {{ $type->name }} model
                        </a>
                    </div>
                </div>

                {{-- Upload JSON file --}}
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <div class="fw-medium small mb-1">
                            <i class="bi bi-upload me-1 text-success"></i>Import from file
                        </div>
                        <p class="text-muted small mb-2">Upload a filled JSON file to auto-fill the form.</p>
                        <input type="file" id="json-file-input" accept=".json" class="form-control form-control-sm mb-2">
                        <button type="button" class="btn btn-sm btn-success" onclick="importFromFile()">
                            Fill form from file
                        </button>
                    </div>
                </div>

                {{-- Paste JSON text --}}
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <div class="fw-medium small mb-1">
                            <i class="bi bi-clipboard me-1 text-warning"></i>Paste JSON
                        </div>
                        <textarea id="json-text-input" class="form-control form-control-sm mb-2"
                            rows="3" placeholder='{"customer_name":"John","quote_number":"Q-001",...}'></textarea>
                        <button type="button" class="btn btn-sm btn-warning" onclick="importFromText()">
                            Fill form from text
                        </button>
                    </div>
                </div>
            </div>
            <div id="import-error" class="alert alert-danger mt-2 py-2 d-none small"></div>
            <div id="import-success" class="alert alert-success mt-2 py-2 d-none small"></div>
        </div>
    </div>
</div>
