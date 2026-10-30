<?php

namespace Honda\Parts\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $table = 'honda_parts_images';
    public $timestamps = false;

    protected $casts = ['extracted' => 'boolean'];

    /**
     * Served via a filesystem symlink (star_yamaha/public/storage/honda-images
     * -> honda_parts/storage/app/public/images) rather than a local copy or a
     * remote CDN — unlike Yamaha's images (served from a remote NorthStar
     * URL), this disc's diagrams only exist on the standalone honda_parts
     * app's local disk, and the relational data alone is what moved into
     * StarYamaha_db.
     */
    public function getUrlAttribute(): string
    {
        if ($this->extracted) {
            return asset('storage/honda-images/' . $this->catalogue_id . '_' . $this->image_id . '.' . $this->format);
        }
        return asset('images/placeholder.svg');
    }
}
