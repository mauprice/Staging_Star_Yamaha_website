<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;

class StockService
{
    /**
     * Decrements stock for every line item on a paid order. Must be called
     * inside a DB transaction with the order row already locked
     * (lockForUpdate) by the caller, so concurrent payment confirmations for
     * the same order can't double-decrement.
     *
     * @return array<int, string> human-readable conflict messages for items
     *                             that oversold (stock still clamped to 0,
     *                             not blocked - the caller decides how to
     *                             surface these, e.g. appended to notes)
     */
    public function decrementForOrder(Order $order): array
    {
        $conflicts = [];

        foreach ($order->items()->get() as $item) {
            if (! $item->product_id) {
                continue;
            }

            if ($item->product_variant_id) {
                $variant = ProductVariant::where('id', $item->product_variant_id)->lockForUpdate()->first();
                if ($variant) {
                    if ($variant->quantity < $item->quantity) {
                        $conflicts[] = "{$item->product_name}: needed {$item->quantity}, only {$variant->quantity} in stock";
                    }
                    $variant->update(['quantity' => max(0, $variant->quantity - $item->quantity)]);
                }
            } else {
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                if ($product) {
                    if ($product->stock_quantity < $item->quantity) {
                        $conflicts[] = "{$item->product_name}: needed {$item->quantity}, only {$product->stock_quantity} in stock";
                    }
                    $product->update(['stock_quantity' => max(0, $product->stock_quantity - $item->quantity)]);
                }
            }
        }

        return $conflicts;
    }
}
