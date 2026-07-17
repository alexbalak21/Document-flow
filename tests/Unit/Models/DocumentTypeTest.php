<?php

namespace Tests\Unit\Models;

use App\Models\DocumentType;
use Tests\TestCase;

class DocumentTypeTest extends TestCase
{
    public function test_template_path_is_derived_from_slug_not_from_the_stored_column(): void
    {
        // Deliberately store a bogus value in the DB column to prove the
        // accessor ignores it and always derives the real path from disk.
        $type = new DocumentType([
            'slug'          => 'invoice',
            'template_path' => '/this/value/must/be/ignored.html',
        ]);

        $this->assertSame(
            storage_path('app/templates/invoice/template.html'),
            $type->template_path
        );
    }

    public function test_config_path_is_derived_from_slug_not_from_the_stored_column(): void
    {
        $type = new DocumentType([
            'slug'        => 'invoice',
            'config_path' => '/this/value/must/be/ignored.json',
        ]);

        $this->assertSame(
            storage_path('app/templates/invoice/form.json'),
            $type->config_path
        );
    }

    public function test_manifest_is_read_from_the_real_manifest_json_on_disk(): void
    {
        $type = new DocumentType(['slug' => 'invoice']);

        $manifest = $type->readManifest();

        $this->assertSame('invoice', $manifest['slug']);
        $this->assertContains('customer', $manifest['entities']);
        $this->assertContains('product', $manifest['entities']);
    }

    public function test_a_nonexistent_slug_returns_an_empty_manifest_instead_of_crashing(): void
    {
        $type = new DocumentType(['slug' => 'this-template-does-not-exist']);

        // A bad/missing slug must not throw — file_exists() guards it and
        // readManifest() returns [] instead of a fatal error.
        $this->assertSame([], $type->readManifest());
    }
}
