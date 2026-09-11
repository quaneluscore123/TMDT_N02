<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompareTest extends TestCase
{
    use RefreshDatabase;

    public function test_compare_page_loads(): void
    {
        $this->get('/compare')
            ->assertOk();
    }

    public function test_user_can_add_product_to_compare(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'status' => 'active',
        ]);

        $response = $this->post("/compare/add/{$product->id}");
        $response->assertStatus(302);
        $response->assertSessionHas('compare_products');
    }

    public function test_user_can_remove_product_from_compare(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'status' => 'active',
        ]);

        $this->session(['compare_products' => [$product->id]]);

        $response = $this->post("/compare/remove/{$product->id}");
        $response->assertStatus(302);
        
        // After removal, the session should have empty array
        $this->assertEmpty(session('compare_products', []));
    }

    public function test_compare_page_shows_products(): void
    {
        $category = Category::factory()->create();
        $product1 = Product::factory()->create([
            'category_id' => $category->id,
            'status' => 'active',
        ]);
        $product2 = Product::factory()->create([
            'category_id' => $category->id,
            'status' => 'active',
        ]);

        $this->get("/compare?products[]={$product1->id}&products[]={$product2->id}")
            ->assertOk()
            ->assertSee($product1->name)
            ->assertSee($product2->name);
    }

    public function test_maximum_4_products_for_compare(): void
    {
        $category = Category::factory()->create();
        $products = Product::factory()->count(5)->create([
            'category_id' => $category->id,
            'status' => 'active',
        ]);

        $this->session(['compare_products' => $products->pluck('id')->take(4)->toArray()]);

        $this->post("/compare/add/{$products->last()->id}")
            ->assertSessionHas('error');
    }
}
