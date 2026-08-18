<?php

namespace Yamaha\Parts\Models;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $table = 'yamaha_parts_prices';

    public $timestamps = false;
    protected $primaryKey = 'part_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'part_number', 'description', 'rrp_cents', 'cost_cents',
        'currency', 'source_file', 'imported_at',
    ];

    protected $appends = ['rrp', 'cost'];

    public function getRrpAttribute(): float
    {
        return round($this->rrp_cents / 100, 2);
    }

    public function getCostAttribute(): float
    {
        return round($this->cost_cents / 100, 2);
    }
}
