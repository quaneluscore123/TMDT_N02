<?php

namespace App\Services\Order;

use App\Exceptions\CouponException;
use App\Exceptions\OrderException;
use App\Mail\OrderConfirmedMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\BaseService;
use App\Services\Cart\CartService;
use App\Services\Coupon\CouponService;
use App\Services\Referral\ReferralService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrderService extends BaseService
{
    public function __construct(
        private CartService $cartService,
        private CouponService $couponService,
        private ReferralService $referralService
    ) {}

    /**
     * Tạo đơn hàng mới từ giỏ hàng hiện tại của user.
     * Toàn bộ quá trình được bọc trong DB Transaction.
     *
     * @throws OrderException Khi giỏ hàng rỗng hoặc tồn kho không đủ.
     */
    public function placeOrder(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            // 1. Lấy giỏ hàng đầy đủ
            $cart = $this->cartService->getCartWithItems($user->id);

            if ($cart->items->isEmpty()) {
                throw new OrderException('Giỏ hàng của bạn đang trống.');
            }

            // 2. Kiểm tra tồn kho & tính subtotal (lấy giá từ DB thực, không tin giỏ hàng)
            $subtotal = 0;
            $itemsToCreate = [];

            $productIds = $cart->items->pluck('product_id');
            $products   = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            foreach ($cart->items as $cartItem) {
                $product = $products->get($cartItem->product_id);

                if (!$product || $product->status !== 'active') {
                    throw new OrderException("Sản phẩm \"{$cartItem->product->name}\" hiện không còn kinh doanh.");
                }

                if ($product->stock < $cartItem->quantity) {
                    throw new OrderException(
                        "Sản phẩm \"{$product->name}\" chỉ còn {$product->stock} trong kho, không đủ số lượng yêu cầu."
                    );
                }

                $lineTotal = $product->effectivePrice() * $cartItem->quantity;
                $subtotal += $lineTotal;

                $itemsToCreate[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'price'        => $product->effectivePrice(),
                    'quantity'     => $cartItem->quantity,
                    'subtotal'     => $lineTotal,
                ];
            }

            // 3. Xử lý Coupon (nếu có trong session)
            $discount  = 0;
            $coupon    = null;
            $couponCode = session('coupon_code');

            if ($couponCode) {
                try {
                    $result = $this->couponService->validateAndCalculate($couponCode, $subtotal, $user);
                    $coupon   = $result['coupon'];
                    $discount = $result['discount'];
                } catch (CouponException $e) {
                    session()->forget('coupon_code');
                    throw $e;
                }
            }

            // 4. Tính phí ship (freeship khi subtotal >= 500k, không áp dụng cho discount)
            $freeShippingThreshold = 500000;
            $shippingFee = $subtotal >= $freeShippingThreshold ? 0 : 30000;
            $total       = $subtotal - $discount + $shippingFee;

            // 5. Tạo Order
            $order = Order::create([
                'user_id'          => $user->id,
                'order_code'       => 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'subtotal'         => $subtotal,
                'discount'         => $discount,
                'shipping_fee'     => $shippingFee,
                'total'            => $total,
                'status'           => 'pending',
                'payment_method'   => $data['payment_method'],
                'payment_status'   => 'pending',
                'shipping_name'    => $data['shipping_name'],
                'shipping_phone'   => $data['shipping_phone'],
                'shipping_address' => $data['shipping_address'],
                'note'             => $data['note'] ?? null,
            ]);

            // 6. Tạo OrderItems
            $order->items()->createMany($itemsToCreate);

            // 7. Redeem Coupon (tăng used_count + tạo CouponUsage)
            if ($coupon) {
                $this->couponService->redeem($coupon, $user, $order);
            }

            // 8. Trừ tồn kho
            foreach ($itemsToCreate as $item) {
                Product::where('id', $item['product_id'])
                    ->decrement('stock', $item['quantity']);
            }

            // 9. Xóa giỏ hàng
            $this->cartService->clearCart($user->id);

            // 10. Lưu vết Referral (nếu có)
            $this->referralService->attachOrderToReferral($order);

            // 11. Xóa coupon khỏi session
            session()->forget('coupon_code');

            // 12. Gửi email xác nhận đơn hàng
            try {
                Mail::to($user->email)->send(new OrderConfirmedMail($order));
            } catch (\Exception $e) {
                \Log::error('Không thể gửi email xác nhận đơn hàng: ' . $e->getMessage());
            }

            return $order;
        });
    }

    /**
     * Lấy danh sách đơn hàng của một user.
     */
    public function getOrdersForUser(int $userId)
    {
        return Order::where('user_id', $userId)
            ->with('items.product')
            ->latest()
            ->paginate(10);
    }

    /**
     * Lấy chi tiết 1 đơn hàng, đảm bảo thuộc về user (tránh IDOR).
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getOrderForUser(int $orderId, int $userId): Order
    {
        return Order::where('id', $orderId)
            ->where('user_id', $userId)
            ->with('items.product')
            ->firstOrFail();
    }

    /**
     * Cập nhật trạng thái đơn hàng (dùng bởi Admin hoặc hệ thống thanh toán).
     */
    public function updateStatus(Order $order, string $status): Order
    {
        $order->update(['status' => $status]);
        return $order;
    }
}
