<?php

namespace Tests\Feature;

use App\Http\Controllers\CheckoutController;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Services\PaymentAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutPickupTest extends TestCase
{
    use RefreshDatabase;

    private function checkoutAsGuest(Product $product, array $overrides = []): Order
    {
        $this->startSession();
        $sessionId = $this->app['session']->getId();

        CartItem::create(['session_id' => $sessionId, 'product_id' => $product->id, 'quantity' => 1]);

        $controller = $this->app->make(CheckoutController::class);
        $availability = $this->app->make(PaymentAvailability::class);

        $storeRequest = Request::create('/checkout', 'POST', array_merge([
            'name' => 'Test Customer', 'email' => 'test@example.com', 'phone' => '0400000000',
            'different_billing' => '0', 'payment_method' => 'bank_transfer',
        ], $overrides));
        $storeRequest->setLaravelSession($this->app['session']->driver());
        $controller->store($storeRequest, $availability);

        $confirmRequest = Request::create('/checkout/confirm', 'POST');
        $confirmRequest->setLaravelSession($this->app['session']->driver());
        $controller->confirm($confirmRequest);

        return Order::where('customer_email', $overrides['email'] ?? 'test@example.com')->firstOrFail();
    }

    public function test_pickup_checkout_skips_shipping_address_and_cost(): void
    {
        Mail::fake();

        $product = Product::create([
            'category' => Product::CATEGORIES[0], 'name' => 'Test Product',
            'price' => 20, 'stock_quantity' => 10, 'active' => true,
        ]);

        $order = $this->checkoutAsGuest($product, [
            'email' => 'pickup@example.com',
            'fulfillment_method' => 'pickup',
            'notes' => 'Leave at the counter',
        ]);

        $this->assertSame('pickup', $order->fulfillment_method);
        $this->assertEquals(0, $order->shipping_total);
        $this->assertSame('Leave at the counter', $order->customer_notes);
        $this->assertNull($order->shippingAddress);
    }

    public function test_shipping_checkout_still_requires_and_stores_an_address(): void
    {
        Mail::fake();

        $product = Product::create([
            'category' => Product::CATEGORIES[0], 'name' => 'Test Product',
            'price' => 20, 'stock_quantity' => 10, 'active' => true,
        ]);

        $order = $this->checkoutAsGuest($product, [
            'email' => 'ship@example.com',
            'fulfillment_method' => 'shipping',
            'line1' => '1 Test St', 'suburb' => 'Testville', 'state' => 'QLD', 'postcode' => '4000',
        ]);

        $this->assertSame('shipping', $order->fulfillment_method);
        $this->assertGreaterThan(0, $order->shipping_total);
        $this->assertSame('1 Test St', $order->shippingAddress->line1);
    }
}
