<?php

namespace Honda\Catalog\Models;

use Illuminate\Database\Eloquent\Model;

class HondaColour extends Model
{
    protected $table = 'honda_colours';

    protected $fillable = ['model_id', 'name', 'hex', 'image_asset_id', 'sort'];

    public function model()
    {
        return $this->belongsTo(HondaModel::class, 'model_id');
    }

    public function image()
    {
        return $this->belongsTo(HondaAsset::class, 'image_asset_id');
    }
}
