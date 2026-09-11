<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Mã phần trăm — giảm 10%, đơn tối thiểu 200k, giảm tối đa 100k
        Coupon::create([
            'code'              => 'SALE10',
            'type'              => 'percent',
            'value'             => 10,
            'min_order_amount'  => 200000,
            'max_discount'      => 100000,
            'start_at'          => now()->subDays(7),
            'end_at'            => now()->addDays(30),
            'usage_limit'       => 100,
            'used_count'        => 0,
            'status'            => 'active',
        ]);

        // 2. Mã cố định — giảm 50k, đơn tối thiểu 300k
        Coupon::create([
            'code'              => 'FIXED50K',
            'type'              => 'fixed',
            'value'             => 50000,
            'min_order_amount'  => 300000,
            'max_discount'      => null,
            'start_at'          => now()->subDays(3),
            'end_at'            => now()->addDays(60),
            'usage_limit'       => 50,
            'used_count'        => 0,
            'status'            => 'active',
        ]);

        // 3. Mã hết hạn
        Coupon::create([
            'code'              => 'EXPIRED',
            'type'              => 'percent',
            'value'             => 20,
            'min_order_amount'  => 0,
            'max_discount'      => null,
            'start_at'          => now()->subDays(30),
            'end_at'            => now()->subDays(1),
            'usage_limit'       => null,
            'used_count'        => 0,
            'status'            => 'active',
        ]);

        // 4. Mã hết lượt (limit = 2, used = 2)
        Coupon::create([
            'code'              => 'LIMITED',
            'type'              => 'fixed',
            'value'             => 30000,
            'min_order_amount'  => 100000,
            'max_discount'      => null,
            'start_at'          => now()->subDays(5),
            'end_at'            => now()->addDays(15),
            'usage_limit'       => 2,
            'used_count'        => 2,
            'status'            => 'active',
        ]);

        // 5. Mã bị vô hiệu hóa
        Coupon::create([
            'code'              => 'INACTIVE',
            'type'              => 'percent',
            'value'             => 15,
            'min_order_amount'  => 0,
            'max_discount'      => null,
            'start_at'          => null,
            'end_at'            => null,
            'usage_limit'       => null,
            'used_count'        => 0,
            'status'            => 'inactive',
        ]);
    }
}
