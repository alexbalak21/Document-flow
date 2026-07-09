<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'reference',
        'name',
        'description',
        'product_unit',
        'unit_price',
        'page_url',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    /**
     * Formatted price for display: e.g. "530.00"
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format((float) $this->unit_price, 2);
    }

    /**
     * Return the entity data array — used by the shared entity system.
     */
    public function toEntityArray(): array
    {
        return [
            'reference'    => $this->reference,
            'name'         => $this->name,
            'description'  => $this->description,
            'product_unit' => $this->product_unit,
            'unit_price'   => (float) $this->unit_price,
            'page_url'     => $this->page_url,
        ];
    }
}
