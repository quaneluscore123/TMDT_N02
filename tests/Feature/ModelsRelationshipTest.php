<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelsRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_coupon_usage_relationships()
    {
        $user = User::factory()->create();
        $coupon = Coupon::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $usage = CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
        ]);

        $this->assertEquals($coupon->id, $usage->coupon->id);
        $this->assertEquals($user->id, $usage->user->id);
        $this->assertEquals($order->id, $usage->order->id);
    }

    public function test_referral_relationships()
    {
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        $referral = Referral::create([
            'referrer_id' => $referrer->id,
            'referred_user_id' => $referred->id,
            'referral_code' => 'TESTCODE',
            'status' => 'pending',
        ]);

        $this->assertEquals($referrer->id, $referral->referrer->id);
        $this->assertEquals($referred->id, $referral->referredUser->id);
    }

    public function test_product_image_relationships()
    {
        $product = Product::factory()->create();
        $image = ProductImage::factory()->create(['product_id' => $product->id]);

        $this->assertEquals($product->id, $image->product->id);
    }

    public function test_payment_relationships()
    {
        $order = Order::factory()->create();
        
        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => 'cod',
            'amount' => 1000,
            'status' => 'pending',
        ]);

        $this->assertEquals($order->id, $payment->order->id);
    }

    public function test_coupon_scope_and_discount()
    {
        $coupon = Coupon::forceCreate([
            'code' => 'TEST100',
            'type' => 'percent',
            'value' => 20,
            'max_discount' => 50000,
            'min_order_amount' => 100000,
            'status' => 'active',
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
            'usage_limit' => 10,
            'used_count' => 0
        ]);

        $validCoupons = Coupon::valid()->get();
        $this->assertTrue($validCoupons->contains('id', $coupon->id));

        // Test calculateDiscount (Percent with Max Discount)
        $discount1 = $coupon->calculateDiscount(300000); // 20% of 300k is 60k, max is 50k
        $this->assertEquals(50000, $discount1);

        $discount2 = $coupon->calculateDiscount(200000); // 20% of 200k is 40k
        $this->assertEquals(40000, $discount2);

        $discount3 = $coupon->calculateDiscount(50000); // Less than min_order_amount
        $this->assertEquals(0, $discount3);

        $couponFixed = Coupon::forceCreate([
            'code' => 'TESTFIXED',
            'type' => 'fixed',
            'value' => 30000,
            'min_order_amount' => 100000,
            'status' => 'active',
        ]);
        $discount4 = $couponFixed->calculateDiscount(200000);
        $this->assertEquals(30000, $discount4);
    }

    public function test_order_item_option_label()
    {
        $item = new \App\Models\OrderItem(['size' => 'XL', 'color' => 'Red']);
        $this->assertEquals('XL - Red', $item->optionLabel());

        $item2 = new \App\Models\OrderItem(['size' => 'XL']);
        $this->assertEquals('XL', $item2->optionLabel());

        $item3 = new \App\Models\OrderItem();
        $this->assertNull($item3->optionLabel());
    }

    public function test_payment_methods()
    {
        $payment = new \App\Models\Payment();
        $payment->transaction_id = 'VNP123';
        $this->assertEquals('VNP123', $payment->transaction_id);
    }

    public function test_product_image_methods()
    {
        $image = new \App\Models\ProductImage();
        $image->is_primary = true;
        $this->assertTrue($image->is_primary);
    }

    public function test_referral_methods()
    {
        $referral = new \App\Models\Referral();
        $referral->reward_amount = 50000;
        $this->assertEquals(50000, $referral->reward_amount);
    }
}
