<?php

namespace Honda\Parts\Models;

use Illuminate\Database\Eloquent\Model;

class ModelAssembly extends Model
{
    protected $table = 'honda_parts_model_assemblies';
    public $timestamps = false;
    protected $primaryKey = 'model_assembly_id';
    public $incrementing = false;
}
