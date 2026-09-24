<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = app(AuthService::class);
    }

    public function test_register_creates_user_and_fires_event()
    {
        Event::fake([Registered::class]);

        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $user = $this->authService->register($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertEquals('customer', $user->role);
        $this->assertNotNull($user->referral_code);

        Event::assertDispatched(Registered::class, function ($e) use ($user) {
            return $e->user->id === $user->id;
        });
    }

    public function test_login_success_logs_audit()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $success = $this->authService->login([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $this->assertTrue($success);
        $this->assertEquals($user->id, Auth::id());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login_success',
            'user_id' => $user->id
        ]);
    }

    public function test_login_failed_logs_audit()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $success = $this->authService->login([
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $this->assertFalse($success);
        $this->assertNull(Auth::user());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login_failed',
            'user_id' => $user->id
        ]);
    }

    public function test_login_blocked_logs_audit()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $success = $this->authService->login([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $this->assertFalse($success);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login_blocked',
            'user_id' => $user->id
        ]);
    }

    public function test_is_blocked_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $this->assertTrue($this->authService->isBlockedLogin('test@example.com', 'password123'));
        $this->assertFalse($this->authService->isBlockedLogin('test@example.com', 'wrong'));
        $this->assertFalse($this->authService->isBlockedLogin('nonexistent@example.com', 'pwd'));
    }

    public function test_logout_logs_audit_and_invalidates_session()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = request();
        $request->setLaravelSession(app('session.store'));
        app('session.store')->start();

        $this->authService->logout();

        $this->assertNull(Auth::user());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'logout',
            'user_id' => $user->id
        ]);
    }

    public function test_send_reset_link()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $status = $this->authService->sendResetLink('test@example.com');
        $this->assertEquals(Password::RESET_LINK_SENT, $status);
    }

    public function test_reset_password()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = Password::broker()->createToken($user);

        $status = $this->authService->resetPassword([
            'email' => 'test@example.com',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
            'token' => $token,
        ]);

        $this->assertEquals(Password::PASSWORD_RESET, $status);
        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
    }

    public function test_handle_google_callback_creates_new_user()
    {
        $googleUser = $this->getMockBuilder(SocialiteUser::class)
            ->onlyMethods(['getId', 'getName', 'getEmail', 'getAvatar'])
            ->getMock();

        $googleUser->method('getId')->willReturn('123456');
        $googleUser->method('getName')->willReturn('Google User');
        $googleUser->method('getEmail')->willReturn('google@example.com');
        $googleUser->method('getAvatar')->willReturn('http://avatar.com/123');

        Socialite::shouldReceive('driver')->with('google')->andReturnSelf();
        Socialite::shouldReceive('user')->andReturn($googleUser);

        $user = $this->authService->handleGoogleCallback();

        $this->assertEquals('Google User', $user->name);
        $this->assertEquals('google@example.com', $user->email);
        $this->assertEquals('123456', $user->provider_id);
        $this->assertEquals('google', $user->provider);
        $this->assertNotNull($user->referral_code);
    }

    public function test_handle_google_callback_updates_existing_user_without_referral_code()
    {
        $existing = User::factory()->create([
            'email' => 'google@example.com',
            'referral_code' => null
        ]);

        $googleUser = $this->getMockBuilder(SocialiteUser::class)
            ->onlyMethods(['getId', 'getName', 'getEmail', 'getAvatar'])
            ->getMock();

        $googleUser->method('getId')->willReturn('123456');
        $googleUser->method('getName')->willReturn('Google User');
        $googleUser->method('getEmail')->willReturn('google@example.com');
        $googleUser->method('getAvatar')->willReturn('http://avatar.com/123');

        Socialite::shouldReceive('driver')->with('google')->andReturnSelf();
        Socialite::shouldReceive('user')->andReturn($googleUser);

        $user = $this->authService->handleGoogleCallback();

        $this->assertEquals($existing->id, $user->id);
        $this->assertNotNull($user->fresh()->referral_code);
    }

    public function test_generate_unique_referral_code()
    {
        $code = $this->authService->generateUniqueReferralCode();
        $this->assertIsString($code);
        $this->assertEquals(8, strlen($code));
    }
}
