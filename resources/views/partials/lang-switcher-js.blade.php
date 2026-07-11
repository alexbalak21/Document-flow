{{-- Language switcher JS — requires _i18n to be defined before this partial --}}
<script>
(function () {
    const switcher = document.getElementById('lang-switcher');
    if (!switcher || typeof _i18n === 'undefined') return;

    function applyLang(lang) {
        const strings = _i18n[lang] || _i18n['en'] || {};
        const hidden  = document.getElementById('form-lang');
        if (hidden) hidden.value = lang;

        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (strings[key] !== undefined) {
                const asterisk = el.querySelector('span.text-danger');
                el.textContent = strings[key];
                if (asterisk) el.appendChild(asterisk);
            }
        });
    }

    switcher.addEventListener('change', function () { applyLang(this.value); });
    applyLang(switcher.value);
})();
</script>
