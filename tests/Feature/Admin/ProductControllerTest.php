<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_index_displays_products()
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.products.index', ['search' => 'test', 'category' => $category->id]));
        $response->assertOk()->assertViewIs('admin.products.index');
    }

    public function test_create_displays_form()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.products.create'));
        $response->assertOk()->assertViewIs('admin.products.create');
    }

    public function test_store_creates_product()
    {
        Storage::fake('public');
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'New Product',
            'price' => 100,
            'category_id' => $category->id,
            'stock' => 10,
            'image' => UploadedFile::fake()->image('product.jpg'),
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', ['name' => 'New Product']);
        $this->assertDatabaseHas('product_images', ['is_primary' => true]);
    }

    public function test_edit_displays_form()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.products.edit', $product));
        $response->assertOk()->assertViewIs('admin.products.edit');
    }

    public function test_update_modifies_product_and_syncs_variants()
    {
        Storage::fake('public');
        $product = Product::factory()->create();
        ProductImage::factory()->create(['product_id' => $product->id, 'is_primary' => true]);

        $response = $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
            'name' => 'Updated Product',
            'price' => 200,
            'category_id' => $product->category_id,
            'stock' => 15,
            'image' => UploadedFile::fake()->image('new_product.jpg'),
            'variants' => [
                ['size' => 'M', 'color' => 'Red', 'stock' => 5, 'price' => 200],
                ['size' => 'L', 'color' => 'Blue', 'stock' => 10, 'price' => 220],
                ['size' => '', 'color' => '', 'stock' => 10, 'price' => null], // Should be skipped
            ],
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', ['name' => 'Updated Product']);
        $this->assertDatabaseHas('product_variants', ['size' => 'M']);
    }

    public function test_destroy_deletes_product()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $this->assertModelMissing($product);
    }

    public function test_toggle_active()
    {
        $product = Product::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->admin)->post(route('admin.products.toggle', $product), [], ['Accept' => 'application/json']);

        $response->assertOk()->assertJson(['success' => true, 'status' => 'inactive']);
        $this->assertEquals('inactive', $product->fresh()->status);

        $response2 = $this->actingAs($this->admin)->post(route('admin.products.toggle', $product));
        $response2->assertRedirect();
    }

    public function test_add_images()
    {
        Storage::fake('public');
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.products.images.add', $product), [
            'images' => [
                UploadedFile::fake()->image('img1.jpg'),
                UploadedFile::fake()->image('img2.jpg'),
            ],
        ]);

        $response->assertRedirect();
        $this->assertEquals(2, $product->images()->count());
    }

    public function test_delete_image()
    {
        Storage::fake('public');
        $product = Product::factory()->create();
        $image = ProductImage::factory()->create(['product_id' => $product->id, 'is_primary' => true, 'image_path' => 'images/products/img1.jpg']);
        $image2 = ProductImage::factory()->create(['product_id' => $product->id, 'is_primary' => false, 'image_path' => 'images/products/img2.jpg']);

        $response = $this->actingAs($this->admin)->delete(route('admin.products.images.delete', [$product, $image]));

        $response->assertRedirect();
        $this->assertModelMissing($image);
        $this->assertTrue($image2->fresh()->is_primary);
    }

    public function test_store_generates_unique_slug_for_names_that_slugify_the_same()
    {
        $category = Category::factory()->create();
        $payload = ['description' => 'x', 'price' => 100000, 'category_id' => $category->id, 'stock' => 5];

        $this->actingAs($this->admin)->post(route('admin.products.store'), $payload + ['name' => 'T-Shirt'])->assertRedirect();
        $this->actingAs($this->admin)->post(route('admin.products.store'), $payload + ['name' => 'T Shirt'])->assertRedirect();

        $this->assertDatabaseHas('products', ['name' => 'T-Shirt', 'slug' => 't-shirt']);
        $this->assertDatabaseHas('products', ['name' => 'T Shirt', 'slug' => 't-shirt-2']);
    }

    public function test_update_keeps_own_slug_and_avoids_collisions()
    {
        $category = Category::factory()->create();
        Product::factory()->create(['name' => 'Áo Polo', 'slug' => 'ao-polo', 'category_id' => $category->id]);
        $product = Product::factory()->create(['name' => 'Khác', 'slug' => 'khac', 'category_id' => $category->id]);

        $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
            'name' => 'Áo  Polo', 'price' => 100000, 'category_id' => $category->id, 'stock' => 1,
        ])->assertRedirect();

        $this->assertSame('ao-polo-2', $product->fresh()->slug);
    }

    public function test_destroy_sold_product_deactivates_instead_of_failing()
    {
        $product = Product::factory()->create(['status' => 'active']);
        $order = Order::factory()->create();
        $order->items()->create([
            'product_id' => $product->id, 'product_name' => $product->name,
            'price' => 1000, 'quantity' => 1, 'subtotal' => 1000,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('error');

        $this->assertModelExists($product);
        $this->assertSame('inactive', $product->fresh()->status);
    }

    public function test_destroy_removes_uploaded_image_files()
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/products/a.jpg', 'x');
        $product = Product::factory()->create();
        ProductImage::factory()->create(['product_id' => $product->id, 'image_path' => 'images/products/a.jpg']);

        $this->actingAs($this->admin)->delete(route('admin.products.destroy', $product))->assertRedirect();

        $this->assertModelMissing($product);
        Storage::disk('public')->assertMissing('images/products/a.jpg');
    }
}
