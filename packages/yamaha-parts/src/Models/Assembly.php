<?php

namespace Yamaha\Parts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assembly extends Model
{
    protected $table = 'yamaha_parts_assemblies';

    public $timestamps = false;
    protected $primaryKey = 'assembly_id';
    public $incrementing = false;

    protected $fillable = [
        'assembly_id', 'content_id', 'title', 'assembly_image_id', 'has_parts',
    ];

    protected $casts = ['has_parts' => 'boolean'];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'content_id', 'content_id');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class, 'assembly_id', 'assembly_id')
            ->whereNull('parent_id')->orWhere('parent_id', 0)
            ->orderBy('image_ref_no');
    }

    public function allParts(): HasMany
    {
        return $this->hasMany(Part::class, 'assembly_id', 'assembly_id')
            ->orderBy('image_ref_no');
    }

    public function assemblyImages(): HasMany
    {
        return $this->hasMany(AssemblyImage::class, 'assembly_id', 'assembly_id');
    }
}
