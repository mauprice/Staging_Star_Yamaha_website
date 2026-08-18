<?php

namespace Yamaha\Parts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Part extends Model
{
    protected $table = 'yamaha_parts_parts';

    public $timestamps = false;
    protected $primaryKey = 'part_id';
    public $incrementing = false;

    protected $fillable = [
        'part_id', 'assembly_id', 'number', 'search_part_no', 'mangled_part_no',
        'desc', 'remark', 'qty', 'image_ref_no', 'img_x', 'img_y',
        'parent_id', 'has_child',
    ];

    protected $casts = ['has_child' => 'boolean'];

    public function assembly(): BelongsTo
    {
        return $this->belongsTo(Assembly::class, 'assembly_id', 'assembly_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'parent_id', 'part_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Part::class, 'parent_id', 'part_id')
            ->orderBy('image_ref_no');
    }
}
