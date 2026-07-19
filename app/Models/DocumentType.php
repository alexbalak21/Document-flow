<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'template_path',
        'config_path',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Real template.html path — always derived from slug + manifest.json on
     * disk. The stored `template_path` DB column is intentionally ignored;
     * disk is the single source of truth so templates can be edited/moved
     * without a migration.
     */
    public function getTemplatePathAttribute(): string
    {
        $manifest = $this->readManifest();

        return storage_path("app/templates/{$this->slug}/" . ($manifest['template'] ?? 'template.html'));
    }

    /**
     * Real form.json path — same rationale as getTemplatePathAttribute().
     */
    public function getConfigPathAttribute(): string
    {
        $manifest = $this->readManifest();

        return storage_path("app/templates/{$this->slug}/" . ($manifest['form'] ?? 'form.json'));
    }

    public function readManifest(): array
    {
        $path = storage_path("app/templates/{$this->slug}/manifest.json");

        if (! file_exists($path)) {
            return [];
        }

        return json_decode(file_get_contents($path), true) ?? [];
    }
}
