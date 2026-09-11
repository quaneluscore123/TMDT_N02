<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name'          => 'Admin',
            'email'         => 'admin@socialshop.vn',
            'password'      => Hash::make('password'),
            'role'          => 'admin',
            'referral_code' => 'ADMIN001',
        ]);

        // User test
        User::create([
            'name'          => 'Nguyễn Văn A',
            'email'         => 'user@socialshop.vn',
            'password'      => Hash::make('password'),
            'role'          => 'customer',
            'referral_code' => 'USER0001',
        ]);

        $categories = [
            ['name' => 'Điện thoại', 'slug' => 'dien-thoai', 'sort_order' => 1, 'status' => 'active'],
            ['name' => 'Laptop', 'slug' => 'laptop', 'sort_order' => 2, 'status' => 'active'],
            ['name' => 'Phụ kiện', 'slug' => 'phu-kien', 'sort_order' => 3, 'status' => 'active'],
            ['name' => 'Thời trang', 'slug' => 'thoi-trang', 'sort_order' => 4, 'status' => 'active'],
            ['name' => 'Đồng hồ', 'slug' => 'dong-ho', 'sort_order' => 5, 'status' => 'active'],
            ['name' => 'Giày dép', 'slug' => 'giay-dep', 'sort_order' => 6, 'status' => 'active'],
            ['name' => 'Túi xách', 'slug' => 'tui-xach', 'sort_order' => 7, 'status' => 'active'],
            ['name' => 'Mỹ phẩm', 'slug' => 'my-pham', 'sort_order' => 8, 'status' => 'active'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        $products = [
            // 1. Điện thoại (7)
            ['category_id' => 1, 'name' => 'iPhone 15 Pro Max', 'slug' => 'iphone-15-pro-max', 'description' => 'Điện thoại Apple iPhone 15 Pro Max 256GB, chip A17 Pro, camera 48MP.', 'price' => 34990000, 'stock' => 50, 'status' => 'active'],
            ['category_id' => 1, 'name' => 'Samsung Galaxy S24 Ultra', 'slug' => 'samsung-galaxy-s24-ultra', 'description' => 'Điện thoại Samsung Galaxy S24 Ultra 256GB, S Pen, AI tích hợp.', 'price' => 31990000, 'stock' => 30, 'status' => 'active'],
            ['category_id' => 1, 'name' => 'Xiaomi 14', 'slug' => 'xiaomi-14', 'description' => 'Điện thoại Xiaomi 14 256GB, camera Leica, chip Snapdragon 8 Gen 3.', 'price' => 16990000, 'stock' => 40, 'status' => 'active'],
            ['category_id' => 1, 'name' => 'iPhone 15', 'slug' => 'iphone-15', 'description' => 'Điện thoại Apple iPhone 15 128GB, chip A16 Bionic, camera 48MP.', 'price' => 22990000, 'stock' => 60, 'status' => 'active'],
            ['category_id' => 1, 'name' => 'Samsung Galaxy A55', 'slug' => 'samsung-galaxy-a55', 'description' => 'Điện thoại Samsung Galaxy A55 5G, màn hình Super AMOLED 120Hz, camera 50MP.', 'price' => 10990000, 'stock' => 80, 'status' => 'active'],
            ['category_id' => 1, 'name' => 'OPPO Reno11 F', 'slug' => 'oppo-reno11-f', 'description' => 'Điện thoại OPPO Reno11 F 5G, camera 64MP, sạc nhanh 67W.', 'price' => 8990000, 'stock' => 50, 'status' => 'active'],
            ['category_id' => 1, 'name' => 'Xiaomi Redmi Note 13', 'slug' => 'xiaomi-redmi-note-13', 'description' => 'Điện thoại Xiaomi Redmi Note 13 Pro, camera 200MP, pin 5000mAh.', 'price' => 6990000, 'stock' => 70, 'status' => 'active'],

            // 2. Laptop (7)
            ['category_id' => 2, 'name' => 'MacBook Air M3', 'slug' => 'macbook-air-m3', 'description' => 'Laptop Apple MacBook Air M3, 15 inch, 16GB RAM, 512GB SSD.', 'price' => 36990000, 'stock' => 20, 'status' => 'active'],
            ['category_id' => 2, 'name' => 'Dell XPS 15', 'slug' => 'dell-xps-15', 'description' => 'Laptop Dell XPS 15, Intel Core i7, 16GB RAM, 512GB SSD.', 'price' => 29990000, 'stock' => 15, 'status' => 'active'],
            ['category_id' => 2, 'name' => 'ASUS ROG Zephyrus G14', 'slug' => 'asus-rog-zephyrus-g14', 'description' => 'Laptop gaming ASUS ROG Zephyrus G14, Ryzen 9, RTX 4060.', 'price' => 32990000, 'stock' => 10, 'status' => 'active'],
            ['category_id' => 2, 'name' => 'HP Spectre x360', 'slug' => 'hp-spectre-x360', 'description' => 'Laptop HP Spectre x360 14, touchscreen OLED, Intel Core Ultra 7, 16GB RAM.', 'price' => 35990000, 'stock' => 15, 'status' => 'active'],
            ['category_id' => 2, 'name' => 'Lenovo ThinkPad X1 Carbon', 'slug' => 'lenovo-thinkpad-x1-carbon', 'description' => 'Laptop Lenovo ThinkPad X1 Carbon Gen 11, Intel Core i7, 16GB RAM, 512GB SSD.', 'price' => 31990000, 'stock' => 12, 'status' => 'active'],
            ['category_id' => 2, 'name' => 'Acer Swift 7', 'slug' => 'acer-swift-7', 'description' => 'Laptop Acer Swift 7 siêu mỏng nhẹ, Intel Core i5, 8GB RAM, 512GB SSD.', 'price' => 22990000, 'stock' => 20, 'status' => 'active'],
            ['category_id' => 2, 'name' => 'MSI Prestige 16 AI', 'slug' => 'msi-prestige-16-ai', 'description' => 'Laptop MSI Prestige 16 AI Evo, Intel Core Ultra, màn hình OLED 16 inch.', 'price' => 28990000, 'stock' => 10, 'status' => 'active'],

            // 3. Phụ kiện (7)
            ['category_id' => 3, 'name' => 'AirPods Pro 2', 'slug' => 'airpods-pro-2', 'description' => 'Tai nghe Apple AirPods Pro 2 USB-C, chống ồn chủ động.', 'price' => 5990000, 'stock' => 100, 'status' => 'active'],
            ['category_id' => 3, 'name' => 'Apple Watch Series 9', 'slug' => 'apple-watch-series-9', 'description' => 'Đồng hồ thông minh Apple Watch Series 9 45mm, GPS.', 'price' => 10990000, 'stock' => 25, 'status' => 'active'],
            ['category_id' => 3, 'name' => 'Sony WH-1000XM5', 'slug' => 'sony-wh-1000xm5', 'description' => 'Tai nghe không dây Sony WH-1000XM5, chống ồn chủ động hàng đầu.', 'price' => 7990000, 'stock' => 40, 'status' => 'active'],
            ['category_id' => 3, 'name' => 'Samsung Galaxy Buds3 Pro', 'slug' => 'samsung-galaxy-buds3-pro', 'description' => 'Tai nghe Samsung Galaxy Buds3 Pro, ANC thông minh, âm thanh Hi-Fi.', 'price' => 4990000, 'stock' => 50, 'status' => 'active'],
            ['category_id' => 3, 'name' => 'JBL Flip 6', 'slug' => 'jbl-flip-6', 'description' => 'Loa Bluetooth JBL Flip 6, chống nước IP67, âm bass mạnh.', 'price' => 2990000, 'stock' => 60, 'status' => 'active'],
            ['category_id' => 3, 'name' => 'Logitech MX Master 3S', 'slug' => 'logitech-mx-master-3s', 'description' => 'Chuột không dây Logitech MX Master 3S, cuộn MagSpeed, im lặng.', 'price' => 2490000, 'stock' => 35, 'status' => 'active'],
            ['category_id' => 3, 'name' => 'Samsung Galaxy Watch 6', 'slug' => 'samsung-galaxy-watch-6', 'description' => 'Đồng hồ thông minh Samsung Galaxy Watch 6 Classic, xoay bezel.', 'price' => 6990000, 'stock' => 25, 'status' => 'active'],

            // 4. Thời trang (7)
            ['category_id' => 4, 'name' => 'Áo Polo Nam', 'slug' => 'ao-polo-nam', 'description' => 'Áo Polo nam cotton cao cấp, phong cách thể thao năng động.', 'price' => 350000, 'stock' => 200, 'status' => 'active'],
            ['category_id' => 4, 'name' => 'Quần Jean Nữ', 'slug' => 'quan-jean-nu', 'description' => 'Quần Jean nữ ống rộng, chất liệu co giãn thoải mái.', 'price' => 450000, 'stock' => 150, 'status' => 'active'],
            ['category_id' => 4, 'name' => 'Áo Sơ Mi Nữ', 'slug' => 'ao-so-mi-nu', 'description' => 'Áo sơ mi nữ công sở, chất liệu lụa mềm mại, phong cách thanh lịch.', 'price' => 290000, 'stock' => 150, 'status' => 'active'],
            ['category_id' => 4, 'name' => 'Đầm Dạ Hội', 'slug' => 'dam-da-hoi', 'description' => 'Đầm dạ hội dài, thiết kế sang trọng, phù hợp tiệc và sự kiện.', 'price' => 1200000, 'stock' => 50, 'status' => 'active'],
            ['category_id' => 4, 'name' => 'Quần Short Nam', 'slug' => 'quan-short-nam', 'description' => 'Quần short nam thể thao, chất liệu cotton thoáng mát.', 'price' => 199000, 'stock' => 200, 'status' => 'active'],
            ['category_id' => 4, 'name' => 'Áo Khoác Jacket Nam', 'slug' => 'ao-khoac-jacket-nam', 'description' => 'Áo khoác jacket nam phong cách streetwear, giữ ấm tốt.', 'price' => 599000, 'stock' => 80, 'status' => 'active'],
            ['category_id' => 4, 'name' => 'Áo Thun Unisex', 'slug' => 'ao-thun-unisex', 'description' => 'Áo thun unisex form rộng, chất liệu cotton 100%, nhiều màu sắc.', 'price' => 150000, 'stock' => 300, 'status' => 'active'],

            // 5. Đồng hồ (6)
            ['category_id' => 5, 'name' => 'Casio G-Shock GA-2100', 'slug' => 'casio-g-shock-ga-2100', 'description' => 'Đồng hồ Casio G-Shock GA-2100, chống sốc, chống nước 200m.', 'price' => 3500000, 'stock' => 30, 'status' => 'active'],
            ['category_id' => 5, 'name' => 'Seiko Presage SRPD', 'slug' => 'seiko-presage-srpd', 'description' => 'Đồng hồ Seiko Presage SRPD, automatic, mặt số tinh tế.', 'price' => 8990000, 'stock' => 15, 'status' => 'active'],
            ['category_id' => 5, 'name' => 'Orient Bambino', 'slug' => 'orient-bambino', 'description' => 'Đồng hồ Orient Bambino, automatic, thiết kế cổ điển thanh lịch.', 'price' => 5990000, 'stock' => 20, 'status' => 'active'],
            ['category_id' => 5, 'name' => 'Tissot PRX', 'slug' => 'tissot-prx', 'description' => 'Đồng hồ Tissot PRX automatic, phong cách retro hiện đại.', 'price' => 15990000, 'stock' => 10, 'status' => 'active'],
            ['category_id' => 5, 'name' => 'Citizen Eco-Drive', 'slug' => 'citizen-eco-drive', 'description' => 'Đồng hồ Citizen Eco-Drive, năng lượng ánh sáng, không cần pin.', 'price' => 7990000, 'stock' => 18, 'status' => 'active'],
            ['category_id' => 5, 'name' => 'Garmin Venu 3', 'slug' => 'garmin-venu-3', 'description' => 'Đồng hồ thông minh Garmin Venu 3, AMOLED, theo dõi sức khỏe toàn diện.', 'price' => 11990000, 'stock' => 22, 'status' => 'active'],

            // 6. Giày dép (6)
            ['category_id' => 6, 'name' => 'Nike Air Max 90', 'slug' => 'nike-air-max-90', 'description' => 'Giày Nike Air Max 90, thiết kế huyền thoại, đệm khí thoải mái.', 'price' => 3200000, 'stock' => 40, 'status' => 'active'],
            ['category_id' => 6, 'name' => 'Adidas Ultraboost Light', 'slug' => 'adidas-ultraboost-light', 'description' => 'Giày Adidas Ultraboost Light, công nghệ BOOST, êm ái tối đa.', 'price' => 4500000, 'stock' => 30, 'status' => 'active'],
            ['category_id' => 6, 'name' => 'Converse Chuck Taylor', 'slug' => 'converse-chuck-taylor', 'description' => 'Giày Converse Chuck Taylor All Star Classic, phong cách đường phố.', 'price' => 1500000, 'stock' => 60, 'status' => 'active'],
            ['category_id' => 6, 'name' => 'Vans Old Skool', 'slug' => 'vans-old-skool', 'description' => 'Giày Vans Old Skool, thiết kế side stripe đặc trưng.', 'price' => 1800000, 'stock' => 50, 'status' => 'active'],
            ['category_id' => 6, 'name' => 'Nike Jordan 1 Retro High', 'slug' => 'nike-jordan-1-retro-high', 'description' => 'Giày Nike Air Jordan 1 Retro High OG, biểu tượng sneaker.', 'price' => 4990000, 'stock' => 15, 'status' => 'active'],
            ['category_id' => 6, 'name' => 'Dr. Martens 1460', 'slug' => 'dr-martens-1460', 'description' => 'Giày boot Dr. Martens 1460, da thật, phong cách punk cổ điển.', 'price' => 4200000, 'stock' => 25, 'status' => 'active'],

            // 7. Túi xách (6)
            ['category_id' => 7, 'name' => 'Coach Tabby Bag', 'slug' => 'coach-tabby-bag', 'description' => 'Túi Coach Tabby Shoulder Bag, da cao cấp, thiết kế cổ điển.', 'price' => 8500000, 'stock' => 20, 'status' => 'active'],
            ['category_id' => 7, 'name' => 'Michael Kors Jet Set', 'slug' => 'michael-kors-jet-set', 'description' => 'Túi Michael Kors Jet Set Travel Tote, phong cách năng động.', 'price' => 6990000, 'stock' => 25, 'status' => 'active'],
            ['category_id' => 7, 'name' => 'Furla Metropolis', 'slug' => 'furla-metropolis', 'description' => 'Túi Furla Metropolis Mini, thiết kế Italy thanh lịch.', 'price' => 7500000, 'stock' => 15, 'status' => 'active'],
            ['category_id' => 7, 'name' => 'Longchamp Le Pliage', 'slug' => 'longchamp-le-pliage', 'description' => 'Túi Longchamp Le Pliage, vải nylon chống nước, gập gọn tiện lợi.', 'price' => 3500000, 'stock' => 30, 'status' => 'active'],
            ['category_id' => 7, 'name' => 'Tote Bag Canvas Nam', 'slug' => 'tote-bag-canvas-nam', 'description' => 'Tote bag canvas nam, chất liệu dày dặn, phong cách tối giản.', 'price' => 250000, 'stock' => 100, 'status' => 'active'],
            ['category_id' => 7, 'name' => 'Balo Laptop Anti-theft', 'slug' => 'balo-laptop-anti-theft', 'description' => 'Balo laptop chống trộm, sạc USB tích hợp, chống nước.', 'price' => 890000, 'stock' => 40, 'status' => 'active'],

            // 8. Mỹ phẩm (6)
            ['category_id' => 8, 'name' => 'Son MAC Ruby Woo', 'slug' => 'son-mac-ruby-woo', 'description' => 'Son môi MAC Ruby Woo, màu đỏ cổ điển, finish matte.', 'price' => 450000, 'stock' => 100, 'status' => 'active'],
            ['category_id' => 8, 'name' => 'Nước Hoa Chanel No.5', 'slug' => 'nuoc-hoa-chanel-no-5', 'description' => 'Nước hoa Chanel No.5 Eau de Parfum 100ml, hương hoa cổ điển.', 'price' => 3500000, 'stock' => 20, 'status' => 'active'],
            ['category_id' => 8, 'name' => 'Kem Chống Nắng Anessa', 'slug' => 'kem-chong-nang-anessa', 'description' => 'Kem chống nắng Anessa Perfect UV SPF50+, chống nước, không trắng.', 'price' => 480000, 'stock' => 80, 'status' => 'active'],
            ['category_id' => 8, 'name' => 'Serum The Ordinary Niacinamide', 'slug' => 'serum-the-ordinary-niacinamide', 'description' => 'Serum The Ordinary Niacinamide 10% + Zinc 1%, se khít lỗ chân lông.', 'price' => 250000, 'stock' => 120, 'status' => 'active'],
            ['category_id' => 8, 'name' => 'Phấn Nước MAC', 'slug' => 'phan-nuoc-mac', 'description' => 'Phấn nước MAC Studio Fix Fluid, che phủ hoàn hảo, lâu trôi.', 'price' => 950000, 'stock' => 50, 'status' => 'active'],
            ['category_id' => 8, 'name' => 'Mascara Maybelline Lash Sensational', 'slug' => 'mascara-maybelline-lash', 'description' => 'Mascara Maybelline Lash Sensational, làm dày và cong mi vượt trội.', 'price' => 180000, 'stock' => 90, 'status' => 'active'],
        ];

        foreach ($products as $prod) {
            $product = Product::create($prod);
            // Also create a ProductImage record
            $imageSlug = $prod['slug'];
            if (file_exists(public_path("product-images/{$imageSlug}.jpg"))) {
                $product->images()->create([
                    'image_path' => "product-images/{$imageSlug}.jpg",
                    'is_primary' => true,
                    'sort_order' => 0,
                ]);
            }
        }

        // Coupons
        $this->call(CouponSeeder::class);
    }
}
