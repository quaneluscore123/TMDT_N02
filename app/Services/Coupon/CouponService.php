<?php

namespace App\Services\Coupon;

use App\Exceptions\CouponException;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use App\Services\BaseService;

class CouponService extends BaseService
{
    /**
     * Validate coupon cho UX (không lock DB).
     * Trả về JSON discount để hiển thị trên checkout.
     *
     * @throws CouponException
     */
    public function applyCoupon(string $code, int $subtotal, User $user): array
    {
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            throw new CouponException('Mã giảm giá không hợp lệ.');
        }

        $this->validateCoupon($coupon, $subtotal, $user);

        $discount = $coupon->calculateDiscount($subtotal);

        return [
            'code'     => $coupon->code,
            'discount' => $discount,
            'type'     => $coupon->type,
            'value'    => $coupon->value,
            'message'  => "Áp dụng mã {$coupon->code} thành công!",
        ];
    }

    /**
     * Validate + lock coupon trong DB Transaction.
     * Dùng trong OrderService::placeOrder().
     *
     * @return array{coupon: Coupon, discount: int}
     * @throws CouponException
     */
    public function validateAndCalculate(string $code, int $subtotal, User $user): array
    {
        $coupon = Coupon::where('code', $code)->lockForUpdate()->first();

        if (!$coupon) {
            throw new CouponException('Mã giảm giá không hợp lệ.');
        }

        $this->validateCoupon($coupon, $subtotal, $user);

        $discount = $coupon->calculateDiscount($subtotal);

        return [
            'coupon'   => $coupon,
            'discount' => $discount,
        ];
    }

    /**
     * Thực thi cuối cùng: tăng used_count + tạo CouponUsage.
     * Gọi SAU khi Order đã được tạo thành công.
     */
    public function redeem(Coupon $coupon, User $user, Order $order): void
    {
        $coupon->increment('used_count');

        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id'   => $user->id,
            'order_id'  => $order->id,
        ]);
    }

    /**
     * Các bước validate chung cho cả apply và validateAndCalculate.
     *
     * @throws CouponException
     */
    private function validateCoupon(Coupon $coupon, int $subtotal, User $user): void
    {
        // 1. Active
        if ($coupon->status !== 'active') {
            throw new CouponException('Mã giảm giá đã bị vô hiệu hóa.');
        }

        // 2. Thời hạn
        $now = now();
        if ($coupon->start_at && $coupon->start_at->greaterThan($now)) {
            throw new CouponException('Mã giảm giá chưa đến thời hạn sử dụng.');
        }
        if ($coupon->end_at && $coupon->end_at->lessThan($now)) {
            throw new CouponException('Mã giảm giá đã hết hạn.');
        }

        // 3. Usage limit
        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw new CouponException('Mã giảm giá đã hết lượt sử dụng.');
        }

        // 4. Min order amount
        if ($subtotal < $coupon->min_order_amount) {
            $minFormatted = number_format($coupon->min_order_amount, 0, ',', '.') . '₫';
            throw new CouponException("Đơn hàng chưa đạt giá trị tối thiểu {$minFormatted} để sử dụng mã này.");
        }

        // 5. Per-user limit
        $usedBefore = CouponUsage::where('coupon_id', $coupon->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($usedBefore) {
            throw new CouponException('Bạn đã sử dụng mã giảm giá này rồi.');
        }
    }
}
