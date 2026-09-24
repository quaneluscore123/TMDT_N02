<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_show_passes_meta_and_jsonld_to_layout(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'status' => 'active',
            'name' => 'Áo Thun SEO Test',
            'description' => 'Mô tả sản phẩm dùng để kiểm tra meta description',
            'slug' => 'ao-thun-seo-test',
        ]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('<title>Áo Thun SEO Test - SocialShop</title>', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('"@type": "Product"', false);
    }

    public function test_product_jsonld_includes_price_and_availability(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'status' => 'active',
            'stock' => 10,
        ]);

        $response = $this->get(route('products.show', $product));
        $response->assertOk();
        $html = $response->getContent();
        $this->assertStringContainsString('"priceCurrency": "VND"', $html);
        $this->assertStringContainsString('"price": '.$product->effectivePrice(), $html);
        $this->assertStringNotContainsString('"price": '.($product->price / 1000), $html);

        $this->assertStringContainsString('"@type": "Offer"', $html);
        $this->assertStringContainsString('InStock', $html);
        $this->assertStringContainsString('priceCurrency', $html);
    }
}
