<?php

namespace Honda\Parts\Models;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $table = 'honda_parts_prices';
    public $timestamps = false;
    protected $primaryKey = 'part_number';
    public $incrementing = false;
    protected $keyType = 'string';
}
