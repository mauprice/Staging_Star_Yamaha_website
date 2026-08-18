<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'path', 'is_hero', 'sort_order'];

    protected $casts = ['is_hero' => 'boolean'];

    protected static function booted(): void
    {
        // Enforce "only one hero per product" at the model layer, regardless
        // of whether it's set via the admin form, tinker, or a future importer.
        static::saving(function (self $image) {
            if ($image->is_hero) {
                static::where('product_id', $image->product_id)
                    ->when($image->exists, fn ($q) => $q->where('id', '!=', $image->id))
                    ->update(['is_hero' => false]);
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
