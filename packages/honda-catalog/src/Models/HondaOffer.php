<?php

namespace Honda\Catalog\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class HondaOffer extends Model
{
    protected $table = 'honda_offers';

    protected $fillable = [
        'parent_id', 'slug', 'title', 'subtitle', 'price_label', 'body',
        'image_asset_id', 'cta_url', 'cta_label', 'honda_model_id',
        'source_url', 'sort', 'content_hash', 'last_scraped_at', 'is_active',
        'show_in_homepage_slider',
    ];

    protected function casts(): array
    {
        return [
            'last_scraped_at' => 'datetime',
            'is_active' => 'boolean',
            'show_in_homepage_slider' => 'boolean',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(HondaOffer::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(HondaOffer::class, 'parent_id')->orderBy('sort');
    }

    public function image()
    {
        return $this->belongsTo(HondaAsset::class, 'image_asset_id');
    }

    public function hondaModel()
    {
        return $this->belongsTo(HondaModel::class, 'honda_model_id');
    }

    /**
     * Resolves where this offer should link to, preferring an internal page
     * over sending visitors off to honda.com.au: a matched product page if
     * the offer targets one specific model, else our own listing page if it
     * has child items (e.g. a runout campaign), else the raw Honda-site CTA.
     */
    protected function linkUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->honda_model_id && $this->hondaModel) {
                    return route('honda.product', [
                        $this->hondaModel->category,
                        $this->hondaModel->subcategory,
                        $this->hondaModel->slug,
                    ]);
                }

                if ($this->parent_id === null && $this->children->isNotEmpty()) {
                    return route('honda.offers.show', $this->slug);
                }

                if (! $this->cta_url) {
                    return route('honda.offers');
                }

                if (str_starts_with($this->cta_url, 'http')) {
                    return $this->cta_url;
                }

                return rtrim(config('honda-catalog.base_url'), '/').'/'.ltrim($this->cta_url, '/');
            },
        );
    }
}
