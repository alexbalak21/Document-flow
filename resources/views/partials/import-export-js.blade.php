{{-- JS for the import/export JSON panel on create form --}}
{{-- Requires: $type->slug in scope when rendered --}}
<script>
function toggleImportPanel() {
    const panel   = document.getElementById('import-panel');
    const chevron = document.getElementById('import-chevron');
    const open    = panel.style.display === 'none';
    panel.style.display = open ? 'block' : 'none';
    chevron.className   = open ? 'bi bi-chevron-up' : 'bi bi-chevron-down';
}

async function importFromFile() {
    const file = document.getElementById('json-file-input').files[0];
    if (!file) { showImportError('Please select a JSON file first.'); return; }
    await sendImport(await file.text());
}

async function importFromText() {
    const text = document.getElementById('json-text-input').value.trim();
    if (!text) { showImportError('Please paste JSON text first.'); return; }
    await sendImport(text);
}

async function sendImport(text) {
    const formData = new FormData();
    formData.append('json_text', text);

    const res  = await fetch('{{ route("import.document", $type->slug) }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: formData,
    });
    const json = await res.json();
    if (json.error) { showImportError(json.error); return; }

    // Step 1 — Auto-activate optional sections that have data
    // Must happen BEFORE filling fields so inputs are enabled
    if (json.sections_to_activate && json.sections_to_activate.length > 0) {
        json.sections_to_activate.forEach(sectionId => {
            const toggle = document.getElementById(sectionId + '-toggle');
            if (toggle && !toggle.checked) {
                toggle.checked = true;
                documentFlowToggleSection(sectionId, true);
            }
        });
    }

    // Step 2 — Fill all fields
    let filled = 0;
    for (const [name, value] of Object.entries(json.fields)) {
        const el = document.querySelector(`[name="${name}"]`);
        if (el) {
            el.value = value ?? '';
            filled++;
        }
    }

    showImportSuccess(`${filled} field(s) filled from JSON.`);
}

function showImportError(msg) {
    document.getElementById('import-error').textContent = msg;
    document.getElementById('import-error').classList.remove('d-none');
    document.getElementById('import-success').classList.add('d-none');
}

function showImportSuccess(msg) {
    document.getElementById('import-success').textContent = msg;
    document.getElementById('import-success').classList.remove('d-none');
    document.getElementById('import-error').classList.add('d-none');
}
</script>