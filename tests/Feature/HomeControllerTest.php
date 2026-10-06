<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_displays_categories_and_products()
    {
        $category = Category::factory()->create(['status' => 'active']);
        $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active']);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.home');
        $response->assertSee($category->name);
        $response->assertSee($product->name);
    }

    public function test_home_product_cards_do_not_cause_n_plus_one_queries()
    {
        $category = Category::factory()->create(['status' => 'active']);
        $products = Product::factory()->count(8)->create(['category_id' => $category->id, 'status' => 'active']);
        foreach ($products as $product) {
            ProductImage::factory()->create(['product_id' => $product->id, 'is_primary' => true]);
            Review::factory()->create(['product_id' => $product->id, 'status' => 'approved', 'rating' => 4]);
        }

        DB::enableQueryLog();
        $this->get(route('home'))->assertOk()->assertSee('4.0');
        $queryCount = count(DB::getQueryLog());

        // Trước đây mỗi card tốn 3-5 query (ảnh, đếm review, trung bình sao) → 16 card ≈ 60+ query
        $this->assertLessThan(25, $queryCount);
    }
}
