<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use App\Services\Cart\CartService;
use App\Services\Referral\ReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private ReferralService $referralService
    ) {}

    /**
     * Redirect sang trang đăng nhập của Google.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Google callback — xử lý sau khi user đồng ý cấp quyền.
     */
    public function callback(): RedirectResponse
    {
        try {
            $user = $this->authService->handleGoogleCallback();

            if (! $user->is_active) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.']);
            }

            Auth::login($user, remember: true);
            request()->session()->regenerate();

            try {
                app(CartService::class)->mergeGuestCart($user->id);
            } catch (\Throwable) {
                // bỏ qua lỗi merge cart
            }

            if ($user->isAdmin()) {
                $response = redirect()->route('admin.dashboard');
            } else {
                $response = redirect()->route('home')->with('success', 'Đăng nhập bằng Google thành công!');
            }

            $referralCode = request()->cookie(config('referral.cookie_name', 'referral_code'));
            if ($referralCode) {
                // WasRecentlyCreated: chỉ lưu vết nếu user đăng ký mới
                if ($user->wasRecentlyCreated) {
                    $this->referralService->createReferral($user, $referralCode);
                }
                $response->withoutCookie(config('referral.cookie_name', 'referral_code'));
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('Google Auth Failed: '.$e->getMessage());

            return redirect()->route('login')
                ->withErrors(['email' => 'Đăng nhập Google thất bại. Vui lòng thử lại.']);
        }
    }
}
