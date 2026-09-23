<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Mail\OrderCompletedMail;
use App\Mail\OrderReadyForPickupMail;
use App\Mail\OrderReceiptMail;
use App\Mail\OrderShippedMail;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_marking_an_order_paid_decrements_stock_and_queues_receipt(): void
    {
        Mail::fake();

        $product = Product::create([
            'category' => Product::CATEGORIES[0], 'name' => 'Test Product',
            'price' => 20, 'stock_quantity' => 10, 'active' => true,
        ]);

        $order = Order::create([
            'customer_name' => 'Test', 'customer_email' => 'test@example.com',
            'status' => OrderStatus::AwaitingBankDeposit,
            'payment_method' => PaymentMethod::BankTransfer,
            'fulfillment_method' => 'shipping',
            'subtotal' => 20, 'total' => 20,
        ]);
        $order->items()->create([
            'product_id' => $product->id, 'product_name' => $product->name,
            'unit_price' => 20, 'quantity' => 3, 'line_total' => 60,
        ]);

        $order->update(['status' => OrderStatus::Paid]);

        $this->assertSame(7, $product->fresh()->stock_quantity);
        $this->assertNotNull($order->fresh()->paid_at);
        Mail::assertQueued(OrderReceiptMail::class, fn ($mail) => $mail->hasTo('test@example.com'));

        // A second, no-op save (status unchanged) must not decrement again.
        $order->update(['notes' => 'unrelated change']);
        $this->assertSame(7, $product->fresh()->stock_quantity);
    }

    public function test_shipped_and_completed_transitions_send_their_own_emails(): void
    {
        Mail::fake();

        $order = Order::create([
            'customer_name' => 'Test', 'customer_email' => 'test@example.com',
            'status' => OrderStatus::Paid,
            'payment_method' => PaymentMethod::BankTransfer,
            'fulfillment_method' => 'shipping',
            'subtotal' => 20, 'total' => 20,
        ]);

        $order->update(['status' => OrderStatus::Shipped]);
        Mail::assertQueued(OrderShippedMail::class);
        $this->assertNotNull($order->fresh()->shipped_at);

        $order->update(['status' => OrderStatus::Completed]);
        Mail::assertQueued(OrderCompletedMail::class);
        $this->assertNotNull($order->fresh()->completed_at);
    }

    public function test_ready_for_pickup_transition_sends_its_own_email(): void
    {
        Mail::fake();

        $order = Order::create([
            'customer_name' => 'Test', 'customer_email' => 'test@example.com',
            'status' => OrderStatus::Paid,
            'payment_method' => PaymentMethod::BankTransfer,
            'fulfillment_method' => 'pickup',
            'subtotal' => 20, 'total' => 20,
        ]);

        $order->update(['status' => OrderStatus::ReadyForPickup]);

        Mail::assertQueued(OrderReadyForPickupMail::class, fn ($mail) => $mail->hasTo('test@example.com'));
        $this->assertNotNull($order->fresh()->ready_for_pickup_at);
    }

    public function test_order_emails_reply_to_the_dealership_not_the_send_from_address(): void
    {
        $order = Order::create([
            'customer_name' => 'Test', 'customer_email' => 'test@example.com',
            'status' => OrderStatus::Paid,
            'payment_method' => PaymentMethod::BankTransfer,
            'fulfillment_method' => 'shipping',
            'subtotal' => 20, 'total' => 20,
        ]);

        $mail = new OrderReceiptMail($order);
        $replyTo = $mail->envelope()->replyTo[0]->address;

        $this->assertSame('info@staryamaha.com.au', $replyTo);
    }

    public function test_pos_orders_without_an_email_are_not_sent_anything(): void
    {
        Mail::fake();

        $order = Order::create([
            'customer_name' => 'Walk-in', 'customer_email' => '',
            'status' => OrderStatus::PendingPayment,
            'payment_method' => PaymentMethod::Stripe,
            'fulfillment_method' => 'pickup',
            'subtotal' => 20, 'total' => 20,
        ]);

        $order->update(['status' => OrderStatus::Paid]);

        Mail::assertNothingQueued();
    }
}
