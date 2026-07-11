<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Product;
use Illuminate\Http\Request;

class ImportExportController extends Controller
{
    // =========================================================================
    // DOCUMENT — Download blank JSON model
    // =========================================================================

    /**
     * Download a blank JSON template for a given document type.
     * Shows every field the document expects so the user can fill it.
     */
    public function documentModel(string $slug)
    {
        $type     = DocumentType::where('slug', $slug)->where('active', true)->firstOrFail();
        $form     = json_decode(file_get_contents($type->config_path), true);
        $manifest = json_decode(file_get_contents(
            storage_path('app/templates/' . $slug . '/manifest.json')
        ), true);

        $model = ['_template' => $slug, '_version' => $manifest['version'] ?? '1.0'];

        // Customer fields
        if (in_array('customer', $manifest['entities'] ?? [])) {
            $model['customer'] = [
                'name'       => '',
                'company'    => '',
                'department' => '',
                'street'     => '',
                'city'       => '',
                'zip'        => '',
                'country'    => '',
                'phone'      => '',
                'email'      => '',
                'vat_number' => '',
            ];
        }

        // Product fields
        if (in_array('product', $manifest['entities'] ?? [])) {
            $model['product'] = [
                'reference'    => '',
                'name'         => '',
                'product_unit' => '',
                'quantity'     => 1,
                'unit_price'   => 0.00,
            ];
        }

        // Document-specific fields from form.json
        foreach ($form as $section) {
            foreach ($section['fields'] as $field) {
                $model[$field['name']] = match($field['type']) {
                    'number', 'currency' => 0,
                    'date'   => date('Y-m-d'),
                    default  => '',
                };
            }
        }

        $filename = $slug . '-model.json';

        return response()->json($model, 200, [
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // =========================================================================
    // DOCUMENT — Export saved document as JSON
    // =========================================================================

    public function documentExport(Document $document)
    {
        $data = array_merge(
            ['_template' => $document->documentType->slug],
            ['_reference' => $document->reference],
            ['_version' => $document->version],
            ['_status' => $document->status],
            $document->json_data ?? []
        );

        $filename = ($document->reference ?? 'document-' . $document->id) . '.json';

        return response()->json($data, 200, [
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // =========================================================================
    // DOCUMENT — Import JSON to pre-fill create form (returns JSON for JS)
    // =========================================================================

    public function documentImport(Request $request, string $slug)
    {
        $data = $this->parseJsonInput($request);

        if (is_string($data)) {
            return response()->json(['error' => $data], 422);
        }

        // Flatten customer/product nested keys to match form field names
        $flat = [];

        if (isset($data['customer']) && is_array($data['customer'])) {
            foreach ($data['customer'] as $k => $v) {
                $flat['customer_' . $k] = $v;
            }
        }

        if (isset($data['product']) && is_array($data['product'])) {
            foreach ($data['product'] as $k => $v) {
                // map quantity → product_quantity, unit_price → product_unit_price
                $mapped = match($k) {
                    'quantity'   => 'product_quantity',
                    'unit_price' => 'product_unit_price',
                    default      => 'product_' . $k,
                };
                $flat[$mapped] = $v;
            }
        }

        // Copy top-level fields (document-specific)
        foreach ($data as $k => $v) {
            if (in_array($k, ['_template', '_version', '_reference', '_status', 'customer', 'product'])) continue;
            if (! is_array($v)) {
                $flat[$k] = $v;
            }
        }

        return response()->json(['fields' => $flat]);
    }

    // =========================================================================
    // CUSTOMER — Download blank JSON model
    // =========================================================================

    public function customerModel()
    {
        $model = [
            'name'       => '',
            'company'    => '',
            'department' => '',
            'street'     => '',
            'city'       => '',
            'zip'        => '',
            'country'    => '',
            'phone'      => '',
            'email'      => '',
            'vat_number' => '',
        ];

        return response()->json($model, 200, [
            'Content-Disposition' => 'attachment; filename="customer-model.json"',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // =========================================================================
    // CUSTOMER — Export
    // =========================================================================

    public function customerExport(Customer $customer)
    {
        $data = $customer->toEntityArray();
        $filename = 'customer-' . \Str::slug($customer->name) . '.json';

        return response()->json($data, 200, [
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // =========================================================================
    // CUSTOMER — Import JSON (returns JSON for JS to fill the form)
    // =========================================================================

    public function customerImport(Request $request)
    {
        $data = $this->parseJsonInput($request);

        if (is_string($data)) {
            return response()->json(['error' => $data], 422);
        }

        $allowed = ['name','company','department','street','city','zip','country','phone','email','vat_number'];
        $fields  = array_intersect_key($data, array_flip($allowed));

        return response()->json(['fields' => $fields]);
    }

    // =========================================================================
    // PRODUCT — Download blank JSON model
    // =========================================================================

    public function productModel()
    {
        $model = [
            'reference'    => '',
            'name'         => '',
            'description'  => '',
            'product_unit' => '',
            'unit_price'   => 0.00,
            'page_url'     => '',
        ];

        return response()->json($model, 200, [
            'Content-Disposition' => 'attachment; filename="product-model.json"',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // =========================================================================
    // PRODUCT — Export
    // =========================================================================

    public function productExport(Product $product)
    {
        $data     = $product->toEntityArray();
        $filename = 'product-' . \Str::slug($product->reference) . '.json';

        return response()->json($data, 200, [
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // =========================================================================
    // PRODUCT — Import JSON (returns JSON for JS to fill the form)
    // =========================================================================

    public function productImport(Request $request)
    {
        $data = $this->parseJsonInput($request);

        if (is_string($data)) {
            return response()->json(['error' => $data], 422);
        }

        $allowed = ['reference','name','description','product_unit','unit_price','page_url'];
        $fields  = array_intersect_key($data, array_flip($allowed));

        // Map to form field names
        $mapped = [];
        foreach ($fields as $k => $v) {
            $mapped['product_' . $k] = $v;
        }

        // unit_price → product_unit_price
        if (isset($mapped['product_unit_price'])) {
            // already mapped correctly
        }

        return response()->json(['fields' => $mapped]);
    }

    // =========================================================================
    // HELPER — Parse JSON from either file upload or raw text input
    // =========================================================================

    private function parseJsonInput(Request $request): array|string
    {
        if ($request->hasFile('json_file')) {
            $content = file_get_contents($request->file('json_file')->getRealPath());
        } elseif ($request->filled('json_text')) {
            $content = $request->input('json_text');
        } else {
            return 'No JSON provided. Upload a file or paste JSON text.';
        }

        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return 'Invalid JSON: ' . json_last_error_msg();
        }

        return $decoded;
    }
}
