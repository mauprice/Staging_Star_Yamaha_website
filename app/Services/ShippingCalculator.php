<?php

namespace App\Services;

use App\Models\Setting;

/**
 * Placeholder flat-rate calculator for Phase 3. Phase 5 replaces the
 * internals of forSubtotal() with a real rate-table / carrier-API engine
 * keyed on order_addresses.postcode/state/country and product weight -
 * callers of this class don't change.
 */
class ShippingCalculator
{
    public function forSubtotal(float $subtotal): float
    {
        $freeThreshold = (float) Setting::get('shipping_free_threshold', '150');

        if ($subtotal >= $freeThreshold) {
            return 0.0;
        }

        return (float) Setting::get('shipping_flat_rate', '10');
    }
}
