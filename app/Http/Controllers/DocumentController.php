<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Product;
use App\Services\EntityResolver;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(protected EntityResolver $entityResolver) {}

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
            ->with('customer')
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

        return view('documents.create', compact('type', 'form', 'entities', 'entityData'));
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
        $html = $this->renderHtml($type, $data);

        $reference = $data[$slug . '_number']
            ?? $data['invoice_number']
            ?? $data['quote_number']
            ?? $document->reference;

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

        $data = $this->computeTotals($slug, $data);
        $html = $this->renderHtml($type, $data);

        $reference = $data[$slug . '_number']
            ?? $data['invoice_number']
            ?? $data['quote_number']
            ?? null;

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
        $html = $this->renderHtml($type, $data);

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
        if (in_array($slug, ['invoice', 'quote'])) {
            $qty       = (float) ($data['product_quantity']   ?? $data['quantity'] ?? 0);
            $unitPrice = (float) ($data['product_unit_price'] ?? $data['unit_price'] ?? 0);
            $vatRate   = (float) ($data['vat_rate']           ?? 0);

            $subtotal  = $qty * $unitPrice;
            $vatAmount = $subtotal * ($vatRate / 100);
            $total     = $subtotal + $vatAmount;

            $data['subtotal']   = number_format($subtotal,  2, '.', '');
            $data['vat_amount'] = number_format($vatAmount, 2, '.', '');
            $data['total']      = number_format($total,     2, '.', '');
        }

        return $data;
    }

    public function renderHtml(DocumentType $type, array $data): string
    {
        $html = file_get_contents($type->template_path);
        $css  = file_get_contents(dirname($type->template_path) . '/style.css');

        $html = str_replace('{{style}}', $css, $html);

        $company = config('company');
        $data['company_name']                  = $company['name']                    ?? '';
        $data['company_logo']                  = $company['logo']                    ?? '';
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

        $html = preg_replace_callback(
            '/\{\{#(\w+)\}\}(.*?)\{\{\/\1\}\}/s',
            function ($matches) use ($data) {
                $value = trim($data[$matches[1]] ?? '');
                return $value !== '' ? $matches[2] : '';
            },
            $html
        );

        foreach ($data as $key => $value) {
            $html = str_replace('{{' . $key . '}}', htmlspecialchars((string) $value), $html);
        }

        $html = preg_replace('/\{\{.*?\}\}/', '', $html);

        return $html;
    }
}
