<?php

namespace Honda\Parts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assembly extends Model
{
    protected $table = 'honda_parts_assemblies';
    public $timestamps = false;
    protected $primaryKey = 'assembly_id';
    public $incrementing = false;

    public function catalogue(): BelongsTo
    {
        return $this->belongsTo(Catalogue::class, 'catalogue_id', 'catalogue_id');
    }

    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'honda_parts_model_assemblies', 'assembly_id', 'model_id');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class, 'assembly_id', 'assembly_id')->orderBy('ref_no');
    }

    public function assemblyImages(): HasMany
    {
        return $this->hasMany(AssemblyImage::class, 'assembly_id', 'assembly_id');
    }
}
