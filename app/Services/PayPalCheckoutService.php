<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * PayPal Orders v2 REST integration via plain signed HTTP calls - PayPal's
 * official PHP SDK is heavyweight and largely unmaintained, so this mirrors
 * how StripeCheckoutService wraps the Stripe SDK but talks to the REST API
 * directly instead.
 */
class PayPalCheckoutService
{
    private function baseUrl(): string
    {
        return config('services.paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * Client-credentials tokens last ~8 hours; cached so checkout doesn't
     * do an extra round trip to PayPal for every single order.
     */
    private function accessToken(): string
    {
        return Cache::remember('paypal_access_token_' . config('services.paypal.mode'), 28000, function () {
            $response = Http::asForm()
                ->withBasicAuth(config('services.paypal.client_id'), config('services.paypal.client_secret'))
                ->baseUrl($this->baseUrl())
                ->throw()
                ->post('/v1/oauth2/token', ['grant_type' => 'client_credentials']);

            return (string) $response->json('access_token');
        });
    }

    private function client(): PendingRequest
    {
        return Http::withToken($this->accessToken())->baseUrl($this->baseUrl())->throw();
    }

    /**
     * @return array{id: string, approve_url: string}
     */
    public function createOrderFor(Order $order): array
    {
        $itemTotal = (float) $order->items->sum(fn ($item) => $item->unit_price * $item->quantity);
        $shipping = (float) $order->shipping_total;

        $items = $order->items->map(fn ($item) => [
            'name' => Str::limit($item->product_name . ($item->variant_label ? " ({$item->variant_label})" : ''), 127, ''),
            'quantity' => (string) $item->quantity,
            'unit_amount' => [
                'currency_code' => $order->currency,
                'value' => $this->money($item->unit_price),
            ],
        ])->all();

        $response = $this->client()->post('/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => (string) $order->id,
                'custom_id' => (string) $order->id,
                'amount' => [
                    'currency_code' => $order->currency,
                    'value' => $this->money($itemTotal + $shipping),
                    'breakdown' => [
                        'item_total' => ['currency_code' => $order->currency, 'value' => $this->money($itemTotal)],
                        'shipping' => ['currency_code' => $order->currency, 'value' => $this->money($shipping)],
                    ],
                ],
                'items' => $items,
            ]],
            'application_context' => [
                'brand_name' => config('app.name'),
                'user_action' => 'PAY_NOW',
                'return_url' => route('yamaha.checkout.paypal.return', $order, absolute: true),
                'cancel_url' => route('yamaha.checkout.cancel', $order, absolute: true),
            ],
        ]);

        $id = $response->json('id');
        $approveUrl = collect($response->json('links'))->firstWhere('rel', 'approve')['href'] ?? null;

        if (! $id || ! $approveUrl) {
            throw new \RuntimeException('PayPal did not return an order id and approval link');
        }

        return ['id' => $id, 'approve_url' => $approveUrl];
    }

    /**
     * @return array{status: ?string, raw: array}
     */
    public function captureOrder(string $paypalOrderId): array
    {
        $response = $this->client()->post("/v2/checkout/orders/{$paypalOrderId}/capture");

        return ['status' => $response->json('status'), 'raw' => (array) $response->json()];
    }

    /**
     * Confirms a webhook payload genuinely came from PayPal, via PayPal's
     * own verification endpoint (no local certificate handling needed).
     * Requires the webhook's ID - configured in Payment Settings once the
     * webhook is registered in the PayPal Dashboard.
     *
     * @param  array<string, array<int, string>>  $headers
     */
    public function verifyWebhookSignature(array $headers, array $event): bool
    {
        $webhookId = config('services.paypal.webhook_id');

        if (blank($webhookId)) {
            return false;
        }

        $response = $this->client()->post('/v1/notifications/verify-webhook-signature', [
            'auth_algo' => $headers['paypal-auth-algo'][0] ?? null,
            'cert_url' => $headers['paypal-cert-url'][0] ?? null,
            'transmission_id' => $headers['paypal-transmission-id'][0] ?? null,
            'transmission_sig' => $headers['paypal-transmission-sig'][0] ?? null,
            'transmission_time' => $headers['paypal-transmission-time'][0] ?? null,
            'webhook_id' => $webhookId,
            'webhook_event' => $event,
        ]);

        return $response->json('verification_status') === 'SUCCESS';
    }

    private function money(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}
