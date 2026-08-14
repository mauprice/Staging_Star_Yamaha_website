<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YamahaBanner extends Model
{
    protected $table = 'yamaha_banners';

    public $incrementing = false;

    protected $keyType = 'integer';

    protected $fillable = ['id', 'product_id', 'image', 'image_mobile', 'image_tablet', 'image_options', 'image_type', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(YamahaProduct::class, 'product_id');
    }
}
