<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_view_orders(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/orders')
            ->assertOk();
    }

    public function test_admin_can_view_order_detail(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin)
            ->get("/admin/orders/{$order->id}")
            ->assertOk();
    }

    public function test_admin_can_update_order_status(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin)
            ->patch("/admin/orders/{$order->id}/status", [
                'status' => 'confirmed',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_admin_can_filter_orders_by_status(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'delivered',
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/orders?status=pending')
            ->assertOk()
            ->assertSee('pending');
    }
}
