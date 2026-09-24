<?php

namespace Tests\Feature;

use App\Exceptions\CouponException;
use App\Exceptions\OrderException;
use App\Mail\OrderConfirmedMail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Order\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = app(OrderService::class);
    }

    public function test_place_order_throws_exception_if_cart_empty()
    {
        $user = User::factory()->create();

        $this->expectException(OrderException::class);
        $this->expectExceptionMessage('Giỏ hàng của bạn đang trống.');

        $this->orderService->placeOrder($user, []);
    }

    public function test_place_order_throws_exception_if_product_inactive()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => 'inactive']);
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 1000
        ]);

        $this->expectException(OrderException::class);
        $this->expectExceptionMessage('hiện không còn kinh doanh');

        $this->orderService->placeOrder($user, []);
    }

    public function test_place_order_throws_exception_if_stock_insufficient()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => 'active', 'stock' => 1]);
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 1000
        ]);

        $this->expectException(OrderException::class);
        $this->expectExceptionMessage('không đủ số lượng yêu cầu');

        $this->orderService->placeOrder($user, []);
    }

    public function test_place_order_throws_exception_if_variant_stock_insufficient()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => 'active', 'stock' => 10]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 1,
            'price' => 1000
        ]);
        
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
            'price' => 1000
        ]);

        $this->expectException(OrderException::class);
        $this->expectExceptionMessage('không đủ số lượng yêu cầu');

        $this->orderService->placeOrder($user, []);
    }

    public function test_place_order_success_without_coupon()
    {
        Mail::fake();
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => 'active', 'stock' => 10, 'price' => 100000]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 5,
            'price' => 150000
        ]);

        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
            'price' => 150000
        ]);

        $data = [
            'payment_method' => 'cod',
            'shipping_name' => 'John Doe',
            'shipping_phone' => '0123456789',
            'shipping_address' => '123 Street',
            'note' => 'Please deliver fast'
        ];

        $order = $this->orderService->placeOrder($user, $data);

        $this->assertNotNull($order);
        $this->assertEquals(300000, $order->subtotal);
        $this->assertEquals(30000, $order->shipping_fee); // Because subtotal < 500k
        $this->assertEquals(330000, $order->total);
        $this->assertEquals('John Doe', $order->shipping_name);

        // Check stock decremented
        $this->assertEquals(8, $product->fresh()->stock);
        $this->assertEquals(3, $variant->fresh()->stock);

        // Check cart cleared
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);

        // Check email queued
        Mail::assertQueued(OrderConfirmedMail::class);
    }

    public function test_place_order_with_valid_coupon_and_freeship()
    {
        Mail::fake();
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => 'active', 'stock' => 10, 'price' => 600000, 'sale_price' => null]);

        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 600000
        ]);

        $coupon = Coupon::forceCreate([
            'code' => 'DISCOUNT50',
            'type' => 'fixed',
            'value' => 50000,
            'min_order_amount' => 0,
            'status' => 'active',
            'usage_limit' => 10,
            'used_count' => 0
        ]);

        session()->put('coupon_code', 'DISCOUNT50');

        $data = [
            'payment_method' => 'cod',
            'shipping_name' => 'John Doe',
            'shipping_phone' => '0123456789',
            'shipping_address' => '123 Street',
        ];

        $order = $this->orderService->placeOrder($user, $data);

        $this->assertEquals(600000, $order->subtotal);
        $this->assertEquals(50000, $order->discount);
        $this->assertEquals(0, $order->shipping_fee); // Freeship because 600k >= 500k
        $this->assertEquals(550000, $order->total);

        // Check session coupon cleared
        $this->assertNull(session('coupon_code'));
    }

    public function test_place_order_with_invalid_coupon()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => 'active', 'stock' => 10, 'price' => 100000]);

        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100000
        ]);

        session()->put('coupon_code', 'INVALID_CODE');

        $data = [
            'payment_method' => 'cod',
            'shipping_name' => 'John Doe',
            'shipping_phone' => '0123456789',
            'shipping_address' => '123 Street',
        ];

        $this->expectException(CouponException::class);
        $this->orderService->placeOrder($user, $data);
    }

    public function test_get_orders_for_user()
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id]);
        
        $orders = $this->orderService->getOrdersForUser($user->id);
        
        $this->assertCount(3, $orders->items());
    }

    public function test_get_order_for_user_success()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        
        $retrieved = $this->orderService->getOrderForUser($order->id, $user->id);
        
        $this->assertEquals($order->id, $retrieved->id);
    }

    public function test_get_order_for_user_fails_for_wrong_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user1->id]);
        
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->orderService->getOrderForUser($order->id, $user2->id);
    }

    public function test_update_status()
    {
        $order = Order::factory()->create(['status' => 'pending']);
        $this->orderService->updateStatus($order, 'shipping');
        $this->assertEquals('shipping', $order->fresh()->status);
    }

    public function test_email_sending_exception_is_caught()
    {
        // Mock Mail to throw exception
        Mail::shouldReceive('to')->andThrow(new \Exception('Mail server down'));
        
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => 'active', 'stock' => 10, 'price' => 100000]);

        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100000
        ]);

        $data = [
            'payment_method' => 'cod',
            'shipping_name' => 'John Doe',
            'shipping_phone' => '0123456789',
            'shipping_address' => '123 Street',
        ];

        // Should not throw exception, just log it
        $order = $this->orderService->placeOrder($user, $data);
        $this->assertNotNull($order);
    }
}
