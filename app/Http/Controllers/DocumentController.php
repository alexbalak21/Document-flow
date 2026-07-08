<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    // -------------------------------------------------------------------------
    // CREATE FORM
    // -------------------------------------------------------------------------

    public function create(string $slug)
    {
        $type     = DocumentType::where('slug', $slug)->where('active', true)->firstOrFail();
        $form     = json_decode(file_get_contents($type->config_path), true);
        $products = Product::orderBy('name')->get();

        return view('documents.create', compact('type', 'form', 'products'));
    }

    // -------------------------------------------------------------------------
    // SAVE & PREVIEW
    // -------------------------------------------------------------------------

    public function store(Request $request, string $slug)
    {
        $type = DocumentType::where('slug', $slug)->where('active', true)->firstOrFail();

        $data = $request->except('_token');
        $data = $this->computeTotals($slug, $data);

        $html = $this->renderHtml($type, $data);

        $reference = $data[$slug . '_number'] ?? $data['invoice_number'] ?? $data['quote_number'] ?? null;

        $document = Document::create([
            'document_type_id' => $type->id,
            'title'            => $type->name . ($reference ? ' #' . $reference : ''),
            'reference'        => $reference,
            'status'           => Document::STATUS_DRAFT,
            'json_data'        => $data,
            'html_snapshot'    => $html,
        ]);

        return redirect()->route('documents.show', $document)
            ->with('success', $type->name . ' saved successfully.');
    }

    // -------------------------------------------------------------------------
    // PREVIEW (opens in new tab, does NOT save)
    // -------------------------------------------------------------------------

    public function preview(Request $request, string $slug)
    {
        $type = DocumentType::where('slug', $slug)->where('active', true)->firstOrFail();
        $data = $request->except('_token');
        $data = $this->computeTotals($slug, $data);
        $html = $this->renderHtml($type, $data);

        return response($html);
    }

    // -------------------------------------------------------------------------
    // SHOW (render saved html_snapshot)
    // -------------------------------------------------------------------------

    public function show(Document $document)
    {
        return response($document->html_snapshot);
    }

    // -------------------------------------------------------------------------
    // HISTORY
    // -------------------------------------------------------------------------

    public function history(Request $request)
    {
        $types       = DocumentType::orderBy('name')->get();
        $selectedSlug = $request->query('type');

        $query = Document::with('documentType')->orderByDesc('created_at');

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

        // Prevent overwriting invoiced status manually
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

        // Pre-fill the invoice form with quote data
        $data = $document->json_data;

        // Swap quote_number for invoice_number if needed
        if (isset($data['quote_number']) && ! isset($data['invoice_number'])) {
            $data['invoice_number'] = '';
        }

        // Store the parent_id so the form can link back
        session(['convert_from' => $document->id, 'convert_data' => $data]);

        return redirect()->route('documents.create', 'invoice')
            ->with('info', 'Quote #' . $document->reference . ' loaded. Review and save the invoice.');
    }

    // -------------------------------------------------------------------------
    // INTERNAL HELPERS
    // -------------------------------------------------------------------------

    private function computeTotals(string $slug, array $data): array
    {
        // Both invoice and quote share the same product calculation
        if (in_array($slug, ['invoice', 'quote'])) {
            $qty       = (float) ($data['product_quantity']   ?? 0);
            $unitPrice = (float) ($data['product_unit_price'] ?? 0);
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

    private function renderHtml(DocumentType $type, array $data): string
    {
        $html = file_get_contents($type->template_path);
        $css  = file_get_contents(dirname($type->template_path) . '/style.css');

        $html = str_replace('{{style}}', $css, $html);

        // Resolve optional blocks {{#field}}...{{/field}}
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

        // Remove unfilled placeholders
        $html = preg_replace('/\{\{.*?\}\}/', '', $html);

        return $html;
    }
}
