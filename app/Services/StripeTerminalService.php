<?php

namespace App\Services;

use App\Models\Order;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use Stripe\Terminal\Reader;

class StripeTerminalService
{
    private ?StripeClient $client = null;

    /**
     * Lazy for the same reason as StripeCheckoutService::client() - avoids
     * throwing on a blank API key outside the calling method's try/catch.
     */
    private function client(): StripeClient
    {
        return $this->client ??= new StripeClient(config('services.stripe.secret'));
    }

    public function isConfigured(): bool
    {
        return filled(config('services.stripe.secret'))
            && filled(config('services.stripe.terminal_reader_id'));
    }

    public function createPaymentIntentForOrder(Order $order): PaymentIntent
    {
        return $this->client()->paymentIntents->create([
            'amount' => (int) round($order->total * 100),
            'currency' => strtolower($order->currency),
            'payment_method_types' => ['card_present'],
            'capture_method' => 'automatic',
            'metadata' => [
                'order_id' => (string) $order->id,
                'order_number' => $order->order_number,
                'source' => 'pos',
            ],
        ]);
    }

    /**
     * Hands the PaymentIntent to the paired countertop reader - the cashier
     * taps/inserts the card on the physical device, no client-side Terminal
     * JS SDK needed. Result arrives via the payment_intent.succeeded /
     * payment_intent.payment_failed webhook.
     */
    public function sendToReader(PaymentIntent $intent): Reader
    {
        return $this->client()->terminal->readers->processPaymentIntent(
            config('services.stripe.terminal_reader_id'),
            ['payment_intent' => $intent->id],
        );
    }

    public function reader(): Reader
    {
        return $this->client()->terminal->readers->retrieve(config('services.stripe.terminal_reader_id'));
    }

    public function cancelReaderAction(): Reader
    {
        return $this->client()->terminal->readers->cancelAction(config('services.stripe.terminal_reader_id'));
    }
}
