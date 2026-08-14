<?php

namespace Honda\Catalog\Models;

use Illuminate\Database\Eloquent\Model;

class HondaModelFeature extends Model
{
    protected $table = 'honda_model_features';

    protected $fillable = ['model_id', 'sort', 'heading', 'body', 'image_asset_id'];

    public function model()
    {
        return $this->belongsTo(HondaModel::class, 'model_id');
    }

    public function image()
    {
        return $this->belongsTo(HondaAsset::class, 'image_asset_id');
    }
}
