<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'session_id', 'user_id', 'product_id', 'product_variant_id', 'quantity',
        'part_number', 'part_description', 'unit_price_snapshot', 'currency',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price_snapshot' => 'decimal:2',
    ];

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

    public function isPart(): bool
    {
        return $this->product_id === null;
    }

    public function getUnitPriceAttribute(): float
    {
        if ($this->isPart()) {
            return (float) $this->unit_price_snapshot;
        }

        return (float) ($this->variant?->effective_price ?? $this->product->price);
    }

    public function getLineTotalAttribute(): float
    {
        return round($this->unit_price * $this->quantity, 2);
    }

    /**
     * OEM parts have no stock/availability data at all (see Part model) -
     * they're treated as always orderable, capped only by the same 99-unit
     * ceiling every cart line uses.
     */
    public function getAvailableStockAttribute(): int
    {
        if ($this->isPart()) {
            return 99;
        }

        return $this->variant?->quantity ?? $this->product->stock_quantity;
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->isPart()) {
            return $this->part_description
                ? "{$this->part_number} — {$this->part_description}"
                : $this->part_number;
        }

        $name = $this->product->name;

        return $this->variant ? "{$name} ({$this->variant->label})" : $name;
    }
}
