<?php

namespace Honda\Catalog\Models;

use Illuminate\Database\Eloquent\Model;

class HondaSpecification extends Model
{
    protected $table = 'honda_specifications';

    protected $fillable = ['model_id', 'variant_id', 'section', 'category', 'label', 'value', 'sort'];

    public function model()
    {
        return $this->belongsTo(HondaModel::class, 'model_id');
    }

    public function variant()
    {
        return $this->belongsTo(HondaVariant::class, 'variant_id');
    }
}
