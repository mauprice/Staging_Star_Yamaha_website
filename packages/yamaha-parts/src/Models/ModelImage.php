<?php

namespace Yamaha\Parts\Models;

use Illuminate\Database\Eloquent\Model;

class ModelImage extends Model
{
    protected $table = 'yamaha_parts_model_images';

    public $timestamps = false;
    protected $primaryKey = 'model_image_id';
    public $incrementing = false;

    protected $fillable = ['model_image_id', 'product_id', 'image_id'];
}
