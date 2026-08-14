<?php

namespace Honda\Catalog\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class HondaVariant extends Model
{
    protected $table = 'honda_variants';

    protected $fillable = ['model_id', 'name', 'price', 'sort'];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
        ];
    }

    /**
     * price is stored in cents (see migration) - display-ready dollar
     * string, e.g. "$13,699.00".
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price !== null
                ? '$'.number_format($this->price / 100, 2)
                : null,
        );
    }

    public function model()
    {
        return $this->belongsTo(HondaModel::class, 'model_id');
    }

    public function specifications()
    {
        return $this->hasMany(HondaSpecification::class, 'variant_id');
    }
}
