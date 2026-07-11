{{--
    x-ui.import-json-modal
    Generic "Import from JSON" modal.
    Props:
      $modalId      – HTML id for the modal (e.g. "importCustomerModal")
      $title        – Modal heading
      $errorDivId   – id for the error alert div
      $successDivId – id for the success alert div
      $fileInputId  – id for the file input
      $textareaId   – id for the paste textarea
      $placeholder  – textarea placeholder JSON snippet
      $onImport     – JS function name to call on the Import button click
--}}
@props([
    'modalId', 'title',
    'errorDivId', 'successDivId',
    'fileInputId', 'textareaId',
    'placeholder' => '{"key":"value"}',
    'onImport',
])
<div class="modal fade" id="{{ $modalId }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <x-ui.alert type="danger"  :id="$errorDivId"   :hidden="true" :small="true" />
                <x-ui.alert type="success" :id="$successDivId" :hidden="true" :small="true" />

                <div class="mb-3">
                    <label class="form-label fw-medium small">Upload JSON file</label>
                    <input type="file" id="{{ $fileInputId }}" accept=".json" class="form-control form-control-sm">
                </div>
                <div class="text-center text-muted small mb-3">— or —</div>
                <div class="mb-3">
                    <label class="form-label fw-medium small">Paste JSON</label>
                    <textarea id="{{ $textareaId }}" class="form-control form-control-sm" rows="5"
                        placeholder="{{ $placeholder }}"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="{{ $onImport }}()">
                    <i class="bi bi-upload me-1"></i>Import & Create
                </button>
            </div>
        </div>
    </div>
</div>
