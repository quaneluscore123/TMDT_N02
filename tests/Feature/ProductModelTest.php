<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_model_methods()
    {
        $product = Product::factory()->create(['stock' => 10]);
        $outOfStockProduct = Product::factory()->create(['stock' => 0]);

        // 1. primaryImage()
        $image = ProductImage::factory()->create([
            'product_id' => $product->id,
            'is_primary' => true
        ]);
        $this->assertEquals($image->id, $product->primaryImage->id);

        // 2. orderItems()
        $order = Order::factory()->create();
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'price' => 1000,
            'subtotal' => 1000
        ]);
        $this->assertCount(1, $product->orderItems);

        // 3. wishlistedBy()
        $user = User::factory()->create();
        $wishlist = Wishlist::create(['user_id' => $user->id]);
        WishlistItem::create(['wishlist_id' => $wishlist->id, 'product_id' => $product->id]);
        $this->assertCount(1, $product->wishlistedBy()->get());
        $this->assertEquals($user->id, $product->wishlistedBy()->first()->id);

        // 4. scopeInStock()
        $inStockProducts = Product::inStock()->get();
        $this->assertTrue($inStockProducts->contains($product));
        $this->assertFalse($inStockProducts->contains($outOfStockProduct));

        // 5. getAverageRatingAttribute() & getReviewsCountAttribute()
        Review::factory()->create([
            'product_id' => $product->id,
            'status' => 'approved',
            'rating' => 4
        ]);
        Review::factory()->create([
            'product_id' => $product->id,
            'status' => 'approved',
            'rating' => 5
        ]);
        Review::factory()->create([
            'product_id' => $product->id,
            'status' => 'pending', // Pending shouldn't be counted
            'rating' => 1
        ]);

        $this->assertEquals(2, $product->reviews_count);
        $this->assertEquals(4.5, $product->average_rating);
    }
}
