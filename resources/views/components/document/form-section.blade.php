{{--
    x-document.form-section
    Renders a single form.json section (card with fields).
    Props:
      $section – array with 'section', 'fields', and optional 'i18n_section'
      $prefill – array of prefilled values
--}}
@props(['section', 'prefill' => []])

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold"
         @if(!empty($section['i18n_section'])) data-i18n="{{ $section['i18n_section'] }}" @endif>
        {{ $section['section'] }}
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($section['fields'] as $field)
            <div class="col-md-6">
                <label class="form-label fw-medium"
                       @if(!empty($field['i18n_label'])) data-i18n="{{ $field['i18n_label'] }}" @endif>
                    {{ $field['label'] ?? ucfirst(str_replace('_', ' ', $field['name'])) }}
                    @if(!empty($field['required']) && empty($field['auto']))<span class="text-danger">*</span>@endif
                </label>

                @php $val = $prefill[$field['name']] ?? old($field['name'], ''); @endphp

                @if(!empty($field['auto']))
                    {{-- Auto-generated number: shown as read-only, submitted as hidden --}}
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted" title="Auto-generated">
                            <i class="bi bi-magic"></i>
                        </span>
                        <input type="text" class="form-control bg-light text-muted fst-italic"
                               value="{{ $val ?: 'Will be generated on save' }}"
                               readonly tabindex="-1">
                    </div>
                    {{-- Always submit the value — if editing, keep the existing number --}}
                    <input type="hidden" name="{{ $field['name'] }}" value="{{ $val }}">

                @elseif($field['type'] === 'textarea')
                    <textarea name="{{ $field['name'] }}" class="form-control" rows="3"
                        {{ !empty($field['required']) ? 'required' : '' }}>{{ $val }}</textarea>

                @elseif($field['type'] === 'date')
                    <input type="date" name="{{ $field['name'] }}" class="form-control"
                        value="{{ $val ?: date('Y-m-d') }}"
                        {{ !empty($field['required']) ? 'required' : '' }}>

                @elseif(in_array($field['type'], ['number', 'currency']))
                    <input type="number" name="{{ $field['name'] }}" class="form-control"
                        step="{{ $field['type'] === 'currency' ? '0.01' : '1' }}"
                        min="0" value="{{ $val }}"
                        {{ !empty($field['required']) ? 'required' : '' }}>

                @else
                    <input type="{{ $field['type'] }}" name="{{ $field['name'] }}"
                        class="form-control" value="{{ $val }}"
                        {{ !empty($field['required']) ? 'required' : '' }}>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
