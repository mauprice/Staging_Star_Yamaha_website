<?php

namespace Yamaha\Parts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = 'yamaha_parts_products';

    public $timestamps = false;
    protected $primaryKey = 'product_id';
    public $incrementing = false;

    protected $fillable = [
        'product_id', 'catalogue', 'model', 'common_name', 'year', 'type',
        'cat_number', 'prefix', 'serial', 'has_images', 'thumbnail_image_id',
        'admin_prod_id', 'date_modified', 'date_release',
    ];

    protected $casts = [
        'has_images' => 'boolean',
        'date_modified' => 'datetime',
        'date_release' => 'datetime',
    ];

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class, 'product_id', 'product_id');
    }

    public function modelImages(): HasMany
    {
        return $this->hasMany(ModelImage::class, 'product_id', 'product_id');
    }

    public function getTypeNameAttribute(): string
    {
        return match ($this->type) {
            0 => 'Motorcycle',
            1 => 'Outboard Motor',
            2 => 'ATV / Side-by-Side',
            3 => 'Snowmobile',
            4 => 'Personal Watercraft',
            5 => 'Boat',
            default => 'Other',
        };
    }
}
