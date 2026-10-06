<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        return User::factory()->create([
            'password' => Hash::make('correct-password'),
        ]);
    }

    private function postLogin(User $user, string $password)
    {
        return $this->post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);
    }

    public function test_login_throttled_after_5_failures(): void
    {
        $user = $this->createUser();

        // 5 lần sai → vẫn vào được form lỗi (không throttle)
        for ($i = 0; $i < 5; $i++) {
            $this->postLogin($user, 'wrong-password')
                ->assertSessionHasErrors('email');
        }

        // Lần thứ 6 → chặn, kể cả mật khẩu đúng
        $response = $this->postLogin($user, 'correct-password');

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertEquals(
            'Bạn đã nhập sai nhiều lần. Vui lòng thử lại sau ít phút.',
            session('errors')->first('email')
        );
        $this->assertTrue(auth()->guest());
    }

    public function test_login_succeeds_after_throttle_expires(): void
    {
        $user = $this->createUser();

        for ($i = 0; $i < 5; $i++) {
            $this->postLogin($user, 'wrong-password');
        }

        $this->postLogin($user, 'correct-password')->assertRedirect(route('login'));

        // Sau 5 phút (decay window) → đăng nhập được again
        $this->travel(6)->minutes();

        $this->postLogin($user, 'correct-password')
            ->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_rate_limit_is_per_email_not_global(): void
    {
        $user1 = $this->createUser();
        $user2 = User::factory()->create(['password' => Hash::make('correct-password')]);

        for ($i = 0; $i < 5; $i++) {
            $this->postLogin($user1, 'wrong-password');
        }

        // user2 (email khác, cùng IP) vẫn đăng nhập được
        $this->postLogin($user2, 'correct-password')
            ->assertRedirect(route('home'));
    }

    public function test_login_failed_writes_audit_log(): void
    {
        $user = $this->createUser();

        $this->postLogin($user, 'wrong-password');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'login_failed',
        ]);
    }

    public function test_login_failed_audit_log_for_unknown_email(): void
    {
        $this->post('/login', [
            'email' => 'not-exist@example.com',
            'password' => 'wrong',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => null,
            'action' => 'login_failed',
        ]);
    }

    public function test_login_throttled_per_ip_across_different_emails(): void
    {
        // 20 email khác nhau từ cùng 1 IP → lần thứ 21 bị chặn
        for ($i = 0; $i < 20; $i++) {
            $this->post('/login', ['email' => "nobody{$i}@example.com", 'password' => 'x'])
                ->assertSessionHasErrors('email');
        }

        $user = $this->createUser();
        $this->postLogin($user, 'correct-password');

        $this->assertTrue(auth()->guest());
    }
}
