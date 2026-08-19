<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\PartsPricing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yamaha\Parts\Models\Part;

class CartController extends Controller
{
    private const MOTORCYCLE_TYPE = 0;

    public function index(): View
    {
        $items = CartItem::forCurrentCart()
            ->with(['product.heroImage', 'variant'])
            ->latest()
            ->get();

        $subtotal = $items->sum('line_total');

        return view('yamaha.cart', compact('items', 'subtotal'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'product_variant_id' => 'nullable|integer|exists:product_variants,id',
            'quantity' => 'nullable|integer|min:1|max:99',
        ]);

        $product = Product::where('active', true)->findOrFail($data['product_id']);
        $variant = null;

        if ($product->isClothing()) {
            $variant = ProductVariant::where('product_id', $product->id)
                ->where('active', true)
                ->findOrFail($data['product_variant_id'] ?? null);
        }

        $quantity = $data['quantity'] ?? 1;
        $availableStock = $variant?->quantity ?? $product->stock_quantity;

        if ($availableStock < 1) {
            return response()->json(['message' => 'This item is out of stock.'], 422);
        }

        $cartItem = CartItem::forCurrentCart()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        $newQuantity = min(($cartItem->quantity ?? 0) + $quantity, $availableStock, 99);

        if ($cartItem) {
            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            CartItem::create([
                'session_id' => session()->getId(),
                'user_id' => auth()->id(),
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'quantity' => $newQuantity,
            ]);
        }

        return response()->json([
            'message' => 'Added to cart.',
            'count' => self::currentCount(),
        ]);
    }

    /**
     * Adds an OEM part from the parts-finder catalogue - a completely
     * separate identity from the Shop Accessories `products` table (see
     * CartItem::isPart()). Price is always re-derived server-side from the
     * part number, never trusted from the client.
     */
    public function storePart(Request $request, PartsPricing $pricing): JsonResponse
    {
        $data = $request->validate([
            'part_number' => 'required|string|max:255',
            'quantity' => 'nullable|integer|min:1|max:99',
        ]);

        $part = Part::where('number', $data['part_number'])
            ->whereHas('assembly.content.product', fn ($q) => $q->where('type', self::MOTORCYCLE_TYPE))
            ->first();

        abort_unless($part, 404);

        $price = $pricing->forNumber($data['part_number']);

        if (! $price) {
            return response()->json([
                'message' => "This part doesn't have a price and can't be ordered online — please contact us.",
            ], 422);
        }

        $quantity = $data['quantity'] ?? 1;

        $cartItem = CartItem::forCurrentCart()
            ->whereNull('product_id')
            ->where('part_number', $data['part_number'])
            ->first();

        $newQuantity = min(($cartItem->quantity ?? 0) + $quantity, 99);

        if ($cartItem) {
            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            CartItem::create([
                'session_id' => session()->getId(),
                'user_id' => auth()->id(),
                'part_number' => $data['part_number'],
                'part_description' => $part->desc,
                'unit_price_snapshot' => $price['cents'] / 100,
                'currency' => $price['currency'],
                'quantity' => $newQuantity,
            ]);
        }

        return response()->json([
            'message' => 'Added to cart.',
            'count' => self::currentCount(),
        ]);
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorizeOwnership($cartItem);

        $data = $request->validate(['quantity' => 'required|integer|min:1|max:99']);

        $cartItem->update([
            'quantity' => min($data['quantity'], $cartItem->available_stock, 99),
        ]);

        return redirect()->route('yamaha.cart.index')->with('success', 'Cart updated.');
    }

    public function destroy(CartItem $cartItem): RedirectResponse
    {
        $this->authorizeOwnership($cartItem);

        $cartItem->delete();

        return redirect()->route('yamaha.cart.index')->with('success', 'Item removed from cart.');
    }

    private function authorizeOwnership(CartItem $cartItem): void
    {
        $owned = auth()->check()
            ? $cartItem->user_id === auth()->id()
            : $cartItem->user_id === null && $cartItem->session_id === session()->getId();

        abort_unless($owned, 404);
    }

    public static function currentCount(): int
    {
        return (int) CartItem::forCurrentCart()->sum('quantity');
    }
}
