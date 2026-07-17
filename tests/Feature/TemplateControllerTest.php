<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TemplateControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        $this->actingAs(User::factory()->create());
    }

    public function test_toggle_flips_active_state(): void
    {
        $template = DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice', 'active' => true]);

        $this->post("/templates/{$template->id}/toggle");
        $this->assertFalse($template->fresh()->active);

        $this->post("/templates/{$template->id}/toggle");
        $this->assertTrue($template->fresh()->active);
    }

    public function test_disabled_template_is_unreachable_via_the_documents_page(): void
    {
        $template = DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice', 'active' => true]);

        $this->post("/templates/{$template->id}/toggle"); // now disabled

        $response = $this->get('/documents/invoice');
        $response->assertNotFound();
    }

    public function test_rescan_installs_real_templates_found_on_disk(): void
    {
        // storage/app/templates/* on disk is the source of truth; none of
        // these DocumentType rows exist yet in this fresh test DB.
        $this->assertSame(0, DocumentType::count());

        $response = $this->post('/templates/rescan');

        $response->assertRedirect(route('templates.index'));
        $this->assertGreaterThanOrEqual(1, DocumentType::where('slug', 'invoice')->count());
    }

    public function test_rescan_does_not_duplicate_already_installed_templates(): void
    {
        $this->post('/templates/rescan');
        $countAfterFirstScan = DocumentType::count();

        $this->post('/templates/rescan');
        $countAfterSecondScan = DocumentType::count();

        $this->assertSame($countAfterFirstScan, $countAfterSecondScan);
    }

    public function test_destroy_blocks_deletion_when_documents_exist(): void
    {
        $template = DocumentType::factory()->create(['slug' => 'invoice', 'name' => 'Invoice']);
        Document::factory()->create(['document_type_id' => $template->id]);

        $response = $this->delete("/templates/{$template->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('document_types', ['id' => $template->id]);
    }

    public function test_destroy_removes_template_when_no_documents_exist(): void
    {
        // Use a slug that is NOT a real shipped template, so we don't
        // accidentally delete real files other tests in the suite rely on.
        $template = DocumentType::factory()->create([
            'slug' => 'disposable-test-template',
            'name' => 'Disposable',
        ]);

        $response = $this->delete("/templates/{$template->id}");

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('document_types', ['id' => $template->id]);
    }

    public function test_upload_rejects_a_zip_with_no_manifest_json(): void
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'tpl') . '.zip';
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE);
        $zip->addFromString('readme.txt', 'this package has no manifest.json');
        $zip->close();

        $file = new UploadedFile($zipPath, 'bad-template.zip', 'application/zip', null, true);

        $response = $this->post('/templates/upload', ['package' => $file]);

        $response->assertSessionHas('error', 'manifest.json not found in ZIP.');
        unlink($zipPath);
    }

    public function test_upload_rejects_a_zip_with_invalid_json_manifest(): void
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'tpl') . '.zip';
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE);
        $zip->addFromString('manifest.json', '{not valid json');
        $zip->close();

        $file = new UploadedFile($zipPath, 'bad-manifest.zip', 'application/zip', null, true);

        $response = $this->post('/templates/upload', ['package' => $file]);

        $response->assertSessionHas('error', 'manifest.json is invalid JSON.');
        unlink($zipPath);
    }

    public function test_upload_rejects_non_zip_files(): void
    {
        $file = UploadedFile::fake()->create('not-a-zip.txt', 10, 'text/plain');

        $response = $this->post('/templates/upload', ['package' => $file]);

        $response->assertSessionHasErrors('package');
    }
}
