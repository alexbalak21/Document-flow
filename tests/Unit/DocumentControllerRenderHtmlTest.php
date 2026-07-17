<?php

namespace Tests\Unit;

use App\Http\Controllers\DocumentController;
use App\Models\DocumentType;
use App\Services\DocumentNumberService;
use App\Services\EntityResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentControllerRenderHtmlTest extends TestCase
{
    use RefreshDatabase;

    private function controller(): DocumentController
    {
        return new DocumentController(new EntityResolver(), new DocumentNumberService());
    }

    public function test_simple_placeholders_are_substituted(): void
    {
        $type = new DocumentType(['slug' => 'invoice']);

        $html = $this->controller()->renderHtml($type, [
            'invoice_number' => 'INV-0001',
            'invoice_date'   => '2026-07-16',
            'no_fx'          => '1',
        ], 'en');

        $this->assertStringContainsString('INV-0001', $html);
        $this->assertStringContainsString('2026-07-16', $html);
    }

    public function test_conditional_block_renders_when_field_is_truthy(): void
    {
        $type = new DocumentType(['slug' => 'invoice']);

        $html = $this->controller()->renderHtml($type, [
            'no_fx'          => '1',
            'purchase_order' => 'PO-1234',
        ], 'en');

        $this->assertStringContainsString('PO-1234', $html);
    }

    public function test_conditional_block_is_stripped_when_field_is_empty(): void
    {
        $type = new DocumentType(['slug' => 'invoice']);

        $html = $this->controller()->renderHtml($type, [
            'no_fx'          => '1',
            'purchase_order' => '', // explicitly empty
        ], 'en');

        $this->assertStringNotContainsString('PO-1234', $html);
        // The label text inside the {{#purchase_order}} block must not leak either.
        $this->assertStringNotContainsString('Purchase Order', $html);
    }

    public function test_nested_conditional_block_resolves_independently_of_its_parent(): void
    {
        // Regression test: delivery_fee/hs_code/tracking_number live inside
        // {{#delivery_method}}...{{/delivery_method}}. If delivery_method is
        // set but delivery_fee is not, delivery_fee's own nested block must
        // still resolve to "hidden" without breaking the outer block.
        $type = new DocumentType(['slug' => 'invoice']);

        $html = $this->controller()->renderHtml($type, [
            'no_fx'           => '1',
            'delivery_method' => 'FedEx International Priority',
            'hs_code'         => '85258030',
            // delivery_fee and tracking_number intentionally omitted
        ], 'en');

        $this->assertStringContainsString('FedEx International Priority', $html);
        $this->assertStringContainsString('85258030', $html);
        $this->assertStringNotContainsString('delivery_fee', $html);
    }

    public function test_unresolved_placeholders_are_stripped_from_final_output(): void
    {
        $type = new DocumentType(['slug' => 'invoice']);

        $html = $this->controller()->renderHtml($type, ['no_fx' => '1'], 'en');

        $this->assertStringNotContainsString('{{', $html);
        $this->assertStringNotContainsString('}}', $html);
    }

    public function test_i18n_strings_are_injected_and_substituted(): void
    {
        $type = new DocumentType(['slug' => 'invoice']);

        $html = $this->controller()->renderHtml($type, ['no_fx' => '1'], 'en');
        $this->assertStringContainsString('INVOICE', $html); // i18n_doc_title (en)

        $htmlFr = $this->controller()->renderHtml($type, ['no_fx' => '1'], 'fr');
        $this->assertStringContainsString('FACTURE', $htmlFr); // i18n_doc_title (fr)
    }

    public function test_i18n_falls_back_to_english_for_unknown_language(): void
    {
        $type = new DocumentType(['slug' => 'invoice']);

        $html = $this->controller()->renderHtml($type, ['no_fx' => '1'], 'de');

        // No German translation exists for this template, so it must fall
        // back to English rather than leaving raw i18n_* placeholders in.
        $this->assertStringContainsString('INVOICE', $html);
        $this->assertStringNotContainsString('{{i18n_doc_title}}', $html);
    }

    public function test_values_are_html_escaped(): void
    {
        $type = new DocumentType(['slug' => 'invoice']);

        $html = $this->controller()->renderHtml($type, [
            'no_fx'          => '1',
            'purchase_order' => '<script>alert(1)</script>',
        ], 'en');

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }
}
