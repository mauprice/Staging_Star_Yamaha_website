<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Mail\OrderAccountMatchMail;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
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
            'payment_intent.succeeded' => $this->handlePaymentIntentSucceeded($event),
            'payment_intent.payment_failed' => $this->handlePaymentIntentFailed($event),
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

            // Stock decrement and the receipt email are handled centrally by
            // OrderObserver, triggered by this status transition - keeps the
            // same behavior working for every payment path (bank transfer,
            // POS), not just Stripe.
            $order->update(['status' => OrderStatus::Paid]);

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

    /**
     * Card-present POS sales create a PaymentIntent directly (no Checkout
     * Session), so their result arrives here instead of via
     * checkout.session.completed. Scoped to metadata.source=pos - Checkout
     * Sessions create a PaymentIntent internally too, but its Payment row
     * is keyed by the session id, not the intent id, so this lookup would
     * no-op for those anyway; the metadata check just makes the intent explicit.
     */
    private function handlePaymentIntentSucceeded(Event $event): void
    {
        $intent = $event->data->object;

        if (($intent->metadata->source ?? null) !== 'pos') {
            return;
        }

        $orderId = $intent->metadata->order_id ?? null;

        if (! $orderId) {
            Log::error('Stripe payment_intent.succeeded (POS) missing order_id metadata', ['payment_intent_id' => $intent->id]);

            return;
        }

        DB::transaction(function () use ($orderId, $intent) {
            $order = Order::where('id', $orderId)->lockForUpdate()->first();

            if (! $order || $order->status === OrderStatus::Paid) {
                return;
            }

            $order->update(['status' => OrderStatus::Paid]);

            Payment::where('provider', 'stripe')
                ->where('provider_reference', $intent->id)
                ->update([
                    'status' => PaymentStatus::Succeeded,
                    'paid_at' => now(),
                    'raw_response' => $intent->toArray(),
                ]);
        });
    }

    private function handlePaymentIntentFailed(Event $event): void
    {
        $intent = $event->data->object;

        if (($intent->metadata->source ?? null) !== 'pos') {
            return;
        }

        $orderId = $intent->metadata->order_id ?? null;
        $order = $orderId ? Order::find($orderId) : null;

        if ($order && $order->status === OrderStatus::PendingPayment) {
            $order->update(['status' => OrderStatus::PaymentFailed]);

            Payment::where('provider', 'stripe')
                ->where('provider_reference', $intent->id)
                ->update(['status' => PaymentStatus::Failed]);
        }
    }
}
