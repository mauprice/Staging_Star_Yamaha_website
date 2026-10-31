<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierInvoiceLine extends Model
{
    protected $fillable = [
        'supplier_invoice_id', 'product_id', 'product_name', 'part_number',
        'quantity', 'unit_cost', 'line_total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    /**
     * Snapshots product_name/part_number and recomputes line_total before
     * every save - same pattern as PurchaseOrderLine.
     */
    protected static function booted(): void
    {
        static::saving(function (SupplierInvoiceLine $line) {
            if ($line->product_id && ! $line->product_name) {
                $product = Product::find($line->product_id);
                $line->product_name = $product?->name ?? $line->product_name;
                $line->part_number = $product?->part_number ?? $line->part_number;
            }

            $line->line_total = round((float) $line->quantity * (float) $line->unit_cost, 2);
        });
    }

    public function supplierInvoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
