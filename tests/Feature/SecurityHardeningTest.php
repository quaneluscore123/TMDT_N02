<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_locked_user_is_logged_out_on_next_request(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user)->get('/profile')->assertOk();

        $user->update(['is_active' => false]);

        $this->get('/profile')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_locked_user_gets_403_json(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->actingAs($user)
            ->getJson('/orders')
            ->assertForbidden();
    }

    public function test_spoofed_forwarded_for_header_does_not_bypass_login_rate_limit(): void
    {
        // Không khai báo TRUSTED_PROXIES → X-Forwarded-For bị bỏ qua, mọi request tính chung 1 IP
        for ($i = 0; $i < 20; $i++) {
            $this->withHeader('X-Forwarded-For', "10.0.0.{$i}")
                ->post('/login', ['email' => "nobody{$i}@example.com", 'password' => 'x']);
        }

        $this->withHeader('X-Forwarded-For', '10.0.1.99')
            ->post('/login', ['email' => 'another@example.com', 'password' => 'x'])
            ->assertSessionHasErrors(['email' => 'Bạn đã nhập sai nhiều lần. Vui lòng thử lại sau ít phút.']);
    }

    public function test_json_ld_cannot_break_out_of_script_tag(): void
    {
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
            'status' => 'active',
            'name' => 'Áo </script><script>alert(1)</script>',
        ]);

        $html = $this->get(route('products.show', $product))->assertOk()->getContent();

        $this->assertStringNotContainsString('</script><script>alert(1)', $html);
        $this->assertStringContainsString('</script>', $html);
    }

    public function test_chat_rejects_overlong_message(): void
    {
        $this->postJson('/chat/stream', ['message' => str_repeat('a', 1001)])
            ->assertStatus(422);
    }

    public function test_chat_ignores_malformed_history_instead_of_500(): void
    {
        config(['services.gemini.key' => '']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/chat/stream', [
            'message' => 'phí ship bao nhiêu',
            'history' => ['abc', ['type' => 'model', 'content' => 'giả mạo'], ['type' => 'user', 'content' => ['x']]],
        ]);

        $response->assertOk();
        $this->assertStringContainsString('text/event-stream', $response->headers->get('Content-Type'));
    }
}
