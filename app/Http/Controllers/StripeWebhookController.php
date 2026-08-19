<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Mail\OrderAccountMatchMail;
use App\Mail\OrderReceiptMail;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Event;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', ''),
                config('services.stripe.webhook_secret'),
            );
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);

            return response('Invalid signature', 400);
        }

        // Idempotency: Stripe may redeliver the same event more than once.
        if (PaymentWebhookEvent::where('event_id', $event->id)->exists()) {
            return response('Already processed', 200);
        }

        $webhookEvent = PaymentWebhookEvent::create([
            'provider' => 'stripe',
            'event_id' => $event->id,
            'type' => $event->type,
            'payload' => $event->toArray(),
        ]);

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event),
            'checkout.session.expired' => $this->handleCheckoutExpired($event),
            'checkout.session.async_payment_failed' => $this->handleAsyncPaymentFailed($event),
            default => Log::info('Unhandled Stripe webhook event type', ['type' => $event->type]),
        };

        $webhookEvent->update(['processed_at' => now()]);

        return response('OK', 200);
    }

    private function handleCheckoutCompleted(Event $event): void
    {
        $session = $event->data->object;
        $orderId = $session->metadata->order_id ?? null;

        if (! $orderId) {
            Log::error('Stripe checkout.session.completed missing order_id metadata', ['session_id' => $session->id]);

            return;
        }

        $order = DB::transaction(function () use ($orderId, $session) {
            $order = Order::where('id', $orderId)->lockForUpdate()->first();

            if (! $order || $order->status === OrderStatus::Paid) {
                return $order;
            }

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

            $order->update([
                'status' => OrderStatus::Paid,
                'paid_at' => now(),
                'notes' => $conflicts
                    ? trim(($order->notes ? $order->notes . "\n" : '') . "STOCK CONFLICT (needs manual review):\n" . implode("\n", $conflicts))
                    : $order->notes,
            ]);

            Payment::where('provider', 'stripe')
                ->where('provider_reference', $session->id)
                ->update([
                    'status' => PaymentStatus::Succeeded,
                    'paid_at' => now(),
                    'raw_response' => $session->toArray(),
                ]);

            return $order;
        });

        if (! $order) {
            Log::error('Stripe checkout.session.completed referenced an unknown order', ['order_id' => $orderId]);

            return;
        }

        // Money-moving state is already committed above; email is the slow
        // part (SMTP + PDF render), kept off the synchronous webhook path so
        // it can't cause Stripe to time out and retry.
        Mail::to($order->customer_email)->queue(new OrderReceiptMail($order));

        if ($order->placed_as_guest && $order->user_id) {
            $order->loadMissing('user');
            Mail::to($order->user->email)->queue(new OrderAccountMatchMail($order));
        }
    }

    private function handleCheckoutExpired(Event $event): void
    {
        $session = $event->data->object;
        $orderId = $session->metadata->order_id ?? null;

        $order = $orderId ? Order::find($orderId) : null;

        if ($order && $order->status === OrderStatus::PendingPayment) {
            $order->update(['status' => OrderStatus::Cancelled, 'cancelled_at' => now()]);
        }
    }

    private function handleAsyncPaymentFailed(Event $event): void
    {
        $session = $event->data->object;
        $orderId = $session->metadata->order_id ?? null;

        $order = $orderId ? Order::find($orderId) : null;

        if ($order) {
            $order->update(['status' => OrderStatus::PaymentFailed]);

            Payment::where('provider', 'stripe')
                ->where('provider_reference', $session->id)
                ->update(['status' => PaymentStatus::Failed]);
        }
    }
}
