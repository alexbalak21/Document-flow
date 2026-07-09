<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'company',
        'department',
        'street',
        'city',
        'zip',
        'country',
        'phone',
        'email',
        'vat_number',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->company
            ? "{$this->name} — {$this->company}"
            : $this->name;
    }

    /**
     * Return the entity data array — used by the shared entity system.
     */
    public function toEntityArray(): array
    {
        return [
            'name'       => $this->name,
            'company'    => $this->company,
            'department' => $this->department,
            'street'     => $this->street,
            'city'       => $this->city,
            'zip'        => $this->zip,
            'country'    => $this->country,
            'phone'      => $this->phone,
            'email'      => $this->email,
            'vat_number' => $this->vat_number,
        ];
    }
}
