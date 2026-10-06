<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReviewSubmissionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: Product}
     */
    private function buyerWithDeliveredProduct(): array
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
            'status' => 'active',
        ]);
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'delivered']);
        $order->items()->create([
            'product_id' => $product->id, 'product_name' => $product->name,
            'price' => 1000, 'quantity' => 1, 'subtotal' => 1000,
        ]);

        return [$user, $product];
    }

    public function test_review_with_image_is_stored_as_pending(): void
    {
        Storage::fake('public');
        [$user, $product] = $this->buyerWithDeliveredProduct();

        $this->actingAs($user)->post(route('reviews.store', $product), [
            'rating' => 5,
            'comment' => 'Tuyệt vời',
            'image' => UploadedFile::fake()->image('review.jpg'),
        ])->assertSessionHas('success');

        $review = Review::sole();
        $this->assertSame('pending', $review->status);
        $this->assertStringStartsWith('reviews/', $review->image);
        Storage::disk('public')->assertExists($review->image);
    }

    public function test_review_rejects_non_image_upload(): void
    {
        Storage::fake('public');
        [$user, $product] = $this->buyerWithDeliveredProduct();

        $this->actingAs($user)->post(route('reviews.store', $product), [
            'rating' => 5,
            'image' => UploadedFile::fake()->create('virus.php', 10, 'application/x-php'),
        ])->assertSessionHasErrors('image');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_review_rejects_image_over_2mb(): void
    {
        Storage::fake('public');
        [$user, $product] = $this->buyerWithDeliveredProduct();

        $this->actingAs($user)->post(route('reviews.store', $product), [
            'rating' => 4,
            'image' => UploadedFile::fake()->image('big.jpg')->size(3000),
        ])->assertSessionHasErrors('image');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_review_submission_is_throttled(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['category_id' => Category::factory()->create()->id]);

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($user)->post(route('reviews.store', $product), ['rating' => 5]);
        }

        $this->actingAs($user)->post(route('reviews.store', $product), ['rating' => 5])
            ->assertStatus(429);
    }
}
