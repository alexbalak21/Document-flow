<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Product;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * Show the dynamic form for a given template slug.
     */
    public function create(string $slug)
    {
        $type = DocumentType::where('slug', $slug)->where('active', true)->firstOrFail();

        $form = json_decode(file_get_contents($type->config_path), true);

        $products = Product::orderBy('name')->get();

        return view('documents.create', compact('type', 'form', 'products'));
    }

    /**
     * Generate, save, and preview the filled document.
     */
    public function preview(Request $request, string $slug)
    {
        $type = DocumentType::where('slug', $slug)->where('active', true)->firstOrFail();

        $data = $request->except('_token');

        // Computed fields for invoice
        if ($slug === 'invoice') {
            $qty       = (float) ($data['product_quantity']   ?? 0);
            $unitPrice = (float) ($data['product_unit_price'] ?? 0);
            $vatRate   = (float) ($data['vat_rate']           ?? 0);

            $subtotal   = $qty * $unitPrice;
            $vatAmount  = $subtotal * ($vatRate / 100);
            $total      = $subtotal + $vatAmount;

            $data['subtotal']   = number_format($subtotal,  2, '.', '');
            $data['vat_amount'] = number_format($vatAmount, 2, '.', '');
            $data['total']      = number_format($total,     2, '.', '');
        }

        // Load and fill the template
        $html = file_get_contents($type->template_path);
        $css  = file_get_contents(dirname($type->template_path) . '/style.css');

        $html = str_replace('{{style}}', $css, $html);

        foreach ($data as $key => $value) {
            $html = str_replace('{{' . $key . '}}', htmlspecialchars((string) $value), $html);
        }

        $html = preg_replace('/\{\{#\w+\}\}.*?\{\{\/\w+\}\}/s', '', $html);
        $html = preg_replace('/\{\{.*?\}\}/', '', $html);

        // Build a human-readable title and reference
        $reference = $data['invoice_number']
            ?? $data['quote_number']
            ?? $data['reference']
            ?? null;

        $clientName = $data['client_name']
            ?? $data['client_company']
            ?? $data['recipient_name']
            ?? null;

        $title = $type->name;
        if ($reference)  $title .= ' #' . $reference;
        if ($clientName) $title .= ' — ' . $clientName;

        // Persist to database
        Document::create([
            'document_type_id' => $type->id,
            'title'            => $title,
            'reference'        => $reference,
            'json_data'        => $data,
            'html_snapshot'    => $html,
        ]);

        return response($html);
    }

    /**
     * History list — all documents, optionally filtered by type slug.
     */
    public function history(Request $request)
    {
        $types = DocumentType::where('active', true)->orderBy('name')->get();

        $query = Document::with('documentType')->latest();

        if ($slug = $request->get('type')) {
            $type = DocumentType::where('slug', $slug)->firstOrFail();
            $query->where('document_type_id', $type->id);
        }

        $documents = $query->paginate(20)->withQueryString();

        $selectedSlug = $slug ?? null;

        return view('documents.history', compact('types', 'documents', 'selectedSlug'));
    }

    /**
     * Show a single saved document.
     */
    public function show(Document $document)
    {
        return response($document->html_snapshot);
    }
}
