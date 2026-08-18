<?php

namespace Yamaha\Parts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Content extends Model
{
    protected $table = 'yamaha_parts_contents';

    public $timestamps = false;
    protected $primaryKey = 'content_id';
    public $incrementing = false;

    protected $fillable = ['content_id', 'product_id', 'title'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function assemblies(): HasMany
    {
        return $this->hasMany(Assembly::class, 'content_id', 'content_id');
    }
}
