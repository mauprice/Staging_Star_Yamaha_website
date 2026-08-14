<?php

namespace App\Models;

use Database\Factories\StockEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockEntry extends Model
{
    /** @use HasFactory<StockEntryFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'sold_date' => 'date',
            'comm_paid_date' => 'date',
            'date_banked' => 'date',
            'purchase_price' => 'decimal:2',
            'price_before_writeback' => 'decimal:2',
            'writeback' => 'decimal:2',
            'revs_transfer_fee' => 'decimal:2',
            'rego_fee' => 'decimal:2',
            'stamp_duty' => 'decimal:2',
            'workshop_parts' => 'decimal:2',
            'workshop_labour' => 'decimal:2',
            'transport' => 'decimal:2',
            'misc' => 'decimal:2',
            'accessories_purchased' => 'decimal:2',
            'insurance' => 'decimal:2',
            'ymaf_rebate' => 'decimal:2',
            'gst_paid' => 'decimal:2',
            'gst_collected' => 'decimal:2',
            'gst_payable' => 'decimal:2',
            'trade_in_price' => 'decimal:2',
            'bal_paid' => 'decimal:2',
            'sold_price' => 'decimal:2',
            'profit' => 'decimal:2',
            'bal_banked' => 'decimal:2',
            'commission' => 'decimal:2',
        ];
    }

    /**
     * Recalculates GST, sold price, and profit before every save, mirroring the
     * legacy app's client-side cost()/sold() formulas so the figures can never
     * drift out of sync with their inputs.
     */
    protected static function booted(): void
    {
        static::saving(function (StockEntry $entry) {
            $entry->gst_paid = round((float) $entry->purchase_price / 11, 2);
            $entry->gst_payable = round((float) $entry->gst_collected - (float) $entry->gst_paid, 2);
            $entry->sold_price = round((float) $entry->bal_paid + (float) $entry->trade_in_price, 2);
            $entry->profit = round($entry->sold_price - $entry->cost(), 2);
        });
    }

    /**
     * Total acquisition cost: purchase price plus every fee/expense incurred
     * getting the unit ready for sale (matches legacy cost() in script.js).
     */
    public function cost(): float
    {
        return (float) $this->purchase_price
            + (float) $this->rego_fee
            + (float) $this->revs_transfer_fee
            + (float) $this->stamp_duty
            + (float) $this->workshop_parts
            + (float) $this->workshop_labour
            + (float) $this->transport
            + (float) $this->misc
            + (float) $this->writeback
            + (float) $this->gst_payable
            + (float) $this->accessories_purchased;
    }

    public function isSold(): bool
    {
        return $this->sold_date !== null;
    }

    public function getStatusAttribute(): string
    {
        return $this->isSold() ? 'sold' : 'in_stock';
    }

    #[Scope]
    protected function sold(Builder $query): void
    {
        $query->whereNotNull('sold_date');
    }

    #[Scope]
    protected function inStock(Builder $query): void
    {
        $query->whereNull('sold_date');
    }
}
