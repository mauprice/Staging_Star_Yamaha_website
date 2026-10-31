<?php

namespace App\Models;

use App\Enums\SupplierInvoiceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierInvoice extends Model
{
    protected $fillable = ['supplier_id', 'user_id', 'invoice_number', 'type', 'invoice_date', 'notes'];

    protected $casts = [
        'type' => SupplierInvoiceType::class,
        'invoice_date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SupplierInvoiceLine::class);
    }

    public function getTotalCostAttribute(): float
    {
        return round((float) $this->lines()->sum('line_total'), 2);
    }

    /**
     * Applies this document's stock effect: an invoice means goods arrived
     * (stock up, logged as a StockReceipt); a credit means goods went back
     * to the supplier (stock down, no receipt - the line itself is the
     * audit trail). Called once, right after lines are saved (see
     * CreateSupplierInvoice::afterCreate) - entering an invoice IS receiving
     * it, there's no separate draft/receive step like a PurchaseOrder.
     */
    public function applyStock(): void
    {
        foreach ($this->lines as $line) {
            if (! $line->product_id) {
                continue;
            }

            if ($this->type === SupplierInvoiceType::Invoice) {
                Product::where('id', $line->product_id)->increment('stock_quantity', $line->quantity);

                StockReceipt::create([
                    'supplier_invoice_line_id' => $line->id,
                    'product_id' => $line->product_id,
                    'received_by' => $this->user_id,
                    'quantity' => $line->quantity,
                    'received_at' => $this->invoice_date,
                ]);
            } else {
                $product = Product::find($line->product_id);

                if ($product) {
                    $product->update(['stock_quantity' => max(0, $product->stock_quantity - $line->quantity)]);
                }
            }
        }
    }

    /**
     * Undoes applyStock() - called before delete so removing a mis-entered
     * invoice/credit doesn't leave stock permanently skewed.
     */
    public function reverseStock(): void
    {
        foreach ($this->lines as $line) {
            if (! $line->product_id) {
                continue;
            }

            if ($this->type === SupplierInvoiceType::Invoice) {
                $product = Product::find($line->product_id);

                if ($product) {
                    $product->update(['stock_quantity' => max(0, $product->stock_quantity - $line->quantity)]);
                }
            } else {
                Product::where('id', $line->product_id)->increment('stock_quantity', $line->quantity);
            }
        }

        StockReceipt::whereIn('supplier_invoice_line_id', $this->lines->pluck('id'))->delete();
    }
}
