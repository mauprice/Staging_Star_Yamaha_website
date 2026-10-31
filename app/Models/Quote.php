<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    protected $fillable = [
        'quote_number', 'customer_id', 'user_id', 'customer_name', 'customer_phone',
        'order_id', 'converted_at', 'notes',
    ];

    protected $casts = [
        'converted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (Quote $quote) {
            if (! $quote->quote_number) {
                $quote->forceFill(['quote_number' => 'QT-'.str_pad((string) $quote->id, 6, '0', STR_PAD_LEFT)])->save();
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(QuoteLine::class);
    }

    public function isConverted(): bool
    {
        return $this->converted_at !== null;
    }

    public function getTotalAttribute(): float
    {
        return round((float) $this->lines()->sum('line_total'), 2);
    }
}
