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
