<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewModerationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function makeProduct(): Product
    {
        $category = Category::factory()->create(['parent_id' => null, 'status' => 'active']);

        return Product::factory()->create([
            'category_id' => $category->id,
            'status' => 'active',
        ]);
    }

    private function makeReview(Product $product, array $overrides = []): Review
    {
        $user = User::factory()->create();

        return Review::factory()->create(array_merge([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ], $overrides));
    }

    public function test_pending_review_is_not_shown_on_product_page(): void
    {
        $product = $this->makeProduct();
        $this->makeReview($product, [
            'status' => 'pending',
            'comment' => 'Binh luan pending khong duoc hien',
        ]);

        $response = $this->get(route('products.show', $product));

        $response->assertOk()
            ->assertDontSee('Binh luan pending khong duoc hien');
    }

    public function test_approved_review_is_shown_on_product_page(): void
    {
        $product = $this->makeProduct();
        $this->makeReview($product, [
            'status' => 'approved',
            'comment' => 'Binh luan approved hien cong khai',
        ]);

        $response = $this->get(route('products.show', $product));

        $response->assertOk()
            ->assertSee('Binh luan approved hien cong khai');
    }

    public function test_rejected_review_is_not_shown_on_product_page(): void
    {
        $product = $this->makeProduct();
        $this->makeReview($product, [
            'status' => 'rejected',
            'comment' => 'Binh luan bi tu choi',
        ]);

        $response = $this->get(route('products.show', $product));

        $response->assertOk()
            ->assertDontSee('Binh luan bi tu choi');
    }

    public function test_average_rating_only_counts_approved(): void
    {
        $product = $this->makeProduct();
        $this->makeReview($product, ['status' => 'approved', 'rating' => 5]);
        $this->makeReview($product, ['status' => 'pending', 'rating' => 1]);

        $product->refresh();

        $this->assertSame(5.0, $product->average_rating);
        $this->assertSame(1, $product->reviews_count);
    }

    public function test_admin_can_view_reviews_index(): void
    {
        Review::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.reviews.index'));

        $response->assertOk();
    }

    public function test_admin_can_approve_review(): void
    {
        $review = Review::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.reviews.approve', $review));

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_reject_review(): void
    {
        $review = Review::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.reviews.reject', $review));

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => 'rejected',
        ]);
    }

    public function test_admin_can_delete_review(): void
    {
        $review = Review::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.reviews.destroy', $review));

        $response->assertRedirect();
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_admin_can_filter_by_status(): void
    {
        Review::factory()->create(['status' => 'pending', 'comment' => 'Pending review one']);
        Review::factory()->create(['status' => 'approved', 'comment' => 'Approved review one']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.reviews.index', ['status' => 'pending']));

        $response->assertOk()
            ->assertSee('Pending review one')
            ->assertDontSee('Approved review one');
    }

    public function test_admin_can_search_reviews(): void
    {
        $product = $this->makeProduct();
        $this->makeReview($product, ['status' => 'pending', 'comment' => 'San pham rat tot dep']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.reviews.index', ['search' => 'rat tot dep']));

        $response->assertOk()->assertSee('San pham rat tot dep');
    }

    public function test_customer_cannot_access_review_moderation(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get(route('admin.reviews.index'));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_review_moderation(): void
    {
        $this->app['auth']->forgetGuards();
        $this->flushSession();

        $response = $this->get(route('admin.reviews.index'));

        $response->assertRedirect('/login');
    }

    public function test_review_store_sets_pending_status(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $category = $product->category;

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
            'subtotal' => $product->price,
        ]);

        $response = $this->actingAs($user)->post(
            route('reviews.store', $product),
            [
                'rating' => 5,
                'comment' => 'Danh gia moi pending',
            ]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'comment' => 'Danh gia moi pending',
        ]);

        $this->get(route('products.show', $product))
            ->assertDontSee('Danh gia moi pending');
    }

    public function test_cannot_review_before_order_is_delivered(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'shipping',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
            'subtotal' => $product->price,
        ]);

        $this->actingAs($user)
            ->post(route('reviews.store', $product), [
                'rating' => 5,
                'comment' => 'Chua nhan hang nhung van danh gia',
            ])
            ->assertSessionHasErrors('comment');

        $this->assertDatabaseMissing('reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_review_form_hidden_before_delivery(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
            'subtotal' => $product->price,
        ]);

        $this->actingAs($user)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertDontSee('Viết đánh giá của bạn')
            ->assertSee('Mua sản phẩm để để lại đánh giá.');
    }

    public function test_review_form_shown_after_delivery(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
            'subtotal' => $product->price,
        ]);

        $this->actingAs($user)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Viết đánh giá của bạn');
    }

    public function test_delivered_order_shows_review_link_on_order_page(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
            'subtotal' => $product->price,
        ]);

        $this->actingAs($user)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Đánh giá sản phẩm này');
    }

    public function test_order_page_has_no_review_link_before_delivery(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'shipping',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
            'subtotal' => $product->price,
        ]);

        $this->actingAs($user)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertDontSee('Đánh giá sản phẩm này');
    }

    public function test_approve_makes_review_visible_on_product_page(): void
    {
        $product = $this->makeProduct();
        $review = $this->makeReview($product, [
            'status' => 'pending',
            'comment' => 'Danh gia sau khi duyet',
        ]);

        $this->get(route('products.show', $product))
            ->assertDontSee('Danh gia sau khi duyet');

        $this->actingAs($this->admin)
            ->patch(route('admin.reviews.approve', $review));

        $this->get(route('products.show', $product))
            ->assertSee('Danh gia sau khi duyet');
    }
}
