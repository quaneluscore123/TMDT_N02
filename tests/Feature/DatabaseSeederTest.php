<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_demo_orders_and_reviews_for_dashboard(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThan(10, Order::count());
        $this->assertTrue(Order::where('status', 'delivered')->exists());
        $this->assertTrue(Order::where('status', 'cancelled')->exists());
        $this->assertTrue(Order::where('created_at', '>=', now()->subDays(6)->startOfDay())->where('status', '!=', 'cancelled')->exists());
        $this->assertTrue(Review::where('status', 'approved')->exists());
        $this->assertTrue(Review::where('status', 'pending')->exists());

        $admin = User::where('email', 'admin@socialshop.vn')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.reviews.index', ['status' => 'pending']))->assertOk();
    }
}
