<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'customer',
        ]);
    }

    public function test_profile_page_requires_login(): void
    {
        $this->get('/profile')
            ->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_profile(): void
    {
        $this->actingAs($this->user)
            ->get('/profile')
            ->assertOk();
    }

    public function test_user_can_update_profile(): void
    {
        $this->actingAs($this->user)
            ->put('/profile', [
                'name' => 'Updated Name',
                'phone' => '0123456789',
                'address' => '123 Test Street',
            ])
            ->assertRedirect('/profile');

        $this->user->refresh();
        $this->assertEquals('Updated Name', $this->user->name);
        $this->assertEquals('0123456789', $this->user->phone);
        $this->assertEquals('123 Test Street', $this->user->address);
    }

    public function test_profile_update_validates_name(): void
    {
        $this->actingAs($this->user)
            ->put('/profile', [
                'name' => '',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_user_can_change_password(): void
    {
        $this->actingAs($this->user)
            ->put('/profile/password', [
                'current_password' => 'password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect('/profile');

        $this->assertTrue(
            Hash::check('new-password-123', $this->user->fresh()->password)
        );
    }

    public function test_change_password_rejects_wrong_current_password(): void
    {
        $this->actingAs($this->user)
            ->put('/profile/password', [
                'current_password' => 'wrong-current',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(
            Hash::check('password', $this->user->fresh()->password)
        );
    }

    public function test_change_password_requires_min_8_chars(): void
    {
        $this->actingAs($this->user)
            ->put('/profile/password', [
                'current_password' => 'password',
                'password' => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertSessionHasErrors('password');
    }
}
