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
}
