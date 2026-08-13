<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;

class EntityResolver
{
    protected array $registry = [
        'customer' => [
            'model'      => Customer::class,
            'prefix'     => 'customer',
            'unique_key' => 'email', // find existing by email
            'fields'     => [
                'name', 'company', 'department',
                'street', 'city', 'zip', 'country',
                'phone', 'email', 'vat_number',
            ],
        ],
        'product' => [
            'model'      => Product::class,
            'prefix'     => 'product',
            'unique_key' => 'reference', // find existing by reference
            'fields'     => [
                'reference', 'name', 'description',
                'product_unit', 'unit_price', 'page_url',
            ],
        ],
    ];

    public function forManifest(array $manifest): array
    {
        $declared = $manifest['entities'] ?? [];
        $resolved = [];

        foreach ($declared as $entityKey) {
            if (isset($this->registry[$entityKey])) {
                $resolved[$entityKey] = $this->registry[$entityKey];
            }
        }

        return $resolved;
    }

    public function loadAll(array $entities): array
    {
        $data = [];

        foreach ($entities as $key => $config) {
            $model        = $config['model'];
            $data[$key]   = $model::orderBy('name')->get();
        }

        return $data;
    }

    public function saveFromRequest(
        array $entities,
        array &$data,
        array $selectedIds
    ): array {
        $linkedIds = [];

        foreach ($entities as $key => $config) {
            $model      = $config['model'];
            $prefix     = $config['prefix'];
            $fields     = $config['fields'];
            $uniqueKey  = $config['unique_key'];
            $idKey      = $key . '_id';

            // Extract submitted fields, mapping prefix_field → field
            $extracted = [];
            foreach ($fields as $field) {
                $inputKey          = $prefix . '_' . $field;
                $extracted[$field] = $data[$inputKey] ?? $data[$field] ?? null;
            }

            $selectedId = $selectedIds[$idKey] ?? null;

            if ($selectedId) {
                // User picked an existing record — update it
                $record = $model::find($selectedId);
                if ($record) {
                    $record->update(array_filter(
                        $extracted,
                        fn($v) => $v !== null && $v !== ''
                    ));
                }
            } else {
                // No picker selection — find by unique key or create
                $uniqueValue = $extracted[$uniqueKey] ?? null;

                // Does the submission actually contain any data for this entity?
                $hasAnyData = collect($extracted)->contains(fn($v) => $v !== null && $v !== '');

                if (! empty($uniqueValue)) {
                    $record = $model::where($uniqueKey, $uniqueValue)->first();

                    if ($record) {
                        // Already exists — update fields
                        $record->update(array_filter(
                            $extracted,
                            fn($v) => $v !== null && $v !== ''
                        ));
                    } else {
                        // Truly new — create
                        $record = $model::create(array_filter(
                            $extracted,
                            fn($v) => $v !== null && $v !== ''
                        ));
                    }
                } elseif ($hasAnyData) {
                    // No unique key value (e.g. email left blank on a JSON-generated
                    // document) but other fields were filled in — we can't dedupe
                    // against an existing record, so just create a new one rather
                    // than silently dropping the customer entirely.
                    $record = $model::create(array_filter(
                        $extracted,
                        fn($v) => $v !== null && $v !== ''
                    ));
                }

            }

            if (isset($record)) {
                $linkedIds[$idKey] = $record->id;

                // Inject back into $data for template rendering
                foreach ($extracted as $field => $value) {
                    $data[$prefix . '_' . $field] = $value ?? '';
                }

                unset($record);
            }
        }

        return $linkedIds;
    }
}