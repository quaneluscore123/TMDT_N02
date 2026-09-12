<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCouponTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'     => 'admin',
            'email'    => 'admin-coupon-test@socialshop.vn',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($this->admin);
    }

    public function test_admin_can_view_coupons_index(): void
    {
        Coupon::factory()->count(3)->create();

        $response = $this->get(route('admin.coupons.index'));

        $response->assertStatus(200)
                 ->assertSee('Quản lý mã giảm giá');
    }

    public function test_admin_can_create_coupon(): void
    {
        $data = [
            'code'               => 'NEWCODE10',
            'type'               => 'percent',
            'value'              => 10,
            'max_discount'       => 50000,
            'min_order_amount'   => 200000,
            'start_at'           => now()->format('Y-m-d\TH:i'),
            'end_at'             => now()->addDays(30)->format('Y-m-d\TH:i'),
            'usage_limit'        => 100,
            'status'             => 'active',
        ];

        $response = $this->post(route('admin.coupons.store'), $data);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('coupons', [
            'code'    => 'NEWCODE10',
            'type'    => 'percent',
            'value'   => 10,
            'status'  => 'active',
        ]);
    }

    public function test_admin_can_edit_coupon(): void
    {
        $coupon = Coupon::factory()->create(['code' => 'EDITME']);

        $response = $this->put(route('admin.coupons.update', $coupon), [
            'code'             => 'EDITED',
            'type'             => 'fixed',
            'value'            => 25000,
            'min_order_amount' => 0,
            'status'           => 'active',
        ]);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('coupons', [
            'id'     => $coupon->id,
            'code'   => 'EDITED',
            'value'  => 25000,
        ]);
    }

    public function test_admin_can_delete_coupon(): void
    {
        $coupon = Coupon::factory()->create(['used_count' => 0]);

        $response = $this->delete(route('admin.coupons.destroy', $coupon));

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
    }

    public function test_admin_can_toggle_coupon_status(): void
    {
        $coupon = Coupon::factory()->create(['status' => 'active']);

        $response = $this->patch(route('admin.coupons.toggle', $coupon));

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('coupons', [
            'id'     => $coupon->id,
            'status' => 'inactive',
        ]);
    }

    public function test_admin_can_view_usage_history(): void
    {
        $coupon = Coupon::factory()->create(['code' => 'HISTORY']);

        $response = $this->get(route('admin.coupons.usage', $coupon));

        $response->assertStatus(200)
                 ->assertSee('HISTORY');
    }

    public function test_admin_can_bulk_delete_coupons(): void
    {
        $c1 = Coupon::factory()->create(['used_count' => 0]);
        $c2 = Coupon::factory()->create(['used_count' => 0]);

        $response = $this->deleteJson(route('admin.coupons.bulk-delete'), [
            'ids' => [$c1->id, $c2->id],
        ]);

        $response->assertOk()
                 ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('coupons', ['id' => $c1->id]);
        $this->assertDatabaseMissing('coupons', ['id' => $c2->id]);
    }

    public function test_admin_can_bulk_toggle_coupons(): void
    {
        $c1 = Coupon::factory()->create(['status' => 'active']);
        $c2 = Coupon::factory()->create(['status' => 'active']);

        $response = $this->patchJson(route('admin.coupons.bulk-toggle'), [
            'ids' => [$c1->id, $c2->id],
            'status' => 'inactive',
        ]);

        $response->assertOk()
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('coupons', ['id' => $c1->id, 'status' => 'inactive']);
        $this->assertDatabaseHas('coupons', ['id' => $c2->id, 'status' => 'inactive']);
    }

    public function test_customer_cannot_access_admin_coupons(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user);

        $response = $this->get(route('admin.coupons.index'));

        $response->assertForbidden();
    }

    public function test_coupon_code_is_case_insensitive_in_search(): void
    {
        Coupon::factory()->create(['code' => 'CASETEST']);

        $response = $this->get(route('admin.coupons.index', ['search' => 'casetest']));

        $response->assertStatus(200)
                 ->assertSee('CASETEST');
    }
}
