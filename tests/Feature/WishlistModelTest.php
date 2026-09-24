<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_wishlist_and_wishlist_item_relationships()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $wishlist = Wishlist::create(['user_id' => $user->id]);
        
        $item = WishlistItem::create([
            'wishlist_id' => $wishlist->id,
            'product_id' => $product->id
        ]);

        // Test Wishlist
        $this->assertEquals($user->id, $wishlist->user->id);
        $this->assertCount(1, $wishlist->items);
        $this->assertTrue($wishlist->products->contains($product));

        // Test WishlistItem
        $this->assertEquals($wishlist->id, $item->wishlist->id);
        $this->assertEquals($product->id, $item->product->id);
    }
}
