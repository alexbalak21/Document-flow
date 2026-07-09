<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    protected $fillable = [
        'resume_id',
        'name',
        'organization',
        'year',
    ];

    public function resume() { return $this->belongsTo(Resume::class); }
}
