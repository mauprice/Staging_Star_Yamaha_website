<?php

namespace Yamaha\Parts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssemblyImage extends Model
{
    protected $table = 'yamaha_parts_assembly_images';

    public $timestamps = false;
    protected $primaryKey = 'assembly_image_id';
    public $incrementing = false;

    protected $fillable = ['assembly_image_id', 'assembly_id', 'image_id'];

    public function assembly(): BelongsTo
    {
        return $this->belongsTo(Assembly::class, 'assembly_id', 'assembly_id');
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'image_id', 'image_id');
    }
}
