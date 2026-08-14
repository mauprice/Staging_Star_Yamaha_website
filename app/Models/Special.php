<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Special extends Model
{
    protected $fillable = [
        'title', 'description',
        'regular_price', 'special_price',
        'featured_image', 'images',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'regular_price' => 'decimal:2',
        'special_price' => 'decimal:2',
    ];

    protected function images(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? array_values(json_decode($value, true) ?? []) : [],
            set: fn ($value) => json_encode(array_values(array_filter((array) $value))),
        );
    }

    public function getSlugAttribute(): string
    {
        return $this->id . '-' . str($this->title)->slug();
    }

    public function getSavingsAttribute(): ?string
    {
        if ($this->regular_price && $this->special_price) {
            $saving = $this->regular_price - $this->special_price;
            return $saving > 0 ? '$' . number_format($saving, 0) : null;
        }

        return null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
