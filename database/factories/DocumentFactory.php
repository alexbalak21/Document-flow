<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'document_type_id' => DocumentType::factory(),
            'customer_id'      => Customer::factory(),
            'title'            => 'Invoice #INV-TEST-0001',
            'reference'        => 'INV-TEST-0001',
            'status'           => Document::STATUS_DRAFT,
            'version'          => 1,
            'parent_id'        => null,
            'json_data'        => [
                'invoice_number' => 'INV-TEST-0001',
                'invoice_date'   => now()->format('Y-m-d'),
            ],
            'html_snapshot' => '<html><body>Test document</body></html>',
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn (array $attributes) => ['status' => $status]);
    }

    public function quote(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type_id' => DocumentType::factory()->state(['slug' => 'quote', 'name' => 'Quote']),
            'reference'        => 'Q-TEST-0001',
            'json_data'        => ['quote_number' => 'Q-TEST-0001'],
        ]);
    }
}
