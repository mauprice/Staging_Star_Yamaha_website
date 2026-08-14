<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YamahaFeature extends Model
{
    protected $table = 'yamaha_features';

    protected $fillable = ['product_id', 'title', 'type', 'description', 'image'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(YamahaProduct::class, 'product_id');
    }
}
