<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'template_path',
        'config_path',
        'preview_image',
        'active',
        'icon',
        'sidebar_label',
        'sidebar_group',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(\App\Models\Document::class);
    }

    /**
     * Always derive template/config paths from slug at runtime.
     * This makes the stored paths in the DB irrelevant — moving the
     * project or installing on a different machine never breaks paths.
     */
    public function getTemplateDirAttribute(): string
    {
        return storage_path('app/templates/' . $this->slug);
    }

    public function getTemplatePathAttribute(): string
    {
        $manifest = $this->readManifest();
        $file     = $manifest['template'] ?? 'template.html';
        return $this->template_dir . '/' . $file;
    }

    public function getConfigPathAttribute(): string
    {
        $manifest = $this->readManifest();
        $file     = $manifest['form'] ?? 'form.json';
        return $this->template_dir . '/' . $file;
    }

    private function readManifest(): array
    {
        $path = $this->template_dir . '/manifest.json';
        if (! file_exists($path)) return [];
        return json_decode(file_get_contents($path), true) ?? [];
    }

    /**
     * The label shown in the sidebar — falls back to name.
     */
    public function getSidebarLabelDisplayAttribute(): string
    {
        return $this->sidebar_label ?: $this->name;
    }

    /**
     * The Bootstrap Icon class for this template.
     */
    public function getIconDisplayAttribute(): string
    {
        return $this->icon ?: 'bi-file-earmark-text';
    }
}