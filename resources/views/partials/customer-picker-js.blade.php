{{-- Shared JS for customer & product pickers, new-customer modal --}}
{{-- Props: $storeCustomerRoute --}}
<script>
// ── Customer picker ────────────────────────────────────────────────────────
document.getElementById('customer-picker')?.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    if (!opt.value) return;
    const map = {
        customer_name:       opt.dataset.name,
        customer_company:    opt.dataset.company,
        customer_department: opt.dataset.department,
        customer_street:     opt.dataset.street,
        customer_city:       opt.dataset.city,
        customer_zip:        opt.dataset.zip,
        customer_country:    opt.dataset.country,
        customer_phone:      opt.dataset.phone,
        customer_email:      opt.dataset.email,
        customer_vat_number: opt.dataset.vat,
    };
    for (const [name, value] of Object.entries(map)) {
        const el = document.querySelector(`[name="${name}"]`);
        if (el) el.value = value ?? '';
    }
});

// ── Product picker ─────────────────────────────────────────────────────────
document.getElementById('product-picker')?.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    if (!opt.value) return;
    const fields = {
        product_reference:  opt.dataset.reference,
        product_name:       opt.dataset.name,
        product_unit:       opt.dataset.unit,
        product_unit_price: opt.dataset.price,
    };
    for (const [name, value] of Object.entries(fields)) {
        const el = document.querySelector(`[name="${name}"]`);
        if (el) el.value = value ?? '';
    }
});

// ── New Customer AJAX modal ────────────────────────────────────────────────
document.getElementById('saveNewCustomer')?.addEventListener('click', async function () {
    const errBox = document.getElementById('modal-errors');
    errBox.classList.add('d-none');

    const payload = {
        name:       document.getElementById('m_name').value,
        company:    document.getElementById('m_company').value,
        department: document.getElementById('m_department').value,
        street:     document.getElementById('m_street').value,
        city:       document.getElementById('m_city').value,
        zip:        document.getElementById('m_zip').value,
        country:    document.getElementById('m_country').value,
        phone:      document.getElementById('m_phone').value,
        email:      document.getElementById('m_email').value,
        vat_number: document.getElementById('m_vat_number').value,
    };

    if (!payload.name) {
        errBox.textContent = 'Name is required.';
        errBox.classList.remove('d-none');
        return;
    }

    const res = await fetch('{{ route("customers.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify(payload),
    });

    if (!res.ok) {
        const err = await res.json();
        errBox.textContent = Object.values(err.errors ?? {}).flat().join(' ');
        errBox.classList.remove('d-none');
        return;
    }

    const customer = await res.json();
    const picker   = document.getElementById('customer-picker');
    const option   = new Option(
        customer.name + (customer.company ? ' — ' + customer.company : ''),
        customer.id, true, true
    );
    Object.assign(option.dataset, {
        name: customer.name, company: customer.company ?? '',
        department: customer.department ?? '', street: customer.street ?? '',
        city: customer.city ?? '', zip: customer.zip ?? '',
        country: customer.country ?? '', phone: customer.phone ?? '',
        email: customer.email ?? '', vat: customer.vat_number ?? '',
    });
    picker.add(option);
    picker.dispatchEvent(new Event('change'));
    bootstrap.Modal.getInstance(document.getElementById('newCustomerModal')).hide();
});
</script>
