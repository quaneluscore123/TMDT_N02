<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
