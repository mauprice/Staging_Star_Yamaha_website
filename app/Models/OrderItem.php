<?php

namespace App\Models;

use App\Enums\OrderItemFulfillmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_variant_id',
        'product_name', 'variant_label', 'part_number',
        'unit_price', 'quantity', 'line_total',
        'fulfillment_status', 'expected_date', 'fulfillment_notes', 'fulfilled_at',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
        'line_total' => 'decimal:2',
        'fulfillment_status' => OrderItemFulfillmentStatus::class,
        'expected_date' => 'date',
        'fulfilled_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
