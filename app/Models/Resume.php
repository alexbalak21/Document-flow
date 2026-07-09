<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'language',
        'profile_image_base64',
        'template_id',
    ];

    public function user()            { return $this->belongsTo(User::class); }
    public function sections()        { return $this->hasMany(Section::class)->orderBy('order_index'); }
    public function experiences()     { return $this->hasMany(Experience::class); }
    public function educations()      { return $this->hasMany(Education::class); }
    public function certifications()  { return $this->hasMany(Certification::class); }
    public function skills()          { return $this->hasMany(Skill::class); }
    public function languages()       { return $this->hasMany(Language::class); }
    public function softSkills()      { return $this->hasMany(SoftSkill::class); }
}
