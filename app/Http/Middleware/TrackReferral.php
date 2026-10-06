<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackReferral
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->has('ref')) {
            $refCode = $request->query('ref');

            // Check if the referral code belongs to a valid user
            if (User::where('referral_code', $refCode)->exists()) {
                $cookieName = config('referral.cookie_name', 'referral_code');
                $cookieLifetime = config('referral.cookie_lifetime', 43200); // 30 days

                // Attach the cookie to the response
                return $response->cookie($cookieName, $refCode, $cookieLifetime);
            }
        }

        return $response;
    }
}
