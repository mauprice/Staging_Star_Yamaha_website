<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'size', 'colour', 'barcode', 'quantity', 'price_override', 'active'];

    protected $casts = [
        'active'         => 'boolean',
        'quantity'       => 'integer',
        'price_override' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getEffectivePriceAttribute(): string
    {
        return (string) ($this->price_override ?? $this->product->price);
    }

    public function getLabelAttribute(): string
    {
        return trim(($this->size ?? '') . ' / ' . ($this->colour ?? ''), ' /');
    }
}
