<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class PreOwnedListing extends Model
{
    protected $fillable = [
        'title', 'make', 'model', 'year', 'category', 'kms',
        'price', 'condition', 'colour', 'rego', 'description',
        'featured_image', 'images', 'active', 'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'price'  => 'decimal:2',
    ];

    protected function images(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? array_values(json_decode($value, true) ?? []) : [],
            set: fn ($value) => json_encode(array_values(array_filter((array) $value))),
        );
    }

    public function getFormattedPriceAttribute(): ?string
    {
        return $this->price ? '$' . number_format($this->price, 0) : null;
    }

    public function getSlugAttribute(): string
    {
        return $this->id . '-' . str($this->title)->slug();
    }
}
