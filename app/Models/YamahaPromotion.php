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
        'is_visible', 'display_order',
    ];

    protected $casts = [
        'active'     => 'boolean',
        'is_visible' => 'boolean',
    ];

    /**
     * `is_visible`/`display_order` are the admin-controlled counterparts of
     * `active`/`sort_index` (which the Yamaha sync overwrites every run).
     * Seed them from the synced values on first insert only, so a brand new
     * promotion starts out visible in sync order; once a row exists, the
     * sync upsert never includes these keys so admin edits stick.
     */
    protected static function booted(): void
    {
        static::creating(function (self $promotion) {
            $promotion->is_visible ??= $promotion->active;
            $promotion->display_order ??= $promotion->sort_index;
        });
    }
}
