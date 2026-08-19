<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\User;

class CartMerger
{
    /**
     * Session ID rotates on login (session()->regenerate()), and CartItem
     * used to be keyed purely on session_id - so a guest who adds to cart
     * and then logs in mid-checkout would silently lose their cart. This
     * reassigns those rows to the now-authenticated user, merging quantities
     * where the user already has the same product/variant in their cart.
     */
    public function mergeGuestSessionIntoUser(string $sessionId, User $user): void
    {
        $guestItems = CartItem::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->get();

        foreach ($guestItems as $guestItem) {
            $existing = CartItem::where('user_id', $user->id)
                ->where('product_id', $guestItem->product_id)
                ->where('product_variant_id', $guestItem->product_variant_id)
                ->first();

            if ($existing) {
                $availableStock = $existing->available_stock;
                $existing->update([
                    'quantity' => min($existing->quantity + $guestItem->quantity, $availableStock, 99),
                ]);
                $guestItem->delete();
            } else {
                $guestItem->update(['user_id' => $user->id]);
            }
        }
    }
}
