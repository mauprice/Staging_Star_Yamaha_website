<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReceipt extends Model
{
    protected $fillable = [
        'purchase_order_line_id', 'supplier_invoice_line_id', 'product_id', 'received_by', 'quantity', 'received_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'received_at' => 'datetime',
    ];

    public function purchaseOrderLine(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderLine::class);
    }

    public function supplierInvoiceLine(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoiceLine::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
