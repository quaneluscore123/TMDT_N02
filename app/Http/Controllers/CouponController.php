<?php

namespace App\Http\Controllers;

use App\Exceptions\CouponException;
use App\Services\Cart\CartService;
use App\Services\Coupon\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    public function __construct(
        private CouponService $couponService,
        private CartService $cartService
    ) {}

    /**
     * Áp dụng mã giảm giá (AJAX).
     */
    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        try {
            $user    = Auth::user();
            $cart    = $this->cartService->getCartWithItems($user->id);
            $subtotal = $cart->totalPrice();

            $result = $this->couponService->applyCoupon(
                strtoupper($request->code),
                $subtotal,
                $user
            );

            session()->put('coupon_code', $result['code']);

            return response()->json([
                'success'  => true,
                'discount' => $result['discount'],
                'code'     => $result['code'],
                'message'  => $result['message'],
            ]);
        } catch (CouponException $e) {
            session()->forget('coupon_code');

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Xóa mã giảm giá (AJAX).
     */
    public function remove(): JsonResponse
    {
        session()->forget('coupon_code');

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa mã giảm giá.',
        ]);
    }
}
