{{--
    x-document.lang-switcher
    Language dropdown rendered in the page header area.
    Props:
      $languages – array of language codes (e.g. ['en', 'fr'])
      $i18n      – translations array keyed by lang code
--}}
@props(['languages', 'i18n' => []])

@if(!empty($languages))
<div class="ms-auto d-flex align-items-center gap-2">
    <i class="bi bi-translate text-muted"></i>
    <select id="lang-switcher" class="form-select form-select-sm" style="width:auto;">
        @foreach($languages as $lang)
        <option value="{{ $lang }}">
            {{ $lang === 'en' ? '🇬🇧 English' : ($lang === 'fr' ? '🇫🇷 Français' : strtoupper($lang)) }}
        </option>
        @endforeach
    </select>
</div>

<script>const _i18n = @json($i18n);</script>
@endif
