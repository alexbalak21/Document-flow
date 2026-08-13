{{--
    x-document.form-section
    Renders a single form.json section (card with fields).
    Props:
      $section    – array with 'section', 'fields', and optional 'i18n_section'
      $prefill    – array of prefilled values
      $typeSlug   – document type slug, used to call the number-uniqueness check endpoint
      $documentId – current document id when editing (excluded from the uniqueness check), null when creating
--}}
@props(['section', 'prefill' => [], 'typeSlug' => null, 'documentId' => null])

@php
    $isOptional = !empty($section['optional']);
    $sectionId  = 'section-' . \Illuminate\Support\Str::slug($section['section']);

    // If editing and any field in this section already has a saved value,
    // the section should start expanded and checked.
    $hasValue = false;
    if ($isOptional) {
        foreach ($section['fields'] as $field) {
            $existing = $prefill[$field['name']] ?? null;
            if ($existing !== null && $existing !== '') {
                $hasValue = true;
                break;
            }
        }
    }
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold d-flex align-items-center justify-content-between">
        <span @if(!empty($section['i18n_section'])) data-i18n="{{ $section['i18n_section'] }}" @endif>
            @if(!empty($section['icon']))<i class="bi {{ $section['icon'] }} me-1"></i>@endif
            {{ $section['section'] }}
        </span>
        @if($isOptional)
        <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch"
                   id="{{ $sectionId }}-toggle"
                   onchange="documentFlowToggleSection('{{ $sectionId }}', this.checked)"
                   {{ $hasValue ? 'checked' : '' }}>
        </div>
        @endif
    </div>
    <div class="card-body {{ $isOptional && !$hasValue ? 'd-none' : '' }}"
         id="{{ $sectionId }}-body">
        <div class="row g-3">
            @foreach($section['fields'] as $field)
            <div class="col-md-6">
                <label class="form-label fw-medium"
                       @if(!empty($field['i18n_label'])) data-i18n="{{ $field['i18n_label'] }}" @endif>
                    {{ $field['label'] ?? ucfirst(str_replace('_', ' ', $field['name'])) }}
                    @if(!empty($field['required']) && empty($field['auto']))<span class="text-danger">*</span>@endif
                </label>

                @php
                    $val      = $prefill[$field['name']] ?? old($field['name'], '');
                    $disabled = $isOptional && !$hasValue;
                @endphp

                @if(!empty($field['auto']))
                    @php
                        // If editing an existing document, a saved value means
                        // the number was already assigned — default to manual
                        // mode so the existing number is visibly editable.
                        // On a brand-new document, default to auto mode.
                        $autoFieldId  = 'auto-' . $field['name'];
                        $startManual  = $val !== '';
                        $checkUrl     = $typeSlug ? route('documents.check-number', $typeSlug) : null;
                    @endphp
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"
                              role="button"
                              title="Toggle auto-generate / manual"
                              onclick="documentFlowToggleAutoField('{{ $autoFieldId }}')">
                            <i class="bi {{ $startManual ? 'bi-magic' : 'bi-pencil' }}" id="{{ $autoFieldId }}-icon"></i>
                        </span>
                        <input type="text"
                               id="{{ $autoFieldId }}-input"    
                               name="{{ $field['name'] }}"
                               class="form-control {{ $startManual ? '' : 'bg-light text-muted fst-italic' }}"
                               value="{{ $val }}"
                               placeholder="{{ $startManual ? '' : 'Will be generated on save' }}"
                               {{ $startManual ? '' : 'readonly tabindex="-1"' }}
                               @if($checkUrl)
                                   data-unique-check="{{ $checkUrl }}"
                                   data-exclude-id="{{ $documentId }}"
                               @endif
                               autocomplete="off">
                    </div>
                    <div class="form-text" id="{{ $autoFieldId }}-hint">
                        {{ $startManual ? 'Editing manually — click the icon to auto-generate instead.' : 'Auto-generated on save — click the icon to type your own number.' }}
                    </div>
                    @error($field['name'])
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror

                @elseif($field['type'] === 'textarea')
                    <textarea name="{{ $field['name'] }}" class="form-control" rows="3"
                        {{ !empty($field['required']) ? 'required' : '' }}
                        {{ $disabled ? 'disabled' : '' }}>{{ $val }}</textarea>

                @elseif($field['type'] === 'date')
                    <input type="date" name="{{ $field['name'] }}" class="form-control"
                        value="{{ $val ?: date('Y-m-d') }}"
                        {{ !empty($field['required']) ? 'required' : '' }}
                        {{ $disabled ? 'disabled' : '' }}>

                @elseif(in_array($field['type'], ['number', 'currency']))
                    <input type="number" name="{{ $field['name'] }}" class="form-control"
                        step="{{ $field['type'] === 'currency' ? '0.01' : '1' }}"
                        min="0" value="{{ $val }}"
                        {{ !empty($field['required']) ? 'required' : '' }}
                        {{ $disabled ? 'disabled' : '' }}>

                @elseif($field['type'] === 'select')
                    <select name="{{ $field['name'] }}" class="form-select"
                        {{ !empty($field['required']) ? 'required' : '' }}
                        {{ $disabled ? 'disabled' : '' }}>
                        @if(empty($field['required']))
                            <option value="">—</option>
                        @endif
                        @foreach(($field['options'] ?? []) as $option)
                            <option value="{{ $option }}" {{ (string) $val === (string) $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>

                @else
                    <input type="{{ $field['type'] }}" name="{{ $field['name'] }}"
                        class="form-control" value="{{ $val }}"
                        {{ !empty($field['required']) ? 'required' : '' }}
                        {{ $disabled ? 'disabled' : '' }}>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

@if($isOptional)
    @once
    <script>
        function documentFlowToggleSection(sectionId, checked) {
            const body = document.getElementById(sectionId + '-body');
            if (!body) return;

            body.classList.toggle('d-none', !checked);
            body.querySelectorAll('input, select, textarea').forEach((el) => {
                el.disabled = !checked;
            });
        }
    </script>
    @endonce
@endif

@once
<script>
    function documentFlowToggleAutoField(fieldId) {
        const input = document.getElementById(fieldId + '-input');
        const icon  = document.getElementById(fieldId + '-icon');
        const hint  = document.getElementById(fieldId + '-hint');
        if (!input) return;

        const goingManual = input.readOnly; // currently auto -> switching to manual

        if (goingManual) {
            input.readOnly = false;
            input.classList.remove('bg-light', 'text-muted', 'fst-italic');
            input.placeholder = '';
            input.focus();
            icon.classList.remove('bi-pencil');
            icon.classList.add('bi-magic');
            hint.textContent = 'Editing manually — click the icon to auto-generate instead.';
        } else {
            input.readOnly = true;
            input.value = '';
            input.classList.add('bg-light', 'text-muted', 'fst-italic');
            input.placeholder = 'Will be generated on save';
            icon.classList.remove('bi-magic');
            icon.classList.add('bi-pencil');
            hint.textContent = 'Auto-generated on save — click the icon to type your own number.';
        }
    }
</script>
@endonce

@once
<script>
(function () {
    const debounceTimers = new WeakMap();

    function markState(input, state) {
        // state: 'checking' | 'unique' | 'taken' | 'idle'
        input.dataset.numberState = state;
        input.classList.remove('is-invalid', 'is-valid');

        let msg = input.parentElement.parentElement.querySelector('.js-number-check-msg');
        if (!msg) {
            msg = document.createElement('div');
            msg.className = 'js-number-check-msg small mt-1';
            input.closest('.input-group').insertAdjacentElement('afterend', msg);
        }

        if (state === 'checking') {
            msg.textContent = 'Checking availability…';
            msg.className = 'js-number-check-msg small mt-1 text-muted';
        } else if (state === 'taken') {
            input.classList.add('is-invalid');
            msg.textContent = 'This number is already used by another document of this type.';
            msg.className = 'js-number-check-msg small mt-1 text-danger fw-semibold';
        } else if (state === 'unique') {
            input.classList.add('is-valid');
            msg.textContent = 'Available.';
            msg.className = 'js-number-check-msg small mt-1 text-success';
        } else {
            msg.textContent = '';
        }
    }

    async function checkNumber(input) {
        const url = input.dataset.uniqueCheck;
        if (!url) return true;

        const value = input.value.trim();
        if (value === '') {
            markState(input, 'idle');
            return true;
        }

        markState(input, 'checking');

        const excludeId = input.dataset.excludeId || '';
        const params = new URLSearchParams({ number: value, exclude_id: excludeId });

        try {
            const res  = await fetch(url + '?' + params.toString(), {
                headers: { 'Accept': 'application/json' },
            });
            const json = await res.json();
            markState(input, json.unique ? 'unique' : 'taken');
            return json.unique;
        } catch (e) {
            // Network failure — don't block the user, server-side check
            // will still catch a real duplicate on submit.
            markState(input, 'idle');
            return true;
        }
    }

    document.addEventListener('input', function (e) {
        const input = e.target;
        if (!input.matches('[data-unique-check]')) return;

        clearTimeout(debounceTimers.get(input));
        debounceTimers.set(input, setTimeout(() => checkNumber(input), 400));
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-unique-check]').forEach((input) => {
            const form = input.closest('form');
            if (!form || form.dataset.uniqueGuardAttached) return;
            form.dataset.uniqueGuardAttached = '1';

            form.addEventListener('submit', async function (e) {
                // Re-entry guard: after a successful check we re-trigger the
                // submit programmatically via requestSubmit(), which fires
                // this same listener again — let that second pass through.
                if (form.dataset.uniqueVerified === '1') {
                    delete form.dataset.uniqueVerified;
                    return;
                }

                const fields = Array.from(form.querySelectorAll('[data-unique-check]'))
                    .filter(el => !el.disabled && el.value.trim() !== '');

                if (fields.length === 0) return; // nothing to check, let it submit

                e.preventDefault();
                const submitter = e.submitter; // preserves which button was clicked (Save vs Preview)

                const results = await Promise.all(fields.map(checkNumber));

                if (results.every(Boolean)) {
                    form.dataset.uniqueVerified = '1';
                    if (submitter && typeof form.requestSubmit === 'function') {
                        form.requestSubmit(submitter); // respects formaction/formtarget
                    } else {
                        form.submit();
                    }
                } else {
                    const firstBad = fields[results.indexOf(false)];
                    if (firstBad) {
                        firstBad.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstBad.focus();
                    }
                }
            });
        });
    });
})();
</script>
@endonce