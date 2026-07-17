<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * End-to-end version of TemplateDataCoverageTest: instead of calling
 * renderHtml() directly, this posts to the real
 * POST /documents/{slug}/preview route — the same endpoint the "Preview"
 * button in the UI calls — exercising the full pipeline: auth middleware,
 * EntityResolver::saveFromRequest(), computeTotals(), then renderHtml().
 *
 * Kept separate from TemplateDataCoverageTest (which runs against every
 * template) because this one is slower (real HTTP + DB) — use it as a
 * smoke test for the one or two templates you touch most often, and as
 * the direct regression test for the delivery_fee / hs_code bug.
 */
class DocumentPreviewDataCoverageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_invoice_preview_contains_every_submitted_field(): void
    {
        $this->seedInvoiceDocumentType();
        $this->actingAs(User::factory()->create());

        $payload = [
            'customer_name'       => 'John Doe',
            'customer_company'    => 'TechNova LLC',
            'customer_department' => 'Procurement',
            'customer_street'     => '42 Innovation Street',
            'customer_city'       => 'San Francisco',
            'customer_zip'        => '94107',
            'customer_country'    => 'USA',
            'customer_phone'      => '+1 415 555 0199',
            'customer_email'      => 'john.doe@technova.com',
            'customer_vat_number' => 'US123456789',

            'product_reference'   => 'PRD-5001',
            'product_name'        => 'Industrial Thermal Camera',
            'product_unit'        => 'unit',
            'product_quantity'    => 2,
            'product_unit_price'  => 499.99,

            'invoice_number'   => 'INV-2026-0042',
            'invoice_date'     => '2026-07-16',
            'due_date'         => '2026-07-30',
            'delivery_date'    => '2026-07-20',
            'quote_reference'  => 'QTE-2026-112',
            'purchase_order'   => 'PO-8891',
            'vat_rate'         => 20,

            // The exact optional "Delivery" fields that were silently
            // dropped in the original bug report.
            'delivery_method'  => 'FedEx International Priority',
            'delivery_fee'     => 45.00,
            'hs_code'          => '85258030',
            'tracking_number'  => 'FDX772019334US',

            'notes' => 'Thank you for your business.',
            'terms' => 'Payment due within 14 days.',
        ];

        $response = $this->post('/documents/invoice/preview', $payload);

        $response->assertOk();
        $html = $response->getContent();

        // Regression-lock every field from the original bug report.
        $mustAppear = [
            'invoice_number'  => 'INV-2026-0042',
            'due_date'        => '2026-07-30',
            'delivery_date'   => '2026-07-20',
            'quote_reference' => 'QTE-2026-112',
            'purchase_order'  => 'PO-8891',
            'delivery_method' => 'FedEx International Priority',
            'delivery_fee'    => '45',
            'hs_code'         => '85258030',
            'tracking_number' => 'FDX772019334US',
            'customer_email'  => 'john.doe@technova.com',
            'product_name'    => 'Industrial Thermal Camera',
        ];

        foreach ($mustAppear as $field => $expectedValue) {
            $this->assertStringContainsString(
                $expectedValue,
                $html,
                "'{$field}' was submitted with value '{$expectedValue}' but is missing from the invoice preview HTML."
            );
        }
    }

    /**
     * The DocumentType row itself only needs a matching slug — template and
     * form paths are derived from disk at runtime (see DocumentType model),
     * so no other columns are required for renderHtml()/preview() to work.
     */
    private function seedInvoiceDocumentType(): void
    {
        DocumentType::create([
            'name'          => 'Invoice',
            'slug'          => 'invoice',
            'active'        => true,
            // Required NOT NULL columns, but never actually read — see
            // DocumentType::getTemplatePathAttribute()/getConfigPathAttribute().
            'template_path' => 'unused-see-accessor',
            'config_path'   => 'unused-see-accessor',
        ]);
    }
}
