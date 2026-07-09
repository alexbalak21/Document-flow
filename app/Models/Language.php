<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'resume_id',
        'name',
        'level',
    ];

    public function resume() { return $this->belongsTo(Resume::class); }
}
