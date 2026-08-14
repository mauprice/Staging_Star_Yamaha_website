<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class YamahaNews extends Model
{
    protected $table = 'yamaha_news';

    protected $fillable = [
        'id', 'head', 'brief', 'brief_image', 'full_content_url',
        'content', 'image', 'image_options', 'type', 'other_types',
        'country', 'active', 'sort_index',
    ];

    protected $casts = [
        'other_types' => 'array',
        'active'      => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function getSlugAttribute(): string
    {
        return Str::slug($this->head ?? 'news-' . $this->id);
    }

    public function getCleanBriefAttribute(): string
    {
        return strip_tags($this->brief ?? '');
    }
}
