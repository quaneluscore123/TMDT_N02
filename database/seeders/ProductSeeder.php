<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy tất cả subcategories (có parent_id)
        $categoryIds = Category::whereNotNull('parent_id')->pluck('id')->toArray();

        // Tạo 50 sản phẩm
        Product::factory()
            ->count(50)
            ->state(function () use ($categoryIds) {
                return ['category_id' => fake()->randomElement($categoryIds)];
            })
            ->create()
            ->each(function (Product $product) {
                // Mỗi sản phẩm có 2–3 ảnh (dùng placeholder URL)
                $imageCount = fake()->numberBetween(2, 3);
                for ($i = 0; $i < $imageCount; $i++) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => "https://picsum.photos/seed/{$product->id}-{$i}/600/600",
                        'is_primary' => $i === 0,
                        'sort_order' => $i,
                    ]);
                }
            });
    }
}
