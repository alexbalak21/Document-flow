<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentConvertStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        $this->actingAs(User::factory()->create());
    }

    // --- updateStatus -------------------------------------------------

    public function test_status_rejects_invalid_values(): void
    {
        $type = DocumentType::factory()->create(['slug' => 'quote', 'name' => 'Quote']);
        $document = Document::factory()->create(['document_type_id' => $type->id, 'status' => Document::STATUS_DRAFT]);

        $response = $this->post("/documents/{$document->id}/status", ['status' => 'not-a-real-status']);

        $response->assertSessionHasErrors('status');
        $this->assertSame(Document::STATUS_DRAFT, $document->fresh()->status);
    }

    public function test_status_rejects_change_on_already_invoiced_document(): void
    {
        $type = DocumentType::factory()->create(['slug' => 'quote', 'name' => 'Quote']);
        $document = Document::factory()->create(['document_type_id' => $type->id, 'status' => Document::STATUS_INVOICED]);

        $response = $this->post("/documents/{$document->id}/status", ['status' => 'accepted']);

        $response->assertSessionHas('error');
        $this->assertSame(Document::STATUS_INVOICED, $document->fresh()->status);
    }

    public function test_status_update_succeeds_for_a_valid_transition(): void
    {
        $type = DocumentType::factory()->create(['slug' => 'quote', 'name' => 'Quote']);
        $document = Document::factory()->create(['document_type_id' => $type->id, 'status' => Document::STATUS_SENT]);

        $response = $this->post("/documents/{$document->id}/status", ['status' => 'accepted']);

        $response->assertSessionHas('success');
        $this->assertSame(Document::STATUS_ACCEPTED, $document->fresh()->status);
    }

    // --- convert --------------------------------------------------------

    public function test_convert_rejects_non_quote_documents(): void
    {
        $type = DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);
        $document = Document::factory()->create(['document_type_id' => $type->id, 'status' => Document::STATUS_ACCEPTED]);

        $response = $this->post("/documents/{$document->id}/convert");

        $response->assertSessionHas('error', 'Only quotes can be converted.');
    }

    public function test_convert_rejects_non_accepted_quotes(): void
    {
        $type = DocumentType::factory()->create(['slug' => 'quote', 'name' => 'Quote']);
        $document = Document::factory()->create(['document_type_id' => $type->id, 'status' => Document::STATUS_SENT]);

        $response = $this->post("/documents/{$document->id}/convert");

        $response->assertSessionHas('error', 'Only accepted quotes can be converted to an invoice.');
    }

    public function test_convert_rejects_already_converted_quotes(): void
    {
        $quoteType   = DocumentType::factory()->create(['slug' => 'quote', 'name' => 'Quote']);
        $invoiceType = DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);

        $quote = Document::factory()->create(['document_type_id' => $quoteType->id, 'status' => Document::STATUS_ACCEPTED]);
        $invoice = Document::factory()->create(['document_type_id' => $invoiceType->id, 'parent_id' => $quote->id]);

        $response = $this->post("/documents/{$quote->id}/convert");

        $response->assertRedirect(route('documents.show', $invoice));
        $response->assertSessionHas('error', 'This quote was already converted.');
    }

    public function test_convert_stores_session_data_and_redirects_to_invoice_create(): void
    {
        DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);
        $quoteType = DocumentType::factory()->create(['slug' => 'quote', 'name' => 'Quote']);
        $quote = Document::factory()->create([
            'document_type_id' => $quoteType->id,
            'status'            => Document::STATUS_ACCEPTED,
            'reference'         => 'Q-0099',
        ]);

        $response = $this->post("/documents/{$quote->id}/convert");

        $response->assertRedirect(route('documents.create', 'invoice'));
        $this->assertSame($quote->id, session('convert_from'));
    }

    public function test_convert_fails_gracefully_when_invoice_template_not_installed(): void
    {
        // Deliberately do NOT create an 'invoice' DocumentType row.
        $quoteType = DocumentType::factory()->create(['slug' => 'quote', 'name' => 'Quote']);
        $quote = Document::factory()->create(['document_type_id' => $quoteType->id, 'status' => Document::STATUS_ACCEPTED]);

        $response = $this->post("/documents/{$quote->id}/convert");

        $response->assertSessionHas('error', 'Invoice template is not installed.');
    }
}
