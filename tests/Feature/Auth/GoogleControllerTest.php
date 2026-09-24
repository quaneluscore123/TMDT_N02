<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class GoogleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_redirect()
    {
        $response = $this->get('/auth/google');
        $response->assertRedirect();
    }

    public function test_google_callback_success()
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        $abstractUser = \Mockery::mock(\Laravel\Socialite\Two\User::class);
        $abstractUser->shouldReceive('getId')->andReturn('1234567890');
        $abstractUser->shouldReceive('getName')->andReturn('John Doe');
        $abstractUser->shouldReceive('getEmail')->andReturn('john@example.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn('http://avatar.com/john');

        $provider = \Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($abstractUser);
        $provider->shouldReceive('stateless')->andReturnSelf();

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');
        
        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_google_callback_inactive_user()
    {
        $user = User::factory()->create(['email' => 'jane@example.com', 'is_active' => false]);
        
        $abstractUser = \Mockery::mock(\Laravel\Socialite\Two\User::class);
        $abstractUser->shouldReceive('getId')->andReturn('0987654321');
        $abstractUser->shouldReceive('getName')->andReturn('Jane Doe');
        $abstractUser->shouldReceive('getEmail')->andReturn('jane@example.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn('http://avatar.com/jane');

        $provider = \Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($abstractUser);
        $provider->shouldReceive('stateless')->andReturnSelf();

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');
        
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
    
    public function test_google_callback_exception()
    {
        Socialite::shouldReceive('driver')->with('google')->andThrow(new \Exception('Google error'));

        $response = $this->get('/auth/google/callback');
        
        $response->assertRedirect(route('login'));
    }
}
