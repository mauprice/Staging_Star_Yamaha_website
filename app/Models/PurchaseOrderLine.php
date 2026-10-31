<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderLine extends Model
{
    protected $fillable = [
        'purchase_order_id', 'product_id', 'product_name', 'part_number',
        'quantity_ordered', 'quantity_received', 'unit_cost', 'line_total',
    ];

    protected $casts = [
        'quantity_ordered' => 'integer',
        'quantity_received' => 'integer',
        'unit_cost' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    /**
     * Snapshots product_name/part_number from the selected product and
     * recomputes line_total before every save, mirroring StockEntry's
     * booted() pattern - keeps the form simple (just pick a product, qty,
     * cost) without relying on JS to keep derived fields in sync.
     */
    protected static function booted(): void
    {
        static::saving(function (PurchaseOrderLine $line) {
            if ($line->product_id && ! $line->product_name) {
                $product = Product::find($line->product_id);
                $line->product_name = $product?->name ?? $line->product_name;
                $line->part_number = $product?->part_number ?? $line->part_number;
            }

            $line->line_total = round((float) $line->quantity_ordered * (float) $line->unit_cost, 2);
        });
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function quantityOutstanding(): int
    {
        return max(0, $this->quantity_ordered - $this->quantity_received);
    }
}
