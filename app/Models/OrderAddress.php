<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAddress extends Model
{
    protected $fillable = [
        'order_id', 'type', 'full_name', 'phone',
        'line1', 'line2', 'suburb', 'state', 'postcode', 'country',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getSingleLineAttribute(): string
    {
        return trim(implode(', ', array_filter([
            $this->line1, $this->line2, $this->suburb, "{$this->state} {$this->postcode}", $this->country,
        ])));
    }
}
