<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Thêm header bảo mật trên mọi response.
     * (CSP bật riêng ở production với nonce — xem README/INSTALL.)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Chrome/Edge enforce form-action trên redirect sau form POST —
        // phải cho phép host VNPay, nếu không 302 đi sandbox sẽ bị chặn.
        $vnpayOrigin = '';
        $vnpayUrl = (string) config('vnpay.url');
        if ($vnpayUrl !== '') {
            $scheme = parse_url($vnpayUrl, PHP_URL_SCHEME);
            $host = parse_url($vnpayUrl, PHP_URL_HOST);
            if ($scheme && $host) {
                $vnpayOrigin = " {$scheme}://{$host}";
            }
        }

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net",
            "img-src 'self' data: https: http:",
            "connect-src 'self' https://generativelanguage.googleapis.com",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'{$vnpayOrigin}",
            "object-src 'none'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
