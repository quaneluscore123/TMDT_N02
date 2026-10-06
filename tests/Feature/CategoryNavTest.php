<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryNavTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_shows_category_menu_label(): void
    {
        $this->get('/')->assertOk()->assertSee('Danh mục');
    }

    public function test_header_shows_active_parent_and_child_categories(): void
    {
        $parent = Category::factory()->create([
            'name' => 'Thời trang nav test',
            'parent_id' => null,
            'status' => 'active',
        ]);
        Category::factory()->child($parent->id)->create([
            'name' => 'Áo nav test',
            'status' => 'active',
        ]);
        Category::factory()->child($parent->id)->inactive()->create([
            'name' => 'Ẩn nav test',
            'status' => 'inactive',
        ]);
        Category::factory()->inactive()->create([
            'name' => 'Danh mục cha ẩn',
            'parent_id' => null,
            'status' => 'inactive',
        ]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Thời trang nav test')
            ->assertSee('Áo nav test')
            ->assertDontSee('Ẩn nav test')
            ->assertDontSee('Danh mục cha ẩn');
    }

    public function test_parent_category_filter_includes_active_children_products(): void
    {
        $parent = Category::factory()->create(['parent_id' => null, 'status' => 'active']);
        $child1 = Category::factory()->child($parent->id)->create(['status' => 'active']);
        $child2 = Category::factory()->child($parent->id)->create(['status' => 'active']);
        $inactiveChild = Category::factory()->child($parent->id)->inactive()->create(['status' => 'inactive']);

        Product::factory()->create(['category_id' => $child1->id, 'status' => 'active']);
        Product::factory()->create(['category_id' => $child2->id, 'status' => 'active']);
        Product::factory()->create(['category_id' => $inactiveChild->id, 'status' => 'active']);

        $response = $this->get("/products?category={$parent->id}");

        $response->assertOk();
        $this->assertEquals(2, $response->viewData('products')->total());
    }

    public function test_child_category_filter_is_exact(): void
    {
        $parent = Category::factory()->create(['parent_id' => null, 'status' => 'active']);
        $child1 = Category::factory()->child($parent->id)->create(['status' => 'active']);
        $child2 = Category::factory()->child($parent->id)->create(['status' => 'active']);

        Product::factory()->create(['category_id' => $child1->id, 'status' => 'active']);
        Product::factory()->create(['category_id' => $child2->id, 'status' => 'active']);

        $response = $this->get("/products?category={$child1->id}");

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('products')->total());
    }

    public function test_parent_category_filter_includes_products_assigned_to_parent(): void
    {
        $parent = Category::factory()->create(['parent_id' => null, 'status' => 'active']);
        $child = Category::factory()->child($parent->id)->create(['status' => 'active']);

        Product::factory()->create(['category_id' => $parent->id, 'status' => 'active']);
        Product::factory()->create(['category_id' => $child->id, 'status' => 'active']);

        $response = $this->get("/products?category={$parent->id}");

        $response->assertOk();
        $this->assertEquals(2, $response->viewData('products')->total());
    }
}
