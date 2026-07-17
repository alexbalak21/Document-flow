<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        $this->actingAs(User::factory()->create());
    }

    private function invoicePayload(array $overrides = []): array
    {
        return array_merge([
            'customer_name'       => 'John Doe',
            'customer_email'      => 'john.doe@technova.com',
            'product_reference'   => 'PRD-5001',
            'product_name'        => 'Industrial Thermal Camera',
            'product_quantity'    => 2,
            'product_unit_price'  => 499.99,
            'invoice_date'        => '2026-07-16',
            'due_date'            => '2026-07-30',
            'vat_rate'            => 20,
            'delivery_method'     => 'FedEx International Priority',
            'delivery_fee'        => 45.00,
            'hs_code'             => '85258030',
        ], $overrides);
    }

    public function test_storing_a_new_invoice_creates_a_draft_document(): void
    {
        DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);

        $response = $this->post('/documents/invoice/store', $this->invoicePayload());

        $response->assertRedirect(route('documents.page', 'invoice'));
        $this->assertSame(1, Document::count());

        $document = Document::first();
        $this->assertSame(Document::STATUS_DRAFT, $document->status);
        $this->assertSame(1, $document->version);
    }

    public function test_invoice_number_is_auto_generated_when_not_submitted(): void
    {
        DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);

        $this->post('/documents/invoice/store', $this->invoicePayload());

        $document = Document::first();
        $this->assertNotEmpty($document->reference);
        $this->assertStringStartsWith('INV-', $document->reference);
    }

    public function test_invoice_number_is_not_overwritten_when_already_submitted(): void
    {
        DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);

        $this->post('/documents/invoice/store', $this->invoicePayload([
            'invoice_number' => 'INV-MANUAL-0001',
        ]));

        $this->assertSame('INV-MANUAL-0001', Document::first()->reference);
    }

    public function test_generated_html_snapshot_contains_every_submitted_field(): void
    {
        DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);

        $this->post('/documents/invoice/store', $this->invoicePayload());

        $html = Document::first()->html_snapshot;

        // This is the automated regression check for the original bug
        // report: every submitted field must end up visible in the
        // generated document, not silently dropped by the template.
        $this->assertStringContainsString('FedEx International Priority', $html);
        $this->assertStringContainsString('45', $html); // delivery_fee
        $this->assertStringContainsString('85258030', $html); // hs_code
        $this->assertStringContainsString('2026-07-30', $html); // due_date
    }

    public function test_converting_a_quote_sets_parent_id_and_flips_quote_status(): void
    {
        $quoteType   = DocumentType::factory()->create(['slug' => 'quote', 'name' => 'Quote']);
        DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);

        $quote = Document::factory()->create([
            'document_type_id' => $quoteType->id,
            'status'            => Document::STATUS_ACCEPTED,
        ]);

        // Simulate DocumentController::convert() having stashed the source
        // quote id in the session, as it does before redirecting to /create.
        session(['convert_from' => $quote->id]);

        $this->post('/documents/invoice/store', $this->invoicePayload());

        $invoice = Document::where('document_type_id', DocumentType::where('slug', 'invoice')->first()->id)->first();

        $this->assertSame($quote->id, $invoice->parent_id);
        $this->assertSame(Document::STATUS_INVOICED, $quote->fresh()->status);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        auth()->logout();
        DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);

        $response = $this->post('/documents/invoice/store', $this->invoicePayload());

        $response->assertRedirect('/login');
        $this->assertSame(0, Document::count());
    }

    public function test_storing_against_an_inactive_template_returns_404(): void
    {
        DocumentType::factory()->inactive()->create(['slug' => 'invoice', 'name' => 'Invoice']);

        $response = $this->post('/documents/invoice/store', $this->invoicePayload());

        $response->assertNotFound();
    }
}
