<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use App\Services\Cart\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Trang đăng nhập
     *
     * @group Authentication
     *
     * @unauthenticated
     */
    public function showForm(): View
    {
        return view('auth.login');
    }

    /**
     * Đăng nhập
     *
     * Đăng nhập bằng email và mật khẩu. Trả về redirect với session cookie.
     *
     * @group Authentication
     *
     * @unauthenticated
     *
     * @bodyParam email string required Email của tài khoản. Example: admin@dksc.local
     * @bodyParam password string required Mật khẩu. Example: password
     * @bodyParam remember boolean Ghi nhớ đăng nhập (tuỳ chọn). Example: true
     *
     * @response 302 scenario="Đăng nhập thành công" {}
     * @response 422 scenario="Sai email/mật khẩu" {"errors": {"email": ["Email hoặc mật khẩu không chính xác."]}}
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $success = $this->authService->login(
            $request->only('email', 'password'),
            $request->boolean('remember')
        );

        if (! $success) {
            if ($this->authService->isBlockedLogin(
                (string) $request->input('email'),
                (string) $request->input('password')
            )) {
                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.']);
            }

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email hoặc mật khẩu không chính xác.']);
        }

        $request->session()->regenerate();

        // Gộp giỏ guest (session) vào giỏ user
        try {
            app(CartService::class)->mergeGuestCart(auth()->id());
        } catch (\Throwable) {
            // không chặn đăng nhập nếu merge lỗi
        }

        // Admin → trang quản trị, customer → trang chủ
        if (auth()->user()->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('home'));
    }
}
