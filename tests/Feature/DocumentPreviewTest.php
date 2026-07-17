<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentPreviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        $this->actingAs(User::factory()->create());
    }

    public function test_preview_returns_html_and_does_not_persist_a_document(): void
    {
        DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);

        $response = $this->post('/documents/invoice/preview', [
            'customer_name'      => 'John Doe',
            'customer_email'     => 'john@example.com',
            'product_reference'  => 'PRD-1',
            'product_name'       => 'Widget',
            'product_quantity'   => 1,
            'product_unit_price' => 10,
            'invoice_number'     => 'INV-PREVIEW-ONLY',
            'vat_rate'           => 0,
        ]);

        $response->assertOk();
        $response->assertSee('John Doe', false);
        $response->assertSee('INV-PREVIEW-ONLY', false);

        $this->assertSame(0, Document::count(), 'Preview must never write a Document row to the database.');
    }

    public function test_preview_does_not_create_a_new_customer_permanently_reused_by_mistake(): void
    {
        DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);

        // Note: EntityResolver::saveFromRequest() IS called on preview (by
        // design, so the picker stays in sync), so a Customer row can be
        // created here. This test documents that behavior explicitly rather
        // than assuming — if this ever changes, the assertion below should
        // be revisited along with it.
        $this->post('/documents/invoice/preview', [
            'customer_name'      => 'Preview Customer',
            'customer_email'     => 'preview@example.com',
            'product_reference'  => 'PRD-1',
            'product_name'       => 'Widget',
            'product_quantity'   => 1,
            'product_unit_price' => 10,
            'vat_rate'           => 0,
        ]);

        $this->assertDatabaseHas('customers', ['email' => 'preview@example.com']);
        $this->assertSame(0, Document::count());
    }
}
