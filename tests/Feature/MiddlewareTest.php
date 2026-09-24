<?php

namespace Tests\Feature;

use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\TrackReferral;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_admin_middleware_allows_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $request = Request::create('/admin', 'GET');
        $middleware = new IsAdmin();

        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }

    public function test_is_admin_middleware_aborts_non_admin()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user);

        $request = Request::create('/admin', 'GET');
        $middleware = new IsAdmin();

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Bạn không có quyền truy cập trang này.');

        $middleware->handle($request, function () {});
    }

    public function test_is_admin_middleware_aborts_guest()
    {
        $request = Request::create('/admin', 'GET');
        $middleware = new IsAdmin();

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Bạn không có quyền truy cập trang này.');

        $middleware->handle($request, function () {});
    }

    public function test_track_referral_middleware_sets_cookie_if_valid_code()
    {
        $user = User::factory()->create(['referral_code' => 'REF123']);
        Config::set('referral.cookie_name', 'ref_cookie');

        $request = Request::create('/?ref=REF123', 'GET');
        $middleware = new TrackReferral();

        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        $cookies = $response->headers->getCookies();
        $this->assertNotEmpty($cookies);
        $this->assertEquals('ref_cookie', $cookies[0]->getName());
        $this->assertEquals('REF123', $cookies[0]->getValue());
    }

    public function test_track_referral_middleware_ignores_invalid_code()
    {
        $request = Request::create('/?ref=INVALID', 'GET');
        $middleware = new TrackReferral();

        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        $cookies = $response->headers->getCookies();
        $this->assertEmpty($cookies);
    }

    public function test_track_referral_middleware_ignores_missing_code()
    {
        $request = Request::create('/', 'GET');
        $middleware = new TrackReferral();

        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        $cookies = $response->headers->getCookies();
        $this->assertEmpty($cookies);
    }
}
