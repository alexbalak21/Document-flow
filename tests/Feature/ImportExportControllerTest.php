<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportExportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        $this->actingAs(User::factory()->create());
    }

    // --- documentModel ----------------------------------------------------

    public function test_document_model_includes_every_form_field_and_entity_blocks(): void
    {
        DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);

        $response = $this->get('/export/document-model/invoice');

        $response->assertOk();
        $json = $response->json();

        $this->assertSame('invoice', $json['_template']);
        $this->assertArrayHasKey('customer', $json);
        $this->assertArrayHasKey('product', $json);
        $this->assertArrayHasKey('name', $json['customer']);
        $this->assertArrayHasKey('reference', $json['product']);

        // Every form.json field for invoice must appear at the top level too.
        $form = json_decode(file_get_contents(storage_path('app/templates/invoice/form.json')), true);
        foreach ($form as $section) {
            foreach ($section['fields'] as $field) {
                $this->assertArrayHasKey(
                    $field['name'],
                    $json,
                    "documentModel() is missing form field '{$field['name']}'"
                );
            }
        }
    }

    public function test_document_model_flags_optional_section_fields(): void
    {
        DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);

        $response = $this->get('/export/document-model/invoice');
        $json = $response->json();

        $this->assertArrayHasKey('_optional_sections', $json);
        $this->assertArrayHasKey('delivery_fee', $json['_optional_sections']);
        $this->assertArrayHasKey('hs_code', $json['_optional_sections']);
    }

    // --- documentExport -----------------------------------------------------

    public function test_document_export_includes_metadata_and_json_data(): void
    {
        $type = DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);
        $document = Document::factory()->create([
            'document_type_id' => $type->id,
            'reference'         => 'INV-0001',
            'version'           => 2,
            'status'            => Document::STATUS_SENT,
            'json_data'         => ['invoice_number' => 'INV-0001', 'custom_field' => 'value'],
        ]);

        $response = $this->get("/export/document/{$document->id}");

        $response->assertOk();
        $response->assertJson([
            '_template'    => 'invoice',
            '_reference'   => 'INV-0001',
            '_version'     => 2,
            '_status'      => 'sent',
            'custom_field' => 'value',
        ]);
    }

    // --- documentImport -------------------------------------------------

    public function test_document_import_flattens_customer_and_product_keys(): void
    {
        DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);

        $json = json_encode([
            '_template' => 'invoice',
            'customer'  => ['name' => 'Jane', 'email' => 'jane@example.com'],
            'product'   => ['reference' => 'PRD-1', 'quantity' => 3, 'unit_price' => 9.99],
        ]);

        $response = $this->postJson('/import/document/invoice', ['json_text' => $json]);

        $response->assertOk();
        $response->assertJsonFragment([
            'customer_name'      => 'Jane',
            'customer_email'     => 'jane@example.com',
            'product_reference'  => 'PRD-1',
            'product_quantity'   => 3,
            'product_unit_price' => 9.99,
        ]);
    }

    public function test_document_import_auto_activates_optional_sections_with_values(): void
    {
        DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);

        $json = json_encode(['delivery_fee' => 45.00]);

        $response = $this->postJson('/import/document/invoice', ['json_text' => $json]);

        $response->assertOk();
        $sections = $response->json('sections_to_activate');
        $this->assertContains('section-delivery', $sections);
    }

    public function test_document_import_does_not_activate_sections_for_empty_values(): void
    {
        DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);

        $json = json_encode(['delivery_fee' => '', 'hs_code' => null]);

        $response = $this->postJson('/import/document/invoice', ['json_text' => $json]);

        $this->assertSame([], $response->json('sections_to_activate'));
    }

    public function test_document_import_rejects_invalid_json(): void
    {
        DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);

        $response = $this->postJson('/import/document/invoice', ['json_text' => '{not valid json']);

        $response->assertStatus(422);
        $response->assertJsonStructure(['error']);
    }

    public function test_document_import_rejects_request_with_no_json_provided(): void
    {
        DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);

        $response = $this->postJson('/import/document/invoice', []);

        $response->assertStatus(422);
    }

    // --- customer model/export/import ------------------------------------

    public function test_customer_model_lists_every_expected_field(): void
    {
        $response = $this->get('/export/customer-model');

        $response->assertOk();
        $response->assertJsonStructure([
            'name', 'company', 'department', 'street', 'city', 'zip', 'country', 'phone', 'email', 'vat_number',
        ]);
    }

    public function test_customer_export_returns_entity_array(): void
    {
        $customer = Customer::factory()->create(['name' => 'Jane Doe']);

        $response = $this->get("/export/customer/{$customer->id}");

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Jane Doe']);
    }

    public function test_customer_import_only_returns_allowed_fields(): void
    {
        $json = json_encode(['name' => 'Jane', 'email' => 'jane@example.com', 'not_a_real_field' => 'ignored']);

        $response = $this->postJson('/import/customer', ['json_text' => $json]);

        $response->assertOk();
        $fields = $response->json('fields');
        $this->assertArrayHasKey('name', $fields);
        $this->assertArrayNotHasKey('not_a_real_field', $fields);
    }

    // --- product model/export/import --------------------------------------

    public function test_product_model_lists_every_expected_field(): void
    {
        $response = $this->get('/export/product-model');

        $response->assertOk();
        $response->assertJsonStructure(['reference', 'name', 'description', 'product_unit', 'unit_price', 'page_url']);
    }

    public function test_product_export_returns_entity_array(): void
    {
        $product = Product::factory()->create(['reference' => 'PRD-1']);

        $response = $this->get("/export/product/{$product->id}");

        $response->assertOk();
        $response->assertJsonFragment(['reference' => 'PRD-1']);
    }

    public function test_product_import_maps_fields_with_product_prefix(): void
    {
        $json = json_encode(['reference' => 'PRD-1', 'unit_price' => 9.99]);

        $response = $this->postJson('/import/product', ['json_text' => $json]);

        $response->assertOk();
        $response->assertJsonFragment([
            'fields' => ['product_reference' => 'PRD-1', 'product_unit_price' => 9.99],
        ]);
    }
}
