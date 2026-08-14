<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class YamahaProduct extends Model
{
    protected $table = 'yamaha_products';

    public $incrementing = false;

    protected $keyType = 'integer';

    protected $fillable = [
        'id', 'model_name', 'product_type', 'year_model', 'division',
        'product_group', 'sub_category', 'primary_category', 'item_description',
        'description', 'long_description', 'summary_image', 'recommended_retail',
        'recommended_retail_nz', 'brochure_url', 'product_spec', 'synced_at',
    ];

    protected $casts = [
        'recommended_retail'    => 'decimal:2',
        'recommended_retail_nz' => 'decimal:2',
        'product_spec'          => 'array',
        'synced_at'             => 'datetime',
    ];

    public function banners(): HasMany
    {
        return $this->hasMany(YamahaBanner::class, 'product_id');
    }

    public function heroBanners(): HasMany
    {
        return $this->hasMany(YamahaBanner::class, 'product_id')->where('image_type', 1);
    }

    public function colors(): HasMany
    {
        return $this->hasMany(YamahaColor::class, 'product_id');
    }

    public function features(): HasMany
    {
        return $this->hasMany(YamahaFeature::class, 'product_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(YamahaImage::class, 'product_id');
    }

    public function getFormattedPriceAttribute(): ?string
    {
        return $this->recommended_retail > 0
            ? '$' . number_format($this->recommended_retail, 0)
            : null;
    }

    public function getSlugAttribute(): string
    {
        return strtolower(str_replace(' ', '-', $this->model_name));
    }
}
