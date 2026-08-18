<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    public const CATEGORIES = [
        'Clothing & Apparel',
        'Helmets',
        'Riding Gear',
        'Accessories',
        'Oil & Chemicals',
        'Parts',
        'Merchandise',
        'Other',
    ];

    public const CLOTHING_CATEGORIES = ['Clothing & Apparel', 'Helmets', 'Riding Gear'];

    protected $fillable = [
        'category', 'brand', 'name', 'description', 'part_number', 'barcode',
        'price', 'weight_kg', 'length_mm', 'width_mm', 'height_mm',
        'stock_quantity', 'active', 'sort_order',
    ];

    protected $casts = [
        'active'    => 'boolean',
        'price'     => 'decimal:2',
        'weight_kg' => 'decimal:3',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function heroImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_hero', true);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function isClothing(): bool
    {
        return in_array($this->category, self::CLOTHING_CATEGORIES, true);
    }

    public function getTotalStockAttribute(): int
    {
        return $this->isClothing()
            ? $this->variants()->where('active', true)->sum('quantity')
            : $this->stock_quantity;
    }

    public function getSlugAttribute(): string
    {
        return $this->id . '-' . str($this->name)->slug();
    }

    /**
     * Reconciles the admin form's submitted image paths (and chosen hero)
     * against the product_images table. images/hero_image are virtual form
     * fields, not real columns, so this is called from the Filament page
     * classes after save rather than via mass assignment.
     */
    public function syncImages(array $paths, ?string $heroPath): void
    {
        $paths = array_values(array_filter($paths, fn ($p) => is_string($p)));

        $this->images()->whereNotIn('path', $paths)->delete();

        foreach ($paths as $i => $path) {
            $this->images()->updateOrCreate(['path' => $path], ['sort_order' => $i]);
        }

        if ($heroPath) {
            $this->images()->update(['is_hero' => false]);
            $this->images()->where('path', $heroPath)->update(['is_hero' => true]);
        } elseif ($this->images()->exists() && ! $this->images()->where('is_hero', true)->exists()) {
            $this->images()->orderBy('sort_order')->first()?->update(['is_hero' => true]);
        }
    }
}
