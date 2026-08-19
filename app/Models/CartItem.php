<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = ['session_id', 'user_id', 'product_id', 'product_variant_id', 'quantity'];

    protected $casts = ['quantity' => 'integer'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Authenticated users own their cart by user_id (survives session
     * rotation on login/logout); guests are scoped to the current session.
     */
    public function scopeForCurrentCart(Builder $query): Builder
    {
        return auth()->check()
            ? $query->where('user_id', auth()->id())
            : $query->where('session_id', session()->getId())->whereNull('user_id');
    }

    public function getUnitPriceAttribute(): float
    {
        return (float) ($this->variant?->effective_price ?? $this->product->price);
    }

    public function getLineTotalAttribute(): float
    {
        return round($this->unit_price * $this->quantity, 2);
    }

    public function getAvailableStockAttribute(): int
    {
        return $this->variant?->quantity ?? $this->product->stock_quantity;
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->product->name;

        return $this->variant ? "{$name} ({$this->variant->label})" : $name;
    }
}
