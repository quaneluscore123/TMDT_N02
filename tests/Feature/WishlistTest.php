<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'role' => 'customer',
        ]);
    }

    public function test_wishlist_page_requires_login(): void
    {
        $this->get('/wishlist')
            ->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_wishlist(): void
    {
        $this->actingAs($this->user)
            ->get('/wishlist')
            ->assertOk();
    }

    public function test_user_can_add_product_to_wishlist(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'status' => 'active',
        ]);

        $this->actingAs($this->user)
            ->postJson("/wishlist/{$product->id}/toggle")
            ->assertOk()
            ->assertJson([
                'success' => true,
                'is_wishlisted' => true,
            ]);

        $this->assertDatabaseHas('wishlist_items', [
            'product_id' => $product->id,
        ]);
    }

    public function test_user_can_remove_product_from_wishlist(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'status' => 'active',
        ]);

        $wishlist = Wishlist::create(['user_id' => $this->user->id]);
        WishlistItem::create([
            'wishlist_id' => $wishlist->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($this->user)
            ->postJson("/wishlist/{$product->id}/toggle")
            ->assertOk()
            ->assertJson([
                'success' => true,
                'is_wishlisted' => false,
            ]);

        $this->assertDatabaseMissing('wishlist_items', [
            'product_id' => $product->id,
        ]);
    }
}
