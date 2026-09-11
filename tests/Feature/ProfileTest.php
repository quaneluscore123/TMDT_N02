<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
