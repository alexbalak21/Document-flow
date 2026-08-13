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

        // Product fields — an array so a document can carry multiple line items.
        if (in_array('product', $manifest['entities'] ?? [])) {
            $model['products'] = [
                [
                    'reference'    => '',
                    'name'         => '',
                    'product_unit' => '',
                    'quantity'     => 1,
                    'unit_price'   => 0.00,
                ],
            ];

            // Document-level discount (applies to the products subtotal).
            $model['discount_type']  = ''; // "percent" or "amount" (leave blank for no discount)
            $model['discount_value'] = 0;
        }

        // Document-specific fields from form.json
        // Track optional section field names so the importer knows to auto-activate them
        $optionalSectionFields = [];
        foreach ($form as $section) {
            $isOptional = ! empty($section['optional']);
            foreach ($section['fields'] as $field) {
                $model[$field['name']] = match($field['type']) {
                    'number', 'currency' => 0,
                    'date'               => date('Y-m-d'),
                    'select'             => $field['options'][0] ?? '',
                    default              => '',
                };
                if ($isOptional) {
                    $optionalSectionFields[$field['name']] = $section['section'];
                }
            }
        }

        // Hint which fields belong to optional (collapsed) sections.
        // When a non-empty value is present for any of these fields during import,
        // the importer will auto-activate (expand) the corresponding section.
        if ($optionalSectionFields) {
            $model['_optional_sections'] = $optionalSectionFields;
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

        // Load form to know which fields belong to optional (collapsed) sections
        $type = DocumentType::where('slug', $slug)->where('active', true)->firstOrFail();
        $form = json_decode(file_get_contents($type->config_path), true);

        // Map: field_name -> section DOM id (e.g. 'fx_currency' -> 'section-foreign-currency')
        $optionalFieldToSection = [];
        foreach ($form as $section) {
            if (! empty($section['optional'])) {
                $sectionId = 'section-' . \Illuminate\Support\Str::slug($section['section']);
                foreach ($section['fields'] as $field) {
                    $optionalFieldToSection[$field['name']] = $sectionId;
                }
            }
        }

        // Flatten customer nested keys to match form field names
        $flat = [];

        if (isset($data['customer']) && is_array($data['customer'])) {
            foreach ($data['customer'] as $k => $v) {
                $flat['customer_' . $k] = $v;
            }
        }

        // Products: prefer the new "products" array (multiple line items).
        // Still accept the legacy singular "product" object for JSON files
        // exported before multi-product support existed.
        $items = [];
        $rawProducts = $data['products'] ?? $data['items'] ?? (isset($data['product']) ? [$data['product']] : []);
        if (is_array($rawProducts)) {
            foreach ($rawProducts as $p) {
                if (! is_array($p)) continue;
                $items[] = [
                    'reference'  => $p['reference']    ?? '',
                    'name'       => $p['name']          ?? '',
                    'unit'       => $p['product_unit']  ?? $p['unit'] ?? '',
                    'quantity'   => $p['quantity']       ?? 1,
                    'unit_price' => $p['unit_price']     ?? 0,
                ];
            }
        }

        // Copy top-level fields (skip meta keys and nested objects)
        $skip = ['_template', '_version', '_reference', '_status', '_optional_sections', 'customer', 'product', 'products'];
        foreach ($data as $k => $v) {
            if (in_array($k, $skip) || is_array($v)) continue;
            $flat[$k] = $v;
        }

        // Determine which optional sections have at least one non-empty value
        // so the JS can auto-expand and enable them before filling fields
        $sectionsToActivate = [];
        foreach ($flat as $fieldName => $value) {
            if (isset($optionalFieldToSection[$fieldName]) && $value !== '' && $value !== null && $value !== 0) {
                $sectionsToActivate[] = $optionalFieldToSection[$fieldName];
            }
        }
        $sectionsToActivate = array_values(array_unique($sectionsToActivate));

        return response()->json([
            'fields'               => $flat,
            'items'                => $items,
            'sections_to_activate' => $sectionsToActivate,
        ]);
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