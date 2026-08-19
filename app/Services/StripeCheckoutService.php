<?php

namespace App\Services;

use App\Models\Order;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

class StripeCheckoutService
{
    private ?StripeClient $client = null;

    /**
     * Lazy - StripeClient's constructor throws immediately if the API key
     * is blank, and this service is resolved via controller method
     * injection (before the method body runs), so an eager client would
     * throw outside CheckoutController::store()'s try/catch and surface as
     * a raw 500 instead of the graceful "payment unavailable, cart
     * restored" path.
     */
    private function client(): StripeClient
    {
        return $this->client ??= new StripeClient(config('services.stripe.secret'));
    }

    public function createSessionFor(Order $order): Session
    {
        $lineItems = $order->items->map(fn ($item) => [
            'price_data' => [
                'currency' => strtolower($order->currency),
                'product_data' => [
                    'name' => $item->product_name . ($item->variant_label ? " ({$item->variant_label})" : ''),
                ],
                'unit_amount' => (int) round($item->unit_price * 100),
            ],
            'quantity' => $item->quantity,
        ])->all();

        if ((float) $order->shipping_total > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => strtolower($order->currency),
                    'product_data' => ['name' => 'Shipping'],
                    'unit_amount' => (int) round($order->shipping_total * 100),
                ],
                'quantity' => 1,
            ];
        }

        return $this->client()->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => $lineItems,
            'customer_email' => $order->customer_email,
            'metadata' => [
                'order_id' => (string) $order->id,
                'order_number' => $order->order_number,
            ],
            'success_url' => route('yamaha.checkout.success', absolute: true) . '?ref={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('yamaha.checkout.cancel', $order, absolute: true),
        ]);
    }
}
