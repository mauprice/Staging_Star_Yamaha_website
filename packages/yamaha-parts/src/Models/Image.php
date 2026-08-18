<?php

namespace Yamaha\Parts\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $table = 'yamaha_parts_images';

    public $timestamps = false;
    protected $primaryKey = 'image_id';
    public $incrementing = false;

    protected $fillable = [
        'image_id', 'filename', 'format', 'width', 'height', 'extracted',
    ];

    protected $casts = ['extracted' => 'boolean'];

    /**
     * Diagram images are Star and NorthStar Yamaha's shared 71GB YPIC image
     * library — rather than duplicating that storage on this site, images
     * are served directly from NorthStar's already-live copy. Only the
     * (much smaller) relational catalogue data is stored locally.
     */
    public function getUrlAttribute(): string
    {
        if ($this->extracted) {
            return 'https://parts.northstaryamaha.com.au/storage/images/' . $this->image_id . '.' . $this->format;
        }

        return asset('images/placeholder.svg');
    }
}
