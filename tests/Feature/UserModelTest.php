<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_methods()
    {
        $user = User::factory()->create();

        Order::factory()->create(['user_id' => $user->id]);
        $this->assertCount(1, $user->orders);

        $wishlist = Wishlist::create(['user_id' => $user->id]);
        $this->assertEquals($wishlist->id, $user->wishlist->id);

        $product = Product::factory()->create();
        Review::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);
        $this->assertCount(1, $user->reviews);
        WishlistItem::create(['wishlist_id' => $wishlist->id, 'product_id' => $product->id]);
        $this->assertCount(1, $user->wishlistedProducts()->get());
        $this->assertEquals($product->id, $user->wishlistedProducts()->first()->id);
        $userWithoutWishlist = User::factory()->create();
        $this->assertCount(0, $userWithoutWishlist->wishlistedProducts()->get());
    }
}
