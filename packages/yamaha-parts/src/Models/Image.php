<?php

namespace Yamaha\Parts\Models;

use App\Models\Setting;
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
            $baseUrl = Setting::get('yamaha_parts_image_base_url', config('yamaha_parts.image_base_url'));

            return $baseUrl . $this->image_id . '.' . $this->format;
        }

        return asset('images/placeholder.svg');
    }
}
