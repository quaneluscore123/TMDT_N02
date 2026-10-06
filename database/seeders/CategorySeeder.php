<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Thời trang nam',
                'slug' => 'thoi-trang-nam',
                'children' => ['Áo nam', 'Quần nam', 'Phụ kiện nam'],
            ],
            [
                'name' => 'Thời trang nữ',
                'slug' => 'thoi-trang-nu',
                'children' => ['Áo nữ', 'Quần & Váy', 'Phụ kiện nữ'],
            ],
            [
                'name' => 'Giày dép',
                'slug' => 'giay-dep',
                'children' => ['Giày thể thao', 'Giày tây', 'Sandal & Dép'],
            ],
            [
                'name' => 'Túi & Ví',
                'slug' => 'tui-vi',
                'children' => ['Túi xách', 'Balo', 'Ví da'],
            ],
            [
                'name' => 'Mỹ phẩm & Nước hoa',
                'slug' => 'my-pham-nuoc-hoa',
                'children' => ['Son & Phấn', 'Dưỡng da', 'Nước hoa'],
            ],
        ];

        foreach ($categories as $data) {
            $parent = Category::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'parent_id' => null,
                'sort_order' => 0,
                'status' => 'active',
            ]);

            foreach ($data['children'] as $i => $childName) {
                Category::create([
                    'name' => $childName,
                    'slug' => Str::slug($childName).'-'.$parent->id,
                    'parent_id' => $parent->id,
                    'sort_order' => $i + 1,
                    'status' => 'active',
                ]);
            }
        }
    }
}
