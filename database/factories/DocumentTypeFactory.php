<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentType>
 *
 * Defaults to slug 'invoice' since that's a real template shipped on disk
 * under storage/app/templates/invoice — use ->state(['slug' => '...']) to
 * point at a different real template folder.
 */
class DocumentTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'          => 'Invoice',
            'slug'          => 'invoice',
            'description'   => 'Test document type',
            'version'       => '1.0',
            // Real DB columns are required NOT NULL but are never actually
            // read — DocumentType::getTemplatePathAttribute()/getConfigPathAttribute()
            // always derive the real path from slug + manifest.json at runtime.
            'template_path' => 'unused-see-accessor',
            'config_path'   => 'unused-see-accessor',
            'active'        => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['active' => false]);
    }
}
