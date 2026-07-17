<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PdfControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private function documentWithSnapshot(array $overrides = []): Document
    {
        $type = DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);

        return Document::factory()->create(array_merge([
            'document_type_id' => $type->id,
            'reference'         => 'INV-2026-0042',
            'html_snapshot'     => '<html><body>Invoice</body></html>',
        ], $overrides));
    }

    public function test_download_returns_pdf_with_attachment_disposition(): void
    {
        Http::fake([
            '*/pdf' => Http::response('%PDF-1.4 fake pdf bytes', 200, ['Content-Type' => 'application/pdf']),
        ]);

        $document = $this->documentWithSnapshot();

        $response = $this->get("/pdf/{$document->id}/download");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('inv-2026-0042.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_preview_returns_pdf_with_inline_disposition(): void
    {
        Http::fake([
            '*/pdf' => Http::response('%PDF-1.4 fake pdf bytes', 200, ['Content-Type' => 'application/pdf']),
        ]);

        $document = $this->documentWithSnapshot();

        $response = $this->get("/pdf/{$document->id}/preview");

        $response->assertOk();
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
    }

    public function test_missing_html_snapshot_redirects_back_with_error_and_makes_no_http_call(): void
    {
        Http::fake();

        $document = $this->documentWithSnapshot(['html_snapshot' => '']);

        $response = $this->from('/history/' . $document->id)->get("/pdf/{$document->id}/download");

        $response->assertRedirect('/history/' . $document->id);
        $response->assertSessionHas('error', 'No HTML snapshot found for this document.');
        Http::assertNothingSent();
    }

    public function test_pdf_service_failure_returns_friendly_error_not_a_raw_exception(): void
    {
        Http::fake([
            '*/pdf' => Http::response(['error' => 'renderer crashed'], 500),
        ]);

        $document = $this->documentWithSnapshot();

        $response = $this->from('/history/' . $document->id)->get("/pdf/{$document->id}/download");

        $response->assertRedirect('/history/' . $document->id);
        $response->assertSessionHas('error');
        $this->assertStringContainsString(
            'renderer crashed',
            session('error'),
            'The underlying service error should be surfaced to help debugging, without leaking a raw stack trace.'
        );
    }

    public function test_filename_is_slugified_from_the_document_reference(): void
    {
        Http::fake(['*/pdf' => Http::response('%PDF fake', 200)]);

        $document = $this->documentWithSnapshot(['reference' => 'Some Weird Reference / 2026']);

        $response = $this->get("/pdf/{$document->id}/download");

        $this->assertStringContainsString('some-weird-reference-2026.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_guest_cannot_download_pdf(): void
    {
        auth()->logout();
        $document = $this->documentWithSnapshot();

        $response = $this->get("/pdf/{$document->id}/download");

        $response->assertRedirect('/login');
    }
}
