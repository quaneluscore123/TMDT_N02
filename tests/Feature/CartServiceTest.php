<?php

namespace Tests\Feature;

use App\Exceptions\CartException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Cart\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = app(CartService::class);
    }

    public function test_get_or_create_cart()
    {
        $user = User::factory()->create();
        $cart = $this->cartService->getOrCreateCart($user->id);
        $this->assertNotNull($cart);
        $this->assertEquals($user->id, $cart->user_id);

        $cart2 = $this->cartService->getOrCreateCart($user->id);
        $this->assertEquals($cart->id, $cart2->id);
    }

    public function test_get_cart_with_items()
    {
        $user = User::factory()->create();
        $cart = $this->cartService->getCartWithItems($user->id);
        $this->assertNotNull($cart);
        $this->assertTrue($cart->relationLoaded('items'));
    }

    public function test_add_item_success()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'price' => 1000, 'status' => 'active']);

        $item = $this->cartService->addItem($user->id, $product->id, 2);
        
        $this->assertEquals(2, $item->quantity);
        $this->assertEquals($product->effectivePrice(), $item->price);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2
        ]);
    }

    public function test_add_item_with_variant_success()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'price' => 1000, 'status' => 'active']);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEST-SKU',
            'attributes' => ['color' => 'Red'],
            'stock' => 5,
            'price_modifier' => 200
        ]);

        $item = $this->cartService->addItem($user->id, $product->id, 1, $variant->id);
        
        $this->assertEquals(1, $item->quantity);
        $this->assertEquals($variant->unitPrice(), $item->price);
        $this->assertEquals($variant->id, $item->variant_id);
    }

    public function test_add_item_throws_exception_if_not_enough_stock()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 1, 'status' => 'active']);

        $this->expectException(CartException::class);
        $this->cartService->addItem($user->id, $product->id, 2);
    }

    public function test_update_item_success()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'status' => 'active']);
        $cart = Cart::create(['user_id' => $user->id]);
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 1000
        ]);

        $item = $this->cartService->updateItem($user->id, $cartItem->id, 5);
        $this->assertEquals(5, $item->quantity);
    }

    public function test_update_item_throws_exception_if_not_enough_stock()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 3, 'status' => 'active']);
        $cart = Cart::create(['user_id' => $user->id]);
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 1000
        ]);

        $this->expectException(CartException::class);
        $this->cartService->updateItem($user->id, $cartItem->id, 5);
    }

    public function test_remove_item()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => 'active']);
        $cart = Cart::create(['user_id' => $user->id]);
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 1000
        ]);

        $this->cartService->removeItem($user->id, $cartItem->id);
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function test_clear_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['status' => 'active']);
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 1000
        ]);

        $this->cartService->clearCart($user->id);
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
    }

    public function test_get_item_count()
    {
        $user = User::factory()->create();
        $this->assertEquals(0, $this->cartService->getItemCount($user->id));

        $product = Product::factory()->create(['status' => 'active']);
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => 1000
        ]);

        $this->assertEquals(3, $this->cartService->getItemCount($user->id));
    }

    // --- Guest tests ---

    public function test_add_guest_item_success()
    {
        Session::start();
        $product = Product::factory()->create(['stock' => 10, 'status' => 'active']);
        
        $this->cartService->addGuestItem($product->id, 2);
        
        $cart = session()->get('guest_cart');
        $this->assertArrayHasKey((string) $product->id, $cart);
        $this->assertEquals(2, $cart[(string) $product->id]);
    }

    public function test_add_guest_item_throws_exception_if_not_enough_stock()
    {
        Session::start();
        $product = Product::factory()->create(['stock' => 1, 'status' => 'active']);
        
        $this->expectException(CartException::class);
        $this->cartService->addGuestItem($product->id, 2);
    }

    public function test_update_guest_item_success()
    {
        Session::start();
        $product = Product::factory()->create(['stock' => 10, 'status' => 'active']);
        session()->put('guest_cart', [(string) $product->id => 1]);
        
        $this->cartService->updateGuestItem((string) $product->id, 5);
        $this->assertEquals(5, session('guest_cart')[(string) $product->id]);
    }

    public function test_update_guest_item_throws_exception_if_not_in_cart()
    {
        Session::start();
        $this->expectException(CartException::class);
        $this->cartService->updateGuestItem('999', 5);
    }

    public function test_update_guest_item_throws_exception_if_not_enough_stock()
    {
        Session::start();
        $product = Product::factory()->create(['stock' => 5, 'status' => 'active']);
        session()->put('guest_cart', [(string) $product->id => 1]);
        
        $this->expectException(CartException::class);
        $this->cartService->updateGuestItem((string) $product->id, 10);
    }

    public function test_remove_guest_item()
    {
        Session::start();
        session()->put('guest_cart', ['1' => 1]);
        $this->cartService->removeGuestItem('1');
        $this->assertEmpty(session('guest_cart'));
    }

    public function test_clear_guest_cart()
    {
        Session::start();
        session()->put('guest_cart', ['1' => 1]);
        $this->cartService->clearGuestCart();
        $this->assertNull(session('guest_cart'));
    }

    public function test_get_guest_item_count()
    {
        Session::start();
        session()->put('guest_cart', ['1' => 2, '2:3' => 3]);
        $this->assertEquals(5, $this->cartService->getGuestItemCount());
    }

    public function test_get_guest_cart_items()
    {
        Session::start();
        $product = Product::factory()->create(['status' => 'active', 'price' => 1000]);
        $variant = ProductVariant::create([
            'product_id' => $product->id, 
            'sku' => 'TEST-SKU-2',
            'attributes' => ['size' => 'L'],
            'stock' => 10,
            'price_modifier' => 500
        ]);
        
        session()->put('guest_cart', [
            (string) $product->id => 2,
            $product->id . ':' . $variant->id => 1,
            '999' => 1 // Non-existent product should be ignored
        ]);

        $items = $this->cartService->getGuestCartItems();

        $this->assertCount(2, $items);
        
        $item1 = $items->firstWhere('id', (string) $product->id);
        $this->assertNotNull($item1);
        $this->assertEquals(2, $item1->quantity);
        
        $item2 = $items->firstWhere('id', $product->id . ':' . $variant->id);
        $this->assertNotNull($item2);
        $this->assertEquals(1, $item2->quantity);
    }

    public function test_get_guest_cart_items_empty()
    {
        Session::start();
        $this->assertCount(0, $this->cartService->getGuestCartItems());
    }

    public function test_merge_guest_cart()
    {
        Session::start();
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'status' => 'active']);
        
        session()->put('guest_cart', [(string) $product->id => 2]);

        $this->cartService->mergeGuestCart($user->id);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2
        ]);
        $this->assertNull(session('guest_cart'));
    }

    public function test_is_guest()
    {
        $this->assertTrue($this->cartService->isGuest());
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertFalse($this->cartService->isGuest());
    }
}
