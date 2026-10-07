<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\SocialLoginException;
use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\Auth\AuthService;
use App\Services\Cart\CartService;
use App\Services\Referral\ReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

/**
 * Luồng đăng nhập OAuth dùng chung cho các mạng xã hội (Google, Facebook).
 */
abstract class SocialLoginController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected ReferralService $referralService
    ) {}

    /** Tên driver Socialite: 'google' | 'facebook'. */
    abstract protected function provider(): string;

    /** Tên hiển thị trong thông báo. */
    abstract protected function label(): string;

    /**
     * Redirect sang trang đăng nhập của nhà cung cấp.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver($this->provider())->redirect();
    }

    /**
     * Callback — xử lý sau khi user đồng ý cấp quyền.
     */
    public function callback(): RedirectResponse
    {
        try {
            $user = $this->authService->handleSocialCallback($this->provider());

            if (! $user->is_active) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.']);
            }

            Auth::login($user, remember: true);
            request()->session()->regenerate();

            AuditService::log('login_success', 'User', $user->id, ['provider' => $this->provider()]);

            try {
                app(CartService::class)->mergeGuestCart($user->id);
            } catch (\Throwable) {
                // bỏ qua lỗi merge cart
            }

            if ($user->isAdmin()) {
                $response = redirect()->route('admin.dashboard');
            } else {
                $response = redirect()->route('home')->with('success', 'Đăng nhập bằng '.$this->label().' thành công!');
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
        } catch (SocialLoginException $e) {
            return redirect()->route('login')->withErrors(['email' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::error($this->label().' Auth Failed: '.$e->getMessage());

            return redirect()->route('login')
                ->withErrors(['email' => 'Đăng nhập '.$this->label().' thất bại. Vui lòng thử lại.']);
        }
    }
}
