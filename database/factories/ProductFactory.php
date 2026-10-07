<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    // Tên sản phẩm mẫu (tiếng Việt)
    private static array $productNames = [
        'Áo thun cotton',
        'Quần jean slim fit',
        'Giày thể thao',
        'Túi xách da',
        'Đồng hồ thời trang',
        'Kính mắt chống UV',
        'Mũ beret thời trang',
        'Balo thời trang',
        'Ví da nam',
        'Thắt lưng da',
        'Áo khoác denim',
        'Váy hoa mùa hè',
        'Sandal nữ',
        'Nước hoa mini',
        'Son môi lì',
    ];

    public function definition(): array
    {
        $name = fake()->randomElement(self::$productNames).' '.fake()->randomElement(['cao cấp', 'thời trang', 'chính hãng', 'mới nhất']);
        $price = fake()->numberBetween(50000, 5000000); // 50K–5M VNĐ
        $hasSale = fake()->boolean(40); // 40% có sale
        $salePrice = $hasSale ? (int) ($price * fake()->randomFloat(2, 0.6, 0.9)) : null;

        return [
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory()->create()->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(5),
            'sku' => strtoupper(Str::random(3)).'-'.fake()->numberBetween(1000, 9999),
            'description' => fake('vi_VN')->paragraphs(2, true),
            'price' => $price,
            'sale_price' => $salePrice,
            'stock' => fake()->numberBetween(0, 200),
            'brand' => fake()->randomElement(['Nike', 'Adidas', 'Local Brand', 'Uniqlo', 'Zara', null]),
            'status' => fake()->randomElement(['active', 'active', 'active', 'inactive']), // 75% active
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => 'active', 'stock' => fake()->numberBetween(10, 200)]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock' => 0, 'status' => 'out_of_stock']);
    }
}
