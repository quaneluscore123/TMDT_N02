<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private function createProduct(array $attrs = []): Product
    {
        $cat = Category::factory()->create();

        return Product::factory()->create(array_merge([
            'category_id' => $cat->id,
            'status' => 'active',
            'price' => 100000,
            'sale_price' => null,
            'stock' => 10,
        ], $attrs));
    }

    public function test_guest_can_view_empty_cart(): void
    {
        $this->get('/cart')->assertStatus(200)->assertViewIs('cart.index');
    }

    public function test_guest_can_add_product_to_cart(): void
    {
        $product = $this->createProduct();

        $response = $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect();
        $this->assertEquals(2, $this->getGuestCartCount());
    }

    public function test_guest_cart_merges_on_login(): void
    {
        $product = $this->createProduct();
        $user = User::factory()->create();

        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    private function getGuestCartCount(): int
    {
        return (int) array_sum(session()->get('guest_cart', []));
    }

    public function test_authenticated_user_can_view_cart(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/cart')->assertStatus(200)->assertViewIs('cart.index');
    }

    public function test_user_can_add_product_to_cart(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct();

        $response = $this->actingAs($user)->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_adding_same_product_increases_quantity(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct(['stock' => 10]);

        $this->actingAs($user)->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);
        $this->actingAs($user)->post('/cart/add', ['product_id' => $product->id, 'quantity' => 3]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);
    }

    public function test_user_can_remove_item_from_cart(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct();

        $this->actingAs($user)->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

        $cartItem = Cart::where('user_id', $user->id)->first()->items()->first();

        $this->actingAs($user)->post('/cart/remove', ['rowId' => $cartItem->id]);

        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function test_user_can_clear_cart(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct();

        $this->actingAs($user)->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);

        $this->actingAs($user)->post('/cart/clear');

        $this->assertDatabaseMissing('cart_items', ['product_id' => $product->id]);
    }
}
