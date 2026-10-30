<?php

namespace Honda\Parts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Part extends Model
{
    protected $table = 'honda_parts_parts';
    public $timestamps = false;
    protected $primaryKey = 'part_id';
    public $incrementing = false;

    public function assembly(): BelongsTo
    {
        return $this->belongsTo(Assembly::class, 'assembly_id', 'assembly_id');
    }

    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'honda_parts_model_parts', 'part_id', 'model_id')
            ->withPivot('quantity', 'optional');
    }
}
