<?php

namespace Honda\Parts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Catalogue extends Model
{
    protected $table = 'honda_parts_catalogues';
    public $timestamps = false;
    protected $primaryKey = 'catalogue_id';
    public $incrementing = false;

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'catalogue_id', 'catalogue_id');
    }

    public function assemblies(): HasMany
    {
        return $this->hasMany(Assembly::class, 'catalogue_id', 'catalogue_id');
    }
}
