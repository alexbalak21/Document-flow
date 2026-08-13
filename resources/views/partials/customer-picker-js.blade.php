{{-- Shared JS for customer & product search-modal pickers, new-customer modal --}}
<script>
// ── Small debounce helper ────────────────────────────────────────────────
function debounce(fn, delay = 300) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

// ── Customer picker ──────────────────────────────────────────────────────
function fillCustomerFields(c) {
    const map = {
        customer_name:       c.name,
        customer_company:    c.company,
        customer_department: c.department,
        customer_street:     c.street,
        customer_city:       c.city,
        customer_zip:        c.zip,
        customer_country:    c.country,
        customer_phone:      c.phone,
        customer_email:      c.email,
        customer_vat_number: c.vat_number,
    };
    for (const [name, value] of Object.entries(map)) {
        const el = document.querySelector(`[name="${name}"]`);
        if (el) el.value = value ?? '';
    }
}

function selectCustomer(c) {
    document.getElementById('customer-picker').value = c.id;
    const label = document.getElementById('customer-picker-label');
    label.textContent = c.name + (c.company ? ' — ' + c.company : '');
    label.classList.remove('text-muted');
    document.getElementById('customer-picker-clear').style.display = '';
    fillCustomerFields(c);
    bootstrap.Modal.getInstance(document.getElementById('customerSearchModal'))?.hide();
}

function renderCustomerResults(customers) {
    const box = document.getElementById('customer-search-results');
    if (!customers.length) {
        box.innerHTML = '<div class="text-muted small text-center py-4">No customers found.</div>';
        return;
    }
    box.innerHTML = customers.map(c => `
        <button type="button" class="list-group-item list-group-item-action customer-result"
            data-id="${c.id}">
            <div class="fw-medium">${escapeHtml(c.name)}${c.company ? ' — ' + escapeHtml(c.company) : ''}</div>
            <div class="small text-muted">
                ${[c.email, c.city, c.vat_number].filter(Boolean).map(escapeHtml).join(' · ')}
            </div>
        </button>
    `).join('');

    box.querySelectorAll('.customer-result').forEach((btn, i) => {
        btn.addEventListener('click', () => selectCustomer(customers[i]));
    });
}

const searchCustomers = debounce(async function (q) {
    const box = document.getElementById('customer-search-results');
    box.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
    try {
        const res = await fetch(`{{ route('customers.list') }}?q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json' },
        });
        const customers = await res.json();
        renderCustomerResults(customers);
    } catch (e) {
        box.innerHTML = '<div class="text-danger small text-center py-4">Search failed. Please try again.</div>';
    }
}, 300);

document.getElementById('customer-search-input')?.addEventListener('input', function () {
    searchCustomers(this.value.trim());
});

document.getElementById('customerSearchModal')?.addEventListener('shown.bs.modal', function () {
    const input = document.getElementById('customer-search-input');
    input.value = '';
    input.focus();
    searchCustomers(''); // show recent/first customers by default
});

document.getElementById('customer-picker-clear')?.addEventListener('click', function () {
    document.getElementById('customer-picker').value = '';
    const label = document.getElementById('customer-picker-label');
    label.textContent = 'Search & select a customer…';
    label.classList.add('text-muted');
    this.style.display = 'none';
});

// ── Line items (multiple products) ───────────────────────────────────────
let lineItemIndex = document.querySelectorAll('#line-items-body .line-item-row').length;

function currencyFmt(n) {
    return (Number.isFinite(n) ? n : 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function addLineItem(item = {}) {
    const idx  = lineItemIndex++;
    const tbody = document.getElementById('line-items-body');
    const row = document.createElement('tr');
    row.className = 'line-item-row';
    row.innerHTML = `
        <td><input type="text" name="items[${idx}][reference]" class="form-control form-control-sm" value="${escapeAttr(item.reference)}"></td>
        <td><input type="text" name="items[${idx}][name]" class="form-control form-control-sm" value="${escapeAttr(item.name)}" required></td>
        <td><input type="text" name="items[${idx}][unit]" class="form-control form-control-sm" value="${escapeAttr(item.unit)}" placeholder="e.g. 1 plate"></td>
        <td><input type="number" name="items[${idx}][quantity]" class="form-control form-control-sm qty-input" value="${item.quantity ?? 1}" min="1" step="1" required></td>
        <td><input type="number" name="items[${idx}][unit_price]" class="form-control form-control-sm price-input" value="${item.unit_price ?? ''}" min="0" step="0.01" required></td>
        <td class="text-end line-total fw-medium">€ 0.00</td>
        <td><button type="button" class="btn btn-sm btn-outline-danger remove-line" title="Remove"><i class="bi bi-trash"></i></button></td>
    `;
    tbody.appendChild(row);
    wireLineItemRow(row);
    recalcLineItems();
}

function wireLineItemRow(row) {
    row.querySelectorAll('.qty-input, .price-input').forEach(el => {
        el.addEventListener('input', recalcLineItems);
    });
    row.querySelector('.remove-line').addEventListener('click', () => {
        row.remove();
        recalcLineItems();
    });
}

function recalcLineItems() {
    const rows = document.querySelectorAll('#line-items-body .line-item-row');
    document.getElementById('line-items-empty')?.classList.toggle('d-none', rows.length > 0);

    let subtotal = 0;
    rows.forEach(row => {
        const qty   = parseFloat(row.querySelector('.qty-input')?.value) || 0;
        const price = parseFloat(row.querySelector('.price-input')?.value) || 0;
        const lineTotal = qty * price;
        subtotal += lineTotal;
        const cell = row.querySelector('.line-total');
        if (cell) cell.textContent = '€ ' + currencyFmt(lineTotal);
    });

    const subtotalEl = document.getElementById('line-items-subtotal');
    if (subtotalEl) subtotalEl.textContent = 'Products subtotal: € ' + currencyFmt(subtotal);

    updateDiscountPreview(subtotal);
}

function updateDiscountPreview(subtotal) {
    const preview = document.getElementById('discount-preview');
    if (!preview) return;

    const type  = document.getElementById('discount_type')?.value;
    const value = parseFloat(document.getElementById('discount_value')?.value) || 0;

    if (!type || value <= 0) {
        preview.textContent = 'No discount applied.';
        return;
    }

    let amount = type === 'percent' ? subtotal * (value / 100) : value;
    amount = Math.max(0, Math.min(amount, subtotal));
    preview.innerHTML = `– € ${currencyFmt(amount)} &rarr; new subtotal: € ${currencyFmt(subtotal - amount)}`;
}

document.querySelectorAll('#line-items-body .line-item-row').forEach(wireLineItemRow);
recalcLineItems();

document.getElementById('add-manual-line')?.addEventListener('click', () => addLineItem());
document.getElementById('discount_type')?.addEventListener('change', () => recalcLineItems());
document.getElementById('discount_value')?.addEventListener('input', () => recalcLineItems());

function escapeAttr(str) {
    return String(str ?? '').replace(/"/g, '&quot;');
}

function selectProduct(p) {
    addLineItem({
        reference:  p.reference,
        name:       p.name,
        unit:       p.product_unit,
        quantity:   1,
        unit_price: p.unit_price,
    });
    bootstrap.Modal.getInstance(document.getElementById('productSearchModal'))?.hide();
}

function renderProductResults(products) {
    const box = document.getElementById('product-search-results');
    if (!products.length) {
        box.innerHTML = '<div class="text-muted small text-center py-4">No products found.</div>';
        return;
    }
    box.innerHTML = products.map(p => `
        <button type="button" class="list-group-item list-group-item-action product-result"
            data-id="${p.id}">
            <div class="fw-medium">${p.reference ? escapeHtml(p.reference) + ' — ' : ''}${escapeHtml(p.name)}</div>
            <div class="small text-muted">€${escapeHtml(p.formatted_price ?? p.unit_price ?? '')}</div>
        </button>
    `).join('');

    box.querySelectorAll('.product-result').forEach((btn, i) => {
        btn.addEventListener('click', () => selectProduct(products[i]));
    });
}

const searchProducts = debounce(async function (q) {
    const box = document.getElementById('product-search-results');
    box.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
    try {
        const res = await fetch(`{{ route('products.list') }}?q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json' },
        });
        const products = await res.json();
        renderProductResults(products);
    } catch (e) {
        box.innerHTML = '<div class="text-danger small text-center py-4">Search failed. Please try again.</div>';
    }
}, 300);

document.getElementById('product-search-input')?.addEventListener('input', function () {
    searchProducts(this.value.trim());
});

document.getElementById('productSearchModal')?.addEventListener('shown.bs.modal', function () {
    const input = document.getElementById('product-search-input');
    input.value = '';
    input.focus();
    searchProducts(''); // show recent/first products by default
});

// ── Shared helper ────────────────────────────────────────────────────────
function escapeHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, m => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[m]));
}

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
    selectCustomer(customer);
    bootstrap.Modal.getInstance(document.getElementById('newCustomerModal')).hide();
});
</script>
