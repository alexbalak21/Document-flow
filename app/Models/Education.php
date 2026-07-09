<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    protected $table = 'educations';

    protected $fillable = [
        'resume_id',
        'school',
        'diploma',
        'year',
    ];

    public function resume() { return $this->belongsTo(Resume::class); }
}
