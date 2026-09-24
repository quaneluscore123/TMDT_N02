<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
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

    public function test_cancel_order_restores_stock(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create(['stock' => 5, 'status' => 'active']);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => 2,
            'subtotal' => $product->price * 2,
        ]);
        $product->decrement('stock', 2);
        $this->assertEquals(3, $product->fresh()->stock);

        $this->actingAs($this->admin)
            ->patch("/admin/orders/{$order->id}/status", ['status' => 'cancelled'])
            ->assertRedirect();

        $this->assertEquals(5, $product->fresh()->stock);
    }

    public function test_cannot_reopen_cancelled_order(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'cancelled',
        ]);

        $this->actingAs($this->admin)
            ->patch("/admin/orders/{$order->id}/status", ['status' => 'pending'])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
    }
}
