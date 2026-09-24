<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_relationships_and_calculations()
    {
        $user = User::factory()->create();
        $cart = Cart::create(['user_id' => $user->id]);

        $product1 = Product::factory()->create(['price' => 100000, 'sale_price' => null]);
        $product2 = Product::factory()->create(['price' => 200000, 'sale_price' => 150000]);

        $item1 = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price' => 100000
        ]);

        $item2 = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'price' => 150000
        ]);

        $this->assertEquals($cart->id, $item1->cart->id);
        $this->assertEquals($product1->id, $item1->product->id);
        
        $this->assertEquals(200000, $item1->subtotal());
        
        $this->assertEquals(150000, $item2->subtotal());

        $this->assertEquals($user->id, $cart->user->id);
        $this->assertCount(2, $cart->items);
        
        $this->assertEquals(350000, $cart->totalPrice());
        
        $this->assertEquals(3, $cart->totalItems());
    }
}
