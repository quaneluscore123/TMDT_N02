<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use App\Services\Cart\CartService;
use App\Services\Referral\ReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private ReferralService $referralService
    ) {}

    /**
     * Trang đăng ký
     *
     * @group Authentication
     *
     * @unauthenticated
     */
    public function showForm(): View
    {
        return view('auth.register');
    }

    /**
     * Đăng ký tài khoản
     *
     * Tạo tài khoản mới, tự động sinh `referral_code`, đăng nhập ngay sau đó.
     *
     * @group Authentication
     *
     * @unauthenticated
     *
     * @bodyParam name string required Họ tên. Example: Nguyễn Văn A
     * @bodyParam email string required Email (phải duy nhất). Example: newuser@example.com
     * @bodyParam password string required Mật khẩu (tối thiểu 8 ký tự). Example: password123
     * @bodyParam password_confirmation string required Xác nhập mật khẩu. Example: password123
     *
     * @response 302 scenario="Đăng ký thành công, redirect về trang chủ" {}
     * @response 422 scenario="Email đã tồn tại" {"errors": {"email": ["Email này đã được sử dụng."]}}
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = $this->authService->register($request->validated());

        // Ưu tiên form nhập tay, nếu form trống thì lấy từ cookie
        $referralCode = $request->input('referral_code') ?: request()->cookie(config('referral.cookie_name', 'referral_code'));

        if ($referralCode) {
            $this->referralService->createReferral($user, $referralCode);
        }

        Auth::login($user);
        $request->session()->regenerate();

        // Gộp giỏ guest (session) vào giỏ user — như LoginController/GoogleController
        try {
            app(CartService::class)->mergeGuestCart($user->id);
        } catch (\Throwable) {
            // không chặn đăng ký nếu merge lỗi
        }

        $response = redirect()->route('home')
            ->with('success', 'Đăng ký thành công! Chào mừng bạn đến với DK Social Commerce.');

        if ($referralCode) {
            $response->withoutCookie(config('referral.cookie_name', 'referral_code'));
        }

        return $response;
    }
}
