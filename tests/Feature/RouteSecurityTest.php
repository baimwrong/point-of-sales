<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $adminRole = Role::where('name', 'admin')->first();
        $cashierRole = Role::where('name', 'cashier')->first();

        $this->admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'role_id' => $cashierRole->id,
            'is_active' => true,
        ]);
    }

    public function test_login_route_is_throttled_after_repeated_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('login.submit'), [
                'email' => 'wrong@example.com',
                'password' => 'wrong-password',
            ]);
            $this->assertNotEquals(429, $response->status());
        }

        // 6th attempt should be blocked with 429 Too Many Requests
        $response = $this->post(route('login.submit'), [
            'email' => 'wrong@example.com',
            'password' => 'wrong-password',
        ]);
        $response->assertStatus(429);
    }

    public function test_cashier_cannot_export_products(): void
    {
        $response = $this->actingAs($this->cashier)->get(route('products.export'));
        $response->assertStatus(403);

        $adminResponse = $this->actingAs($this->admin)->get(route('products.export'));
        $adminResponse->assertStatus(200);
    }

    public function test_cashier_cannot_delete_customer(): void
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'phone' => '08123456789',
        ]);

        $response = $this->actingAs($this->cashier)->delete(route('customers.destroy', $customer->id));
        $response->assertStatus(403);
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);

        $adminResponse = $this->actingAs($this->admin)->delete(route('customers.destroy', $customer->id));
        $adminResponse->assertRedirect(route('customers.index'));
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }
}
