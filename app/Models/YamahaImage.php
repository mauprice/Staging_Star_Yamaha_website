<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YamahaImage extends Model
{
    protected $table = 'yamaha_images';

    protected $fillable = ['product_id', 'image_url'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(YamahaProduct::class, 'product_id');
    }
}
