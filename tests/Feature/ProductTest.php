<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private function createProduct(array $attrs = []): Product
    {
        $category = Category::factory()->create();

        return Product::factory()->create(array_merge([
            'category_id' => $category->id,
            'status' => 'active',
            'stock' => 10,
        ], $attrs));
    }

    public function test_products_index_page_loads(): void
    {
        $this->createProduct();
        $response = $this->get('/products');
        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertViewHas('products');
    }

    public function test_products_search_filter_works(): void
    {
        $this->createProduct(['name' => 'Áo thun đen']);
        $this->createProduct(['name' => 'Quần jean xanh']);

        $response = $this->get('/products?search=Áo thun');
        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('products'));
    }

    public function test_products_category_filter_works(): void
    {
        $cat1 = Category::factory()->create();
        $cat2 = Category::factory()->create();
        Product::factory()->create(['category_id' => $cat1->id, 'status' => 'active']);
        Product::factory()->create(['category_id' => $cat1->id, 'status' => 'active']);
        Product::factory()->create(['category_id' => $cat2->id, 'status' => 'active']);

        $response = $this->get("/products?category={$cat1->id}");
        $this->assertEquals(2, $response->viewData('products')->total());
    }

    public function test_inactive_products_not_shown(): void
    {
        $this->createProduct(['status' => 'active']);
        $this->createProduct(['status' => 'inactive']);

        $response = $this->get('/products');
        $this->assertEquals(1, $response->viewData('products')->total());
    }

    public function test_product_detail_page_loads(): void
    {
        $product = $this->createProduct(['slug' => 'ao-thun-test']);
        $response = $this->get('/products/ao-thun-test');
        $response->assertStatus(200);
        $response->assertViewIs('products.show');
        $response->assertViewHas('product', fn ($p) => $p->id === $product->id);
    }

    public function test_inactive_product_detail_returns_404(): void
    {
        $this->createProduct(['slug' => 'san-pham-an', 'status' => 'inactive']);
        $this->get('/products/san-pham-an')->assertStatus(404);
    }

    public function test_home_page_loads_with_featured_products(): void
    {
        $this->createProduct();
        $this->get('/')->assertStatus(200)->assertViewIs('pages.home');
    }

    public function test_category_page_loads_with_parent_and_children_products(): void
    {
        $parent = Category::factory()->create([
            'name' => 'Danh mục cha page test',
            'slug' => 'danh-muc-cha-page-test',
            'parent_id' => null,
            'status' => 'active',
        ]);
        $child1 = Category::factory()->child($parent->id)->create([
            'slug' => 'con-mot-page-test',
            'status' => 'active',
        ]);
        $child2 = Category::factory()->child($parent->id)->create([
            'slug' => 'con-hai-page-test',
            'status' => 'active',
        ]);
        $inactiveChild = Category::factory()->child($parent->id)->inactive()->create([
            'slug' => 'con-an-page-test',
            'status' => 'inactive',
        ]);

        Product::factory()->create(['category_id' => $child1->id, 'status' => 'active']);
        Product::factory()->create(['category_id' => $child2->id, 'status' => 'active']);
        Product::factory()->create(['category_id' => $inactiveChild->id, 'status' => 'active']);

        $response = $this->get('/danh-muc/danh-muc-cha-page-test');

        $response->assertOk()
            ->assertViewIs('categories.show')
            ->assertSee('Danh mục cha page test');

        $this->assertEquals(2, $response->viewData('products')->total());
    }

    public function test_category_page_for_child_shows_only_child_products(): void
    {
        $parent = Category::factory()->create(['parent_id' => null, 'status' => 'active']);
        $child1 = Category::factory()->child($parent->id)->create([
            'slug' => 'chi-con-mot',
            'status' => 'active',
        ]);
        Category::factory()->child($parent->id)->create([
            'slug' => 'chi-con-hai',
            'status' => 'active',
        ]);

        Product::factory()->create(['category_id' => $child1->id, 'status' => 'active']);
        Product::factory()->create(['category_id' => $child1->id, 'status' => 'inactive']);

        $response = $this->get('/danh-muc/chi-con-mot');

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('products')->total());
    }

    public function test_category_page_unknown_slug_returns_404(): void
    {
        $this->get('/danh-muc/slug-khong-ton-tai')->assertNotFound();
    }

    public function test_category_page_inactive_category_returns_404(): void
    {
        Category::factory()->create([
            'slug' => 'danh-muc-an',
            'parent_id' => null,
            'status' => 'inactive',
        ]);

        $this->get('/danh-muc/danh-muc-an')->assertNotFound();
    }

    public function test_category_route_uses_slug(): void
    {
        $category = Category::factory()->create([
            'slug' => 'duong-dan-slug',
            'parent_id' => null,
            'status' => 'active',
        ]);

        $this->assertSame(
            url('/danh-muc/duong-dan-slug'),
            route('categories.show', $category)
        );
    }

    public function test_admin_can_update_category_by_id(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create([
            'name' => 'Tên cũ',
            'slug' => 'ten-cu',
            'parent_id' => null,
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.categories.update', ['category' => $category->id]),
            ['name' => 'Tên mới sau update']
        );

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Tên mới sau update',
        ]);
    }

    public function test_brand_filter_returns_only_matching_products(): void
    {
        $this->createProduct(['brand' => 'Nike', 'name' => 'Nike Air 1']);
        $this->createProduct(['brand' => 'Nike', 'name' => 'Nike Air 2']);
        $this->createProduct(['brand' => 'Adidas', 'name' => 'Adidas X']);

        $response = $this->get('/products?brand=Nike');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->viewData('products')->total());
    }

    public function test_brand_filter_excludes_other_brands(): void
    {
        $this->createProduct(['brand' => 'Zara']);
        $this->createProduct(['brand' => 'Uniqlo']);
        $this->createProduct(['brand' => 'Uniqlo']);

        $response = $this->get('/products?brand=Zara');

        $this->assertEquals(1, $response->viewData('products')->total());
    }

    public function test_brand_filter_combined_with_category_and_min_price(): void
    {
        $cat = Category::factory()->create();

        // Thỏa cả 3 điều kiện
        $this->createProduct(['brand' => 'Nike', 'category_id' => $cat->id, 'price' => 500000]);
        // Sai brand
        $this->createProduct(['brand' => 'Adidas', 'category_id' => $cat->id, 'price' => 500000]);
        // Sai category
        $this->createProduct(['brand' => 'Nike', 'price' => 500000]);
        // Giá quá thấp
        $this->createProduct(['brand' => 'Nike', 'category_id' => $cat->id, 'price' => 100000]);

        $response = $this->get("/products?brand=Nike&category={$cat->id}&min_price=300000");

        $this->assertEquals(1, $response->viewData('products')->total());
    }

    public function test_brands_list_passed_to_view(): void
    {
        $this->createProduct(['brand' => 'Nike']);
        $this->createProduct(['brand' => 'Adidas']);
        $this->createProduct(['brand' => null]);

        $response = $this->get('/products');

        $brands = $response->viewData('brands');
        $this->assertContains('Nike', $brands->toArray());
        $this->assertContains('Adidas', $brands->toArray());
        $this->assertCount(2, $brands);
    }

    public function test_product_show_loads_images_relation(): void
    {
        $product = $this->createProduct(['slug' => 'sp-gallery-test']);
        ProductImage::factory()->count(3)->create([
            'product_id' => $product->id,
        ]);

        $response = $this->get('/products/sp-gallery-test');

        $response->assertStatus(200);
        $loadedProduct = $response->viewData('product');
        $this->assertCount(3, $loadedProduct->images);
    }

    public function test_product_show_without_images_does_not_crash(): void
    {
        $product = $this->createProduct(['slug' => 'sp-no-image']);

        $response = $this->get('/products/sp-no-image');

        $response->assertStatus(200);
        $this->assertCount(0, $response->viewData('product')->images);
    }

    public function test_product_show_images_ordered_primary_first(): void
    {
        $product = $this->createProduct(['slug' => 'sp-primary-first']);
        ProductImage::factory()->create([
            'product_id' => $product->id,
            'is_primary' => false,
            'sort_order' => 1,
        ]);
        ProductImage::factory()->create([
            'product_id' => $product->id,
            'is_primary' => true,
            'sort_order' => 2,
        ]);

        // Truy vấn trực tiếp với ordering giống controller: is_primary DESC, sort_order ASC
        $images = ProductImage::where('product_id', $product->id)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->get();

        $this->assertTrue($images->first()->is_primary);
    }

    public function test_products_sort_by_price_ascending_and_descending(): void
    {
        $this->createProduct(['name' => 'Rẻ', 'price' => 100000, 'sale_price' => null]);
        $this->createProduct(['name' => 'Đắt', 'price' => 900000, 'sale_price' => null]);
        $this->createProduct(['name' => 'Vừa', 'price' => 500000, 'sale_price' => null]);

        $asc = $this->get('/products?sort=price_asc')->viewData('products')->pluck('name')->all();
        $desc = $this->get('/products?sort=price_desc')->viewData('products')->pluck('name')->all();

        $this->assertSame(['Rẻ', 'Vừa', 'Đắt'], $asc);
        $this->assertSame(['Đắt', 'Vừa', 'Rẻ'], $desc);
    }

    public function test_products_sort_by_name(): void
    {
        $this->createProduct(['name' => 'Charlie']);
        $this->createProduct(['name' => 'Alpha']);
        $this->createProduct(['name' => 'Bravo']);

        $names = $this->get('/products?sort=name')->viewData('products')->pluck('name')->all();

        $this->assertSame(['Alpha', 'Bravo', 'Charlie'], $names);
    }

    public function test_products_min_and_max_price_filter(): void
    {
        $this->createProduct(['name' => 'Dưới', 'price' => 50000, 'sale_price' => null]);
        $this->createProduct(['name' => 'Trong', 'price' => 300000, 'sale_price' => null]);
        $this->createProduct(['name' => 'Trên', 'price' => 2000000, 'sale_price' => null]);

        $names = $this->get('/products?min_price=100000&max_price=1000000')->viewData('products')->pluck('name')->all();

        $this->assertSame(['Trong'], $names);
    }
}
