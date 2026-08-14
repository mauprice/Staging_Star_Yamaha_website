<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YamahaColor extends Model
{
    protected $table = 'yamaha_colors';

    protected $fillable = ['product_id', 'color_name', 'color_code', 'color_image'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(YamahaProduct::class, 'product_id');
    }
}
