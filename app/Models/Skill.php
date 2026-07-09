<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = [
        'resume_id',
        'name',
        'icon',
        'level_type',
        'level_value',
    ];

    public function resume() { return $this->belongsTo(Resume::class); }
}
