<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    protected $fillable = [
        'resume_id',
        'position_title',
        'company',
        'location',
        'start_date',
        'end_date',
        'description',
    ];

    public function resume() { return $this->belongsTo(Resume::class); }
}
