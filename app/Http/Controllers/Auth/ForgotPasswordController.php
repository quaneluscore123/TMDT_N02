<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function showForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendLink(ForgotPasswordRequest $request): RedirectResponse
    {
        $status = $this->authService->sendResetLink($request->email);

        // Không tiết lộ email có tồn tại hay không (chống enumeration)
        if ($status === Password::RESET_LINK_SENT || $status === Password::INVALID_USER) {
            return back()->with('success', 'Nếu email đã đăng ký, chúng tôi đã gửi link đặt lại mật khẩu. Vui lòng kiểm tra hộp thư.');
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = $this->authService->resetPassword($request->only(
            'email', 'password', 'password_confirmation', 'token'
        ));

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('success', 'Mật khẩu đã được đặt lại. Vui lòng đăng nhập.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
