<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YamahaPromotion extends Model
{
    protected $table = 'yamaha_promotions';

    public $incrementing = false;

    protected $keyType = 'integer';

    protected $fillable = [
        'id', 'head', 'brief', 'brief_image', 'full_content_url',
        'content', 'image', 'type', 'country', 'active', 'sort_index',
    ];

    protected $casts = ['active' => 'boolean'];
}
