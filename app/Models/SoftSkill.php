<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoftSkill extends Model
{
    protected $fillable = [
        'resume_id',
        'name',
        'icon',
        'description',
    ];

    public function resume() { return $this->belongsTo(Resume::class); }
}
