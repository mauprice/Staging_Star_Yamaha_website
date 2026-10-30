<?php

namespace Honda\Parts\Models;

use Illuminate\Database\Eloquent\Model;

class ModelPart extends Model
{
    protected $table = 'honda_parts_model_parts';
    public $timestamps = false;
    protected $primaryKey = 'model_part_id';
    public $incrementing = false;

    protected $casts = ['optional' => 'boolean'];
}
