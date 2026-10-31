<?php

namespace App\Models;

use App\Enums\PurchaseOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number', 'supplier_id', 'user_id', 'status', 'notes', 'ordered_at', 'received_at',
    ];

    protected $casts = [
        'status' => PurchaseOrderStatus::class,
        'ordered_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (PurchaseOrder $po) {
            if (! $po->po_number) {
                $po->forceFill(['po_number' => 'PO-'.str_pad((string) $po->id, 6, '0', STR_PAD_LEFT)])->save();
            }
        });
    }

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
        return $this->hasMany(PurchaseOrderLine::class);
    }

    public function getTotalCostAttribute(): float
    {
        return round((float) $this->lines()->sum('line_total'), 2);
    }

    /**
     * Recomputes status from each line's received/ordered quantities.
     * Called after a receiving action updates line quantities - status is
     * derived, never set directly to Received/PartiallyReceived by hand.
     */
    public function refreshReceivingStatus(): void
    {
        $lines = $this->lines()->get(['quantity_ordered', 'quantity_received']);

        $allReceived = $lines->every(fn (PurchaseOrderLine $l) => $l->quantity_received >= $l->quantity_ordered);
        $anyReceived = $lines->contains(fn (PurchaseOrderLine $l) => $l->quantity_received > 0);

        $this->update([
            'status' => $allReceived ? PurchaseOrderStatus::Received : ($anyReceived ? PurchaseOrderStatus::PartiallyReceived : $this->status),
            'received_at' => $allReceived ? ($this->received_at ?? now()) : $this->received_at,
        ]);
    }
}
