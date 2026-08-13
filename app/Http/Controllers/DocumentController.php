<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Product;
use App\Models\CompanyAsset;
use App\Services\EntityResolver;
use App\Services\DocumentNumberService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(
        protected EntityResolver $entityResolver,
        protected DocumentNumberService $documentNumbers,
    ) {}

    // -------------------------------------------------------------------------
    // DOCUMENT TYPE LANDING PAGE
    // -------------------------------------------------------------------------

    public function page(string $slug)
    {
        $type = DocumentType::where('slug', $slug)->where('active', true)->firstOrFail();

        $convertMap  = ['invoice' => ['quote']];
        $sourceSlugs = $convertMap[$slug] ?? [];

        $convertSources = [];
        foreach ($sourceSlugs as $sourceSlug) {
            $sourceType = DocumentType::where('slug', $sourceSlug)->where('active', true)->first();
            if (! $sourceType) continue;

            $docs = Document::where('document_type_id', $sourceType->id)
                ->where('status', Document::STATUS_ACCEPTED)
                ->whereDoesntHave('convertedInvoice')
                ->with('customer')
                ->latest()
                ->get();

            $convertSources[] = ['type' => $sourceType, 'documents' => $docs];
        }

        $recentDocs = Document::where('document_type_id', $type->id)
            ->with(['customer', 'documentType'])
            ->latest()
            ->take(10)
            ->get();

        return view('documents.page', compact('type', 'convertSources', 'recentDocs'));
    }

    // -------------------------------------------------------------------------
    // CREATE FORM
    // -------------------------------------------------------------------------

    public function create(string $slug)
    {
        $type     = DocumentType::where('slug', $slug)->where('active', true)->firstOrFail();
        $manifest = json_decode(file_get_contents(
            storage_path('app/templates/' . $slug . '/manifest.json')
        ), true);

        $form       = json_decode(file_get_contents($type->config_path), true);
        $entities   = $this->entityResolver->forManifest($manifest);
        $entityData = $this->entityResolver->loadAll($entities);

        // i18n: load translations if the template declares languages
        $languages = $manifest['languages'] ?? [];
        $i18n      = [];
        if (! empty($languages) && isset($manifest['i18n'])) {
            $i18nPath = storage_path('app/templates/' . $slug . '/' . $manifest['i18n']);
            if (file_exists($i18nPath)) {
                $i18n = json_decode(file_get_contents($i18nPath), true) ?? [];
            }
        }

        return view('documents.create', compact('type', 'form', 'entities', 'entityData', 'languages', 'i18n'));
    }

    // -------------------------------------------------------------------------
    // EDIT FORM (draft only)
    // -------------------------------------------------------------------------

    public function edit(Document $document)
    {
        if (! $document->canBeEdited()) {
            return redirect()->route('documents.show', $document)
                ->with('error', 'Only draft documents can be edited.');
        }

        $type = $document->documentType;
        $slug = $type->slug;

        $manifest = json_decode(file_get_contents(
            storage_path('app/templates/' . $slug . '/manifest.json')
        ), true);

        $form       = json_decode(file_get_contents($type->config_path), true);
        $entities   = $this->entityResolver->forManifest($manifest);
        $entityData = $this->entityResolver->loadAll($entities);

        // Use saved json_data as prefill
        $prefill     = $document->json_data ?? [];
        $customerId  = $document->customer_id;

        return view('documents.edit', compact(
            'document', 'type', 'form', 'entities', 'entityData', 'prefill', 'customerId'
        ));
    }

    // -------------------------------------------------------------------------
    // UPDATE (save edits, bump version)
    // -------------------------------------------------------------------------

    public function update(Request $request, Document $document)
    {
        if (! $document->canBeEdited()) {
            return redirect()->route('documents.show', $document)
                ->with('error', 'Only draft documents can be edited.');
        }

        $type = $document->documentType;
        $slug = $type->slug;

        $manifest = json_decode(file_get_contents(
            storage_path('app/templates/' . $slug . '/manifest.json')
        ), true);

        $entities     = $this->entityResolver->forManifest($manifest);
        $entityIdKeys = array_map(fn($k) => $k . '_id', array_keys($entities));
        $data         = $request->except(array_merge(['_token', '_method'], $entityIdKeys));

        $selectedIds = [];
        foreach (array_keys($entities) as $key) {
            $selectedIds[$key . '_id'] = $request->input($key . '_id');
        }

        $linkedIds = $this->entityResolver->saveFromRequest($entities, $data, $selectedIds);

        $data = $this->computeTotals($slug, $data);
        $lang = $request->input('lang', 'en');
        $html = $this->renderHtml($type, $data, $lang);

        $numberKey = $this->resolveNumberKey($slug, $manifest);

        $reference = $data[$slug . '_number']
            ?? $data['invoice_number']
            ?? $data['quote_number']
            ?? $document->reference;

        if ($reference === '') {
            $reference = null;
        }

        // Server-side double-check: never trust client-side validation alone.
        if ($reference !== null && $this->isReferenceTaken($type->id, $reference, $document->id)) {
            return back()
                ->withInput()
                ->withErrors([$numberKey => "\"{$reference}\" is already used by another {$type->name}. Please choose a different number."]);
        }

        $document->update([
            'customer_id'   => $linkedIds['customer_id'] ?? $document->customer_id,
            'title'         => $type->name . ($reference ? ' #' . $reference : ''),
            'reference'     => $reference,
            'version'       => $document->version + 1,
            'json_data'     => $data,
            'html_snapshot' => $html,
        ]);

        return redirect()->route('documents.show', $document)
            ->with('success', $type->name . ' updated to v' . $document->fresh()->version . '.');
    }

    // -------------------------------------------------------------------------
    // STORE (new document)
    // -------------------------------------------------------------------------

    public function store(Request $request, string $slug)
    {
        $type     = DocumentType::where('slug', $slug)->where('active', true)->firstOrFail();
        $manifest = json_decode(file_get_contents(
            storage_path('app/templates/' . $slug . '/manifest.json')
        ), true);

        $entities     = $this->entityResolver->forManifest($manifest);
        $entityIdKeys = array_map(fn($k) => $k . '_id', array_keys($entities));
        $data         = $request->except(array_merge(['_token'], $entityIdKeys));

        $selectedIds = [];
        foreach (array_keys($entities) as $key) {
            $selectedIds[$key . '_id'] = $request->input($key . '_id');
        }

        $linkedIds = $this->entityResolver->saveFromRequest($entities, $data, $selectedIds);

        // Auto-generate document number if the manifest declares a prefix
        // and no number has already been set (e.g. from a quote→invoice conversion).
        $prefix     = $manifest['prefix'] ?? null;
        $numberKey  = $this->resolveNumberKey($slug, $manifest);
        if ($prefix && empty($data[$numberKey])) {
            $data[$numberKey] = $this->documentNumbers->generate($prefix);
        }

        $data = $this->computeTotals($slug, $data);
        $lang = $request->input('lang', 'en');
        $html = $this->renderHtml($type, $data, $lang);

        $reference = $data[$slug . '_number']
            ?? $data['invoice_number']
            ?? $data['quote_number']
            ?? null;

        // Normalize empty string to null so the DB unique index (which
        // treats NULL as "not constrained") doesn't collide across
        // documents that legitimately have no number set.
        if ($reference === '') {
            $reference = null;
        }

        // Server-side double-check: never trust client-side validation alone.
        // This guarantees uniqueness even if JS was bypassed or disabled.
        if ($reference !== null && $this->isReferenceTaken($type->id, $reference)) {
            return back()
                ->withInput()
                ->withErrors([$numberKey => "\"{$reference}\" is already used by another {$type->name}. Please choose a different number."]);
        }

        $parentId = session('convert_from');
        session()->forget(['convert_from', 'convert_data', 'convert_customer_id']);

        $document = Document::create([
            'document_type_id' => $type->id,
            'customer_id'      => $linkedIds['customer_id'] ?? null,
            'parent_id'        => $parentId,
            'title'            => $type->name . ($reference ? ' #' . $reference : ''),
            'reference'        => $reference,
            'status'           => Document::STATUS_DRAFT,
            'version'          => 1,
            'json_data'        => $data,
            'html_snapshot'    => $html,
        ]);

        if ($parentId) {
            Document::find($parentId)?->update(['status' => Document::STATUS_INVOICED]);
        }

        return redirect()->route('documents.page', $slug)
            ->with('success', $type->name . ' saved successfully.');
    }

    // -------------------------------------------------------------------------
    // PREVIEW (no save)
    // -------------------------------------------------------------------------

    public function preview(Request $request, string $slug)
    {
        $type     = DocumentType::where('slug', $slug)->where('active', true)->firstOrFail();
        $manifest = json_decode(file_get_contents(
            storage_path('app/templates/' . $slug . '/manifest.json')
        ), true);

        $entities     = $this->entityResolver->forManifest($manifest);
        $entityIdKeys = array_map(fn($k) => $k . '_id', array_keys($entities));
        $data         = $request->except(array_merge(['_token'], $entityIdKeys));

        $selectedIds = [];
        foreach (array_keys($entities) as $key) {
            $selectedIds[$key . '_id'] = $request->input($key . '_id');
        }

        $this->entityResolver->saveFromRequest($entities, $data, $selectedIds);

        $data = $this->computeTotals($slug, $data);
        $lang = $request->input('lang', 'en');
        $html = $this->renderHtml($type, $data, $lang);

        return response($html);
    }

    // -------------------------------------------------------------------------
    // SHOW
    // -------------------------------------------------------------------------

    public function show(Document $document)
    {
        return view('documents.viewer', compact('document'));
    }

    public function raw(Document $document)
    {
        return response($document->html_snapshot ?? '')
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    // -------------------------------------------------------------------------
    // HISTORY
    // -------------------------------------------------------------------------

    public function history(Request $request)
    {
        $types        = DocumentType::orderBy('name')->get();
        $selectedSlug = $request->query('type');

        $query = Document::with(['documentType', 'customer'])->orderByDesc('created_at');

        if ($selectedSlug) {
            $query->whereHas('documentType', fn($q) => $q->where('slug', $selectedSlug));
        }

        $documents = $query->paginate(15)->withQueryString();

        return view('documents.history', compact('documents', 'types', 'selectedSlug'));
    }

    // -------------------------------------------------------------------------
    // UPDATE STATUS
    // -------------------------------------------------------------------------

    public function updateStatus(Request $request, Document $document)
    {
        $request->validate([
            'status' => ['required', 'in:draft,sent,accepted,rejected,invoiced,paid,cancelled'],
        ]);

        if ($document->status === Document::STATUS_INVOICED) {
            return back()->with('error', 'This quote has already been converted to an invoice.');
        }

        $document->update(['status' => $request->status]);

        return back()->with('success', 'Status updated to "' . $request->status . '".');
    }

    // -------------------------------------------------------------------------
    // CONVERT QUOTE → INVOICE
    // -------------------------------------------------------------------------

    public function convert(Document $document)
    {
        if (! $document->isQuote()) {
            return back()->with('error', 'Only quotes can be converted.');
        }

        if ($document->status !== Document::STATUS_ACCEPTED) {
            return back()->with('error', 'Only accepted quotes can be converted to an invoice.');
        }

        if ($document->convertedInvoice) {
            return redirect()->route('documents.show', $document->convertedInvoice)
                ->with('error', 'This quote was already converted.');
        }

        $invoiceType = DocumentType::where('slug', 'invoice')->where('active', true)->first();
        if (! $invoiceType) {
            return back()->with('error', 'Invoice template is not installed.');
        }

        $data = $document->json_data;

        if (isset($data['quote_number']) && ! isset($data['invoice_number'])) {
            $data['invoice_number'] = '';
        }

        session([
            'convert_from'        => $document->id,
            'convert_data'        => $data,
            'convert_customer_id' => $document->customer_id,
        ]);

        return redirect()->route('documents.create', 'invoice')
            ->with('info', 'Quote #' . $document->reference . ' loaded. Review and save the invoice.');
    }

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------

    private function computeTotals(string $slug, array $data): array
    {
        if (in_array($slug, ['invoice', 'quote', 'proposal', 'proposition', 'delivery-note', 'facture-fr', 'quote-fr'])) {
            $items = $data['items'] ?? [];

            // Legacy fallback for documents / callers still using the old
            // single-product fields instead of the items[] array.
            if (empty($items) && (! empty($data['product_name']) || ! empty($data['product_unit_price']))) {
                $items = [[
                    'reference'  => $data['product_reference']   ?? '',
                    'name'       => $data['product_name']        ?? '',
                    'unit'       => $data['product_unit']        ?? '',
                    'quantity'   => $data['product_quantity']    ?? $data['quantity'] ?? 1,
                    'unit_price' => $data['product_unit_price']  ?? $data['unit_price'] ?? 0,
                ]];
            }

            // Normalize each row, compute its line total, and drop empty rows
            // (e.g. a blank line the user added then didn't fill in).
            $normalizedItems  = [];
            $productSubtotal  = 0.0;
            foreach ($items as $item) {
                $name = trim((string) ($item['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $qty       = (float) ($item['quantity']   ?? 0);
                $price     = (float) ($item['unit_price'] ?? 0);
                $lineTotal = $qty * $price;
                $productSubtotal += $lineTotal;

                $normalizedItems[] = [
                    'reference'  => (string) ($item['reference'] ?? ''),
                    'name'       => $name,
                    'unit'       => (string) ($item['unit'] ?? ''),
                    'quantity'   => $qty,
                    'unit_price' => number_format($price, 2, '.', ''),
                    'line_total' => number_format($lineTotal, 2, '.', ''),
                ];
            }
            $data['items'] = $normalizedItems;

            // Mirror the first line into the legacy product_* keys so any
            // template or integration still reading a single product keeps
            // working even though the document now carries several.
            $first = $normalizedItems[0] ?? null;
            $data['product_reference']  = $first['reference']  ?? ($data['product_reference'] ?? '');
            $data['product_name']       = $first['name']       ?? ($data['product_name'] ?? '');
            $data['product_unit']       = $first['unit']       ?? ($data['product_unit'] ?? '');
            $data['product_quantity']   = $first['quantity']   ?? ($data['product_quantity'] ?? '');
            $data['product_unit_price'] = $first['unit_price'] ?? ($data['product_unit_price'] ?? '');

            $vatRate     = (float) ($data['vat_rate']     ?? 0);
            $deliveryFee = (float) ($data['delivery_fee'] ?? 0);

            // Document-level discount, applied to the product subtotal
            // before delivery fees and VAT.
            $discountType   = $data['discount_type']  ?? '';
            $discountValue  = (float) ($data['discount_value'] ?? 0);
            $discountAmount = 0.0;
            if ($discountValue > 0 && in_array($discountType, ['percent', 'amount'], true)) {
                $discountAmount = $discountType === 'percent'
                    ? $productSubtotal * ($discountValue / 100)
                    : $discountValue;
                // Never let the discount exceed the goods it applies to.
                $discountAmount = max(0.0, min($discountAmount, $productSubtotal));
            }

            $netProductSubtotal = $productSubtotal - $discountAmount;

            // Invoice-level subtotal includes any delivery/customs fee, so it
            // is reflected in VAT and the grand total rather than silently
            // dropped from the document.
            $subtotal  = $netProductSubtotal + $deliveryFee;
            $vatAmount = $subtotal * ($vatRate / 100);
            $total     = $subtotal + $vatAmount;

            $data['product_subtotal'] = number_format($productSubtotal, 2, '.', '');
            $data['has_discount']     = $discountAmount > 0 ? '1' : '';
            $data['discount_type']    = $discountType;
            $data['discount_value']   = $discountValue > 0 ? rtrim(rtrim(number_format($discountValue, 2, '.', ''), '0'), '.') : '';
            $data['discount_amount']  = number_format($discountAmount, 2, '.', '');
            $data['discount_label']   = $discountType === 'percent'
                ? 'Discount (' . $data['discount_value'] . '%)'
                : 'Discount';
            $data['subtotal']         = number_format($subtotal,  2, '.', '');
            $data['vat_amount']       = number_format($vatAmount, 2, '.', '');
            $data['total']            = number_format($total,     2, '.', '');

            if (! empty($data['delivery_fee'])) {
                $data['delivery_fee'] = number_format($deliveryFee, 2, '.', '');
            }

            $lateFee = (float) config('company.late_payment_flat_fee', 0);
            $data['late_payment_flat_fee'] = number_format($lateFee, 2, '.', '');

            // Foreign currency conversion (optional section)
            if (! empty($data['fx_currency']) && ! empty($data['fx_rate'])) {
                $rate = (float) $data['fx_rate'];

                $data['fx_product_subtotal']  = number_format($productSubtotal * $rate, 2, '.', '');
                $data['fx_discount_amount']   = number_format($discountAmount  * $rate, 2, '.', '');
                $data['fx_subtotal']          = number_format($subtotal       * $rate, 2, '.', '');
                $data['fx_vat']               = number_format($vatAmount      * $rate, 2, '.', '');
                $data['fx_total']             = number_format($total          * $rate, 2, '.', '');
                $data['fx_symbol']            = $this->currencySymbol($data['fx_currency']);
                $data['fx_late_fee']          = number_format($lateFee        * $rate, 2, '.', '');

                if ($deliveryFee > 0) {
                    $data['fx_delivery_fee'] = number_format($deliveryFee * $rate, 2, '.', '');
                }

                // Non-EUR invoices are always settled via the international account.
                $data['bank_account'] = 'int';
            }

            // The template engine only supports truthy {{#section}} blocks (no
            // {{^section}} "unless"), so provide an explicit flag for the
            // non-FX ("normal") rendering path.
            $data['no_fx'] = empty($data['fx_currency']) ? '1' : '';

            // Pre-render the <tr> rows for the items table. These are raw
            // HTML (not escaped) and injected verbatim by renderHtml().
            $currencySymbol = $this->companyCurrencySymbol();
            $data['line_items_rows']    = $this->renderLineItemsRows($normalizedItems, false, $data, $currencySymbol);
            $data['line_items_rows_fx'] = $this->renderLineItemsRows($normalizedItems, true,  $data, $currencySymbol);
        }

        return $data;
    }

    /**
     * Build the raw <tr> HTML for each line item. Returned HTML is injected
     * verbatim (not escaped) by renderHtml() via the {{line_items_rows}} /
     * {{line_items_rows_fx}} placeholders.
     */
    private function renderLineItemsRows(array $items, bool $fx, array $data, string $currencySymbol): string
    {
        if (empty($items)) {
            return '';
        }

        $showRef  = ! empty($data['product_reference']);
        $fxRate   = (float) ($data['fx_rate'] ?? 0);
        $fxSymbol = ! empty($data['fx_currency']) ? $this->currencySymbol($data['fx_currency']) : '';

        $rows = '';
        foreach ($items as $item) {
            $name  = htmlspecialchars($item['name']);
            $unit  = htmlspecialchars($item['unit']);
            $ref   = htmlspecialchars($item['reference']);
            $qty   = htmlspecialchars(rtrim(rtrim(number_format((float) $item['quantity'], 2, '.', ''), '0'), '.'));
            $price = htmlspecialchars($item['unit_price']);
            $total = htmlspecialchars($item['line_total']);

            $unitHtml = $unit !== '' ? '<br><span class="item-description">' . $unit . '</span>' : '';
            $refCell  = $showRef ? "<td>{$ref}</td>" : '';

            $fxCell = '';
            if ($fx) {
                $fxTotal = htmlspecialchars(number_format(((float) $item['line_total']) * $fxRate, 2, '.', ''));
                $fxCell  = "<td class=\"right fx-col\" style=\"padding-right:8px;\">{$fxSymbol} {$fxTotal}</td>";
            }

            $rows .= "<tr><td>{$name}{$unitHtml}</td>{$refCell}"
                . "<td class=\"center\">{$currencySymbol} {$price}</td>"
                . "<td class=\"center\">{$qty}</td>"
                . "<td class=\"right\">{$currencySymbol} {$total}</td>{$fxCell}</tr>";
        }

        return $rows;
    }

    /**
     * The company's default currency symbol, read directly from
     * storage/app/company.json (used before renderHtml() would otherwise
     * inject it, since totals are computed first).
     */
    private function companyCurrencySymbol(): string
    {
        $companyPath = storage_path('app/company.json');
        $company = file_exists($companyPath)
            ? (json_decode(file_get_contents($companyPath), true) ?? [])
            : [];

        return $company['default_currency_symbol'] ?? '€';
    }

    /**
     * Map a currency code to its display symbol.
     */
    private function currencySymbol(string $code): string
    {
        return match (strtoupper($code)) {
            'USD'   => '$',
            'GBP'   => '£',
            'INR'   => '₹',
            'JPY'   => '¥',
            'CHF'   => 'CHF',
            'CAD'   => 'CA$',
            'AUD'   => 'A$',
            'CNY'   => '¥',
            default => $code,
        };
    }

    /**
     * AJAX endpoint: check whether a given reference/number is already used
     * by another document of the same type. Used for live validation on the
     * create/edit form as the user types.
     */
    public function checkNumberUnique(Request $request, string $slug)
    {
        $type   = DocumentType::where('slug', $slug)->where('active', true)->firstOrFail();
        $number = trim((string) $request->query('number', ''));
        $excludeId = $request->query('exclude_id');

        if ($number === '') {
            return response()->json(['unique' => true]);
        }

        $taken = $this->isReferenceTaken($type->id, $number, $excludeId);

        return response()->json(['unique' => ! $taken]);
    }

    /**
     * Shared uniqueness check used by both the live AJAX endpoint and the
     * server-side double-check in store()/update().
     */
    private function isReferenceTaken(int $documentTypeId, string $reference, $excludeId = null): bool
    {
        return Document::where('document_type_id', $documentTypeId)
            ->where('reference', $reference)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }

    /**
     * Determine which $data key holds the document number for a given slug.
     * Falls back to scanning form.json for the first field marked "auto": true.
     * Hardcoded fallbacks ensure legacy slugs always resolve correctly.
     */
    private function resolveNumberKey(string $slug, array $manifest): string
    {
        // Try to find the auto field in form.json
        $formPath = storage_path('app/templates/' . $slug . '/' . ($manifest['form'] ?? 'form.json'));
        if (file_exists($formPath)) {
            $sections = json_decode(file_get_contents($formPath), true) ?? [];
            foreach ($sections as $section) {
                foreach ($section['fields'] ?? [] as $field) {
                    if (! empty($field['auto'])) {
                        return $field['name'];
                    }
                }
            }
        }

        // Hardcoded fallbacks
        return match (true) {
            str_contains($slug, 'invoice'), str_contains($slug, 'facture') => 'invoice_number',
            default => 'quote_number',
        };
    }

    public function renderHtml(DocumentType $type, array $data, string $lang = 'en'): string
    {
        $html = file_get_contents($type->template_path);
        $css  = file_get_contents(dirname($type->template_path) . '/style.css');

        // Inject i18n strings into $data before template rendering
        $slug     = $type->slug;
        $manifest = json_decode(file_get_contents(
            storage_path('app/templates/' . $slug . '/manifest.json')
        ), true);
        if (isset($manifest['i18n'])) {
            $i18nPath = storage_path('app/templates/' . $slug . '/' . $manifest['i18n']);
            if (file_exists($i18nPath)) {
                $i18nAll = json_decode(file_get_contents($i18nPath), true) ?? [];
                // Fall back to 'en' if requested lang not found
                $strings = $i18nAll[$lang] ?? $i18nAll['en'] ?? [];
                foreach ($strings as $key => $value) {
                    $data['i18n_' . $key] = $value;
                }
            }
        }

        // Inject accent color as CSS variable override
        $accentColor      = $type->accent_color       ?? '#1a56db';
        $accentColorLight = $type->readManifest()['accent_color_light'] ?? '#f8faff';
        $colorOverride    = ":root{--accent:{$accentColor};--accent-light:{$accentColorLight};}\n";
        $cssWithColors    = $colorOverride . $css;

        $html = str_replace('{{style}}', $cssWithColors, $html);

        // Read all company data from storage/app/company.json
        $companyPath = storage_path('app/company.json');
        $company = file_exists($companyPath)
            ? (json_decode(file_get_contents($companyPath), true) ?? [])
            : [];

        $data['company_name']                  = $company['name']                    ?? '';
        // Logo comes from DB
        $logoAsset = CompanyAsset::logo();
        $data['company_logo']                  = $logoAsset ? $logoAsset->data_uri : '';
        $data['company_legal_form']            = $company['legal_form']              ?? '';
        $data['company_share_capital']         = $company['share_capital']           ?? '';
        $data['company_street']                = $company['street']                  ?? '';
        $data['company_city']                  = $company['city']                    ?? '';
        $data['company_zip']                   = $company['zip']                     ?? '';
        $data['company_country']               = $company['country']                 ?? '';
        $data['company_siren']                 = $company['siren']                   ?? '';
        $data['company_siret']                 = $company['siret']                   ?? '';
        $data['company_vat_number']            = $company['vat_number']              ?? '';
        $data['company_eori']                  = $company['eori']                    ?? '';
        $data['company_email']                 = $company['email']                   ?? '';
        $data['company_website']               = $company['website']                 ?? '';
        $data['company_currency']              = $company['default_currency']        ?? 'EUR';
        $data['company_currency_symbol']       = $company['default_currency_symbol'] ?? '€';
        $data['company_vat_mention']           = $company['vat_mention']             ?? '';
        $data['company_terms_text']            = $company['terms_text']              ?? '';
        $data['company_late_payment_text']     = $company['late_payment_text']       ?? '';
        $data['company_late_payment_fee_text'] = $company['late_payment_fee_text']   ?? '';

        // Inject bank details from config/bank.php
        $bankKey     = $data['bank_account'] ?? config('bank.default', 'int');
        $bankAccount = config('bank.accounts.' . $bankKey) ?? config('bank.accounts.' . config('bank.default'));
        if ($bankAccount) {
            $data['bank_label']          = $bankAccount['label']          ?? '';
            $data['bank_beneficiary']    = $bankAccount['beneficiary']    ?? '';
            $data['bank_name']           = $bankAccount['bank_name']      ?? '';
            $data['bank_address']        = $bankAccount['bank_address']   ?? '';
            $data['bank_iban']           = $bankAccount['iban']           ?? '';
            $data['bank_bic']            = $bankAccount['bic']            ?? '';
            $data['bank_code']           = $bankAccount['bank_code']      ?? '';
            $data['bank_branch_code']    = $bankAccount['branch_code']    ?? '';
            $data['bank_account_number'] = $bankAccount['account_number'] ?? '';
            $data['bank_rib_key']        = $bankAccount['rib_key']        ?? '';
        }

        // Resolve conditional blocks. Runs repeatedly so that blocks nested
        // inside other blocks (e.g. the delivery row nested inside the
        // FX / non-FX items table wrappers) are also evaluated, not just the
        // outermost block on a single pass.
        for ($i = 0; $i < 5; $i++) {
            $previous = $html;
            $html = preg_replace_callback(
                '/\{\{#(\w+)\}\}(.*?)\{\{\/\1\}\}/s',
                function ($matches) use ($data) {
                    $raw = $data[$matches[1]] ?? '';
                    if (is_array($raw)) {
                        return ! empty($raw) ? $matches[2] : '';
                    }
                    $value = trim((string) $raw);
                    return $value !== '' ? $matches[2] : '';
                },
                $html
            );
            if ($html === $previous) {
                break;
            }
        }

        // These keys hold pre-built, already-escaped HTML (the line item
        // rows) and must be injected verbatim, not passed through
        // htmlspecialchars() with the rest of $data below.
        $rawHtmlKeys = ['line_items_rows', 'line_items_rows_fx'];
        foreach ($rawHtmlKeys as $key) {
            if (isset($data[$key])) {
                $html = str_replace('{{' . $key . '}}', $data[$key], $html);
                unset($data[$key]);
            }
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                continue; // e.g. 'items' — not a template placeholder itself
            }
            $html = str_replace('{{' . $key . '}}', htmlspecialchars((string) $value), $html);
        }

        $html = preg_replace('/\{\{.*?\}\}/', '', $html);

        return $html;
    }
}