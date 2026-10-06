<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;

class LogoutController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Đăng xuất
     *
     * Hủy session hiện tại và redirect về trang đăng nhập.
     *
     * @group Authentication
     *
     * @authenticated
     *
     * @response 302 scenario="Đăng xuất thành công" {}
     */
    public function logout(): RedirectResponse
    {
        $this->authService->logout();

        return redirect()->route('login')
            ->with('success', 'Bạn đã đăng xuất thành công.');
    }
}
