<?php

namespace Honda\Catalog\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class HondaModel extends Model
{
    protected $table = 'honda_models';

    protected $fillable = [
        'slug', 'category', 'subcategory', 'name', 'tagline', 'description',
        'price_from', 'price_currency', 'price_label', 'source_url',
        'og_image_asset_id', 'last_scraped_at', 'content_hash',
    ];

    protected function casts(): array
    {
        return [
            'price_from' => 'integer',
            'last_scraped_at' => 'datetime',
        ];
    }

    /**
     * price_from is stored in cents (see migration) to avoid float rounding
     * bugs - this exposes it as a display-ready dollar string, e.g. "$5,499.00".
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price_from !== null
                ? '$'.number_format($this->price_from / 100, 2)
                : null,
        );
    }

    public function ogImage()
    {
        return $this->belongsTo(HondaAsset::class, 'og_image_asset_id');
    }

    public function features()
    {
        return $this->hasMany(HondaModelFeature::class, 'model_id')->orderBy('sort');
    }

    public function variants()
    {
        return $this->hasMany(HondaVariant::class, 'model_id')->orderBy('sort');
    }

    public function specifications()
    {
        return $this->hasMany(HondaSpecification::class, 'model_id')->orderBy('sort');
    }

    public function colours()
    {
        return $this->hasMany(HondaColour::class, 'model_id')->orderBy('sort');
    }

    public function assets()
    {
        return $this->belongsToMany(HondaAsset::class, 'honda_model_asset', 'model_id', 'asset_id')
            ->withPivot(['role', 'sort'])
            ->withTimestamps();
    }
}
