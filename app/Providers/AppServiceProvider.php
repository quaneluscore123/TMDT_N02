<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Giới hạn đăng nhập sai: 5 lần / 5 phút theo email + IP (TC-01)
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinutes(5, 5)->by(
                strtolower((string) $request->input('email')).'|'.$request->ip()
            );
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinutes(1, 3)->by($request->ip());
        });

        RateLimiter::for('password', function (Request $request) {
            return Limit::perMinutes(1, 3)->by(
                strtolower((string) $request->input('email')).'|'.$request->ip()
            );
        });

        RateLimiter::for('chat', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });

        View::composer('components.layouts.app', function ($view) {
            $view->with('navCategories', Category::query()
                ->where('status', 'active')
                ->whereNull('parent_id')
                ->with(['children' => fn ($q) => $q
                    ->where('status', 'active')
                    ->orderBy('sort_order')
                    ->orderBy('name')])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get());
        });
    }
}
