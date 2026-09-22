<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'cashier_id', 'placed_as_guest', 'source',
        'customer_name', 'customer_email', 'customer_phone',
        'status', 'payment_method', 'fulfillment_method', 'currency',
        'subtotal', 'shipping_total', 'total', 'tax_total',
        'notes', 'customer_notes', 'ip_address', 'user_agent',
        'placed_at', 'paid_at', 'shipped_at', 'completed_at', 'cancelled_at',
    ];

    protected $casts = [
        'placed_as_guest' => 'boolean',
        'status' => OrderStatus::class,
        'payment_method' => PaymentMethod::class,
        'subtotal' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'placed_at' => 'datetime',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function isPickup(): bool
    {
        return $this->fulfillment_method === 'pickup';
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            // AU pricing is GST-inclusive throughout the site, so the GST
            // component is always 1/11th of the total - computed here so
            // every order (online or POS) gets an itemizable tax figure
            // without each creation path having to remember to set it.
            $order->tax_total ??= round($order->total / 11, 2);
        });

        static::created(function (Order $order) {
            if (! $order->order_number) {
                $order->forceFill(['order_number' => 'SY-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT)])->save();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class)->where('type', 'shipping');
    }

    public function billingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class)->where('type', 'billing');
    }

    public function effectiveBillingAddress(): ?OrderAddress
    {
        return $this->billingAddress ?? $this->shippingAddress;
    }
}
