<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'resume_id',
        'title',
        'icon',
        'markdown_content',
        'order_index',
        'section_type',
    ];

    public function resume() { return $this->belongsTo(Resume::class); }
}
