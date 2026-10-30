<?php

namespace Honda\Parts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A vehicle entry in Honda's electronic parts catalogue (table
 * honda_parts_models). Named "Vehicle", not "HondaModel" — the dedicated
 * Honda\Parts\Models namespace already disambiguates it from the unrelated
 * Honda\Catalog\Models\HondaModel (marketing/sales data), the same way
 * Yamaha's equivalent is just "Product", not "YamahaProduct".
 */
class Vehicle extends Model
{
    protected $table = 'honda_parts_models';
    public $timestamps = false;
    protected $primaryKey = 'model_id';
    public $incrementing = false;

    public function catalogue(): BelongsTo
    {
        return $this->belongsTo(Catalogue::class, 'catalogue_id', 'catalogue_id');
    }

    public function assemblies(): BelongsToMany
    {
        return $this->belongsToMany(Assembly::class, 'honda_parts_model_assemblies', 'model_id', 'assembly_id');
    }

    public function parts(): BelongsToMany
    {
        return $this->belongsToMany(Part::class, 'honda_parts_model_parts', 'model_id', 'part_id')
            ->withPivot('quantity', 'optional');
    }
}
