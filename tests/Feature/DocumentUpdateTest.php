<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        $this->actingAs(User::factory()->create());
    }

    public function test_editing_a_draft_document_succeeds_and_bumps_version(): void
    {
        $type = DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);
        $document = Document::factory()->create([
            'document_type_id' => $type->id,
            'status'            => Document::STATUS_DRAFT,
            'version'           => 1,
        ]);

        $response = $this->put("/documents/{$document->id}/update", [
            'customer_name'      => 'Jane Updated',
            'customer_email'     => 'jane@example.com',
            'product_reference'  => 'PRD-1',
            'product_name'       => 'Widget',
            'product_quantity'   => 1,
            'product_unit_price' => 10,
            'invoice_number'     => 'INV-KEEP-0001',
            'vat_rate'           => 0,
        ]);

        $response->assertRedirect(route('documents.show', $document));
        $document->refresh();

        $this->assertSame(2, $document->version);
        $this->assertStringContainsString('Jane Updated', $document->html_snapshot);
    }

    public function test_editing_a_non_draft_document_is_rejected_and_leaves_data_unchanged(): void
    {
        $type = DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);
        $document = Document::factory()->create([
            'document_type_id' => $type->id,
            'status'            => Document::STATUS_SENT,
            'version'           => 1,
            'reference'         => 'INV-ORIGINAL',
        ]);

        $response = $this->put("/documents/{$document->id}/update", [
            'customer_name' => 'Should Not Save',
        ]);

        $response->assertRedirect(route('documents.show', $document));
        $response->assertSessionHas('error');

        $document->refresh();
        $this->assertSame(1, $document->version);
        $this->assertSame('INV-ORIGINAL', $document->reference);
    }

    public function test_updated_reference_falls_back_to_existing_reference_when_number_field_missing(): void
    {
        $type = DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);
        $document = Document::factory()->create([
            'document_type_id' => $type->id,
            'status'            => Document::STATUS_DRAFT,
            'reference'         => 'INV-KEEP-THIS',
        ]);

        $this->put("/documents/{$document->id}/update", [
            'customer_name' => 'No invoice_number field submitted at all',
        ]);

        $this->assertSame('INV-KEEP-THIS', $document->fresh()->reference);
    }
}
